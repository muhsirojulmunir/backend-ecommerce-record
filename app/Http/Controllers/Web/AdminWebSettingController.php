<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminWebSettingController extends Controller
{
    /** Label & ikon tiap tab grup pengaturan. */
    private const GROUPS = [
        'general'     => ['Informasi Toko', 'fa-store',          'Identitas toko yang tampil ke pembeli.'],
        'contact'     => ['Kontak & Sosial', 'fa-address-book',  'Cara pembeli menghubungi toko.'],
        'shipping'    => ['Pengiriman',      'fa-truck-fast',    'Alamat asal kirim dan kurir yang aktif.'],
        'payment'     => ['Pembayaran',      'fa-credit-card',   'Rekening tujuan dan metode bayar.'],
        'operational' => ['Operasional',     'fa-sliders',       'Perilaku toko: mode perbaikan, batas bayar, stok.'],
    ];

    /** Pilihan kurir untuk pengaturan bertipe json. */
    private const COURIERS = [
        'jne'      => 'JNE',
        'jnt'      => 'J&T Express',
        'sicepat'  => 'SiCepat',
        'anteraja' => 'AnterAja',
        'pos'      => 'POS Indonesia',
        'ninja'    => 'Ninja Xpress',
        'tiki'     => 'TIKI',
    ];

    public function index(Request $request)
    {
        $settings = WebsiteSetting::orderBy('id')->get()->groupBy('group');

        // Grup yang dikenal tampil lebih dulu dan berurutan; sisanya menyusul
        $ordered = collect(self::GROUPS)
            ->keys()
            ->filter(fn ($g) => $settings->has($g))
            ->merge($settings->keys()->diff(array_keys(self::GROUPS)))
            ->values();

        return view('admin.settings', [
            'settings'  => $settings,
            'groups'    => self::GROUPS,
            'order'     => $ordered,
            'active'    => $request->get('tab', $ordered->first() ?? 'general'),
            'couriers'  => self::COURIERS,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings'   => ['array'],
            'files'      => ['array'],
            'files.*'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg,ico', 'max:4096'],
        ], [
            'files.*.image' => 'Berkas :attribute harus berupa gambar.',
            'files.*.max'   => 'Ukuran gambar maksimal 4MB.',
        ]);

        $submitted = $request->input('settings', []);
        $files     = $request->file('files', []);
        $changed   = 0;

        foreach (WebsiteSetting::all() as $setting) {
            $newValue = null;
            $touched  = false;

            if ($setting->type === 'image') {
                // Hapus gambar kalau tombol "hapus" dicentang
                if ($request->boolean("remove.{$setting->key}")) {
                    $this->deleteImage($setting->value);
                    $newValue = null;
                    $touched  = true;
                } elseif (isset($files[$setting->key])) {
                    $this->deleteImage($setting->value);
                    $newValue = $files[$setting->key]->store('settings', 'public');
                    $touched  = true;
                }
            } elseif ($setting->type === 'boolean') {
                // Checkbox tidak terkirim saat tidak dicentang, jadi selalu dibaca ulang
                $newValue = $request->boolean("settings.{$setting->key}") ? '1' : '0';
                $touched  = true;
            } elseif (array_key_exists($setting->key, $submitted)) {
                $value = $submitted[$setting->key];

                $newValue = match ($setting->type) {
                    'json'    => json_encode(array_values(array_filter((array) $value))),
                    'integer' => (string) max(0, (int) $value),
                    default   => is_string($value) ? trim($value) : $value,
                };

                if ($newValue === '') {
                    $newValue = null;
                }

                $touched = true;
            }

            if ($touched && $newValue !== $setting->value) {
                $setting->value = $newValue;
                $setting->save();
                $changed++;
            }
        }

        $tab = $request->get('tab', 'general');

        return redirect()->route('admin.settings', ['tab' => $tab])->with(
            'success',
            $changed > 0
                ? "Pengaturan berhasil disimpan. {$changed} nilai diperbarui."
                : 'Tidak ada perubahan yang perlu disimpan.'
        );
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
