<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Admin\ProductImportService;
use App\Services\Admin\ProductTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminWebProductImportController extends Controller
{
    /** Kunci session untuk menyimpan berkas yang sedang dipratinjau. */
    private const SESSION_KEY = 'product_import.file';

    public function __construct(
        private ProductImportService $importer,
        private ProductTemplateService $templater,
    ) {}

    /**
     * Halaman impor: panduan + form unggah.
     */
    public function index()
    {
        return view('admin.products-import', [
            'columns'    => ProductImportService::COLUMNS,
            'maxRows'    => ProductImportService::MAX_ROWS,
            'categories' => Category::orderBy('name')->pluck('name'),
            'preview'    => null,
        ]);
    }

    /**
     * Unduh template .xlsx yang sudah berisi contoh dan panduan.
     */
    public function template(): BinaryFileResponse
    {
        $path = storage_path('app/template-impor-produk-' . uniqid() . '.xlsx');

        $this->templater->writeTo($path);

        return response()
            ->download($path, 'template-impor-produk.xlsx')
            ->deleteFileAfterSend(true);
    }

    /**
     * Unggah berkas lalu tampilkan pratinjau — belum menyimpan apa pun.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
            'duplicate_mode' => ['nullable', 'in:skip,update'],
        ], [
            'file.required' => 'Silakan pilih berkas Excel atau CSV terlebih dahulu.',
            'file.mimes'    => 'Format berkas harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran berkas maksimal 10MB.',
        ]);

        $mode = $request->get('duplicate_mode', 'skip');

        // Berkas disimpan sementara supaya bisa dibaca ulang saat konfirmasi
        $storedPath = $request->file('file')->store('imports');

        try {
            $parsed = $this->importer->parse(Storage::path($storedPath), $mode);
        } catch (\Throwable $e) {
            Storage::delete($storedPath);
            Log::error('Gagal membaca berkas impor produk: ' . $e->getMessage());

            return redirect()->route('admin.products.import')
                ->with('error', 'Berkas tidak bisa dibaca. Pastikan formatnya .xlsx, .xls, atau .csv yang valid.');
        }

        // Buang berkas pratinjau sebelumnya kalau ada
        $this->forgetStoredFile();

        session([self::SESSION_KEY => [
            'path'          => $storedPath,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mode'          => $mode,
        ]]);

        return view('admin.products-import', [
            'columns'    => ProductImportService::COLUMNS,
            'maxRows'    => ProductImportService::MAX_ROWS,
            'categories' => Category::orderBy('name')->pluck('name'),
            'preview'    => $parsed,
            'fileName'   => $request->file('file')->getClientOriginalName(),
            'mode'       => $mode,
        ]);
    }

    /**
     * Jalankan impor berdasarkan berkas yang sedang dipratinjau.
     */
    public function store(Request $request)
    {
        $stored = session(self::SESSION_KEY);

        if (! $stored || ! Storage::exists($stored['path'])) {
            return redirect()->route('admin.products.import')
                ->with('error', 'Berkas pratinjau sudah tidak tersedia. Silakan unggah ulang berkasnya.');
        }

        $mode = $request->get('duplicate_mode', $stored['mode'] ?? 'skip');

        try {
            $parsed = $this->importer->parse(Storage::path($stored['path']), $mode);
            $result = $this->importer->import($parsed, $mode);
        } catch (\Throwable $e) {
            Log::error('Gagal mengimpor produk: ' . $e->getMessage());

            return redirect()->route('admin.products.import')
                ->with('error', 'Impor gagal: ' . $e->getMessage());
        } finally {
            $this->forgetStoredFile();
        }

        return redirect()->route('admin.products')->with('success', $this->buildSummaryMessage($result));
    }

    /**
     * Batalkan pratinjau dan hapus berkas sementara.
     */
    public function cancel()
    {
        $this->forgetStoredFile();

        return redirect()->route('admin.products.import')->with('success', 'Pratinjau dibatalkan.');
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    private function forgetStoredFile(): void
    {
        $stored = session(self::SESSION_KEY);

        if ($stored && Storage::exists($stored['path'])) {
            Storage::delete($stored['path']);
        }

        session()->forget(self::SESSION_KEY);
    }

    private function buildSummaryMessage(array $result): string
    {
        $parts = [];

        if ($result['created'] > 0) {
            $parts[] = "{$result['created']} produk baru ditambahkan";
        }

        if ($result['updated'] > 0) {
            $parts[] = "{$result['updated']} produk diperbarui";
        }

        if ($result['skipped'] > 0) {
            $parts[] = "{$result['skipped']} dilewati karena sudah ada";
        }

        if (! empty($result['categoriesCreated'])) {
            $count = count($result['categoriesCreated']);
            $names = implode(', ', array_slice($result['categoriesCreated'], 0, 3));
            $suffix = $count > 3 ? ", dan " . ($count - 3) . ' lainnya' : '';
            $parts[] = "{$count} kategori baru dibuat otomatis ({$names}{$suffix})";
        }

        if (! empty($result['failed'])) {
            $parts[] = count($result['failed']) . ' baris gagal';
        }

        if (empty($parts)) {
            return 'Tidak ada produk yang diimpor.';
        }

        return 'Impor selesai: ' . implode(', ', $parts) . '.';
    }
}
