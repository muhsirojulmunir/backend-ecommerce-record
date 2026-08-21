<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BiteshipSaldo;
use App\Services\SaldoBiteshipService;
use Illuminate\Http\Request;

/**
 * Pencatatan saldo Biteship oleh admin.
 *
 * Biteship tidak menyediakan endpoint saldo, jadi angkanya tidak bisa diambil
 * sendiri oleh sistem. Yang bisa dilakukan: admin menuliskan angka yang dia
 * lihat di dasbor Biteship setiap kali mengisi ulang, lalu sistem mengurangi
 * ongkir tiap pengiriman yang terbit sesudahnya untuk memperkirakan sisanya.
 */
class AdminWebSaldoBiteshipController extends Controller
{
    public function __construct(private SaldoBiteshipService $saldo)
    {
    }

    public function index()
    {
        return view('admin.saldo-biteship', [
            'ringkasan' => $this->saldo->ringkasan(),
            'terpakai'  => $this->saldo->terpakaiSejakDicatat(),
            'riwayat'   => BiteshipSaldo::with('pencatat')->latest('dicatat_pada')->take(20)->get(),
            'ambang'    => (int) config('biteship.ambang_peringatan', 10_000),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'saldo_tercatat' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'catatan'        => ['nullable', 'string', 'max:255'],
        ], [
            'saldo_tercatat.required' => 'Isi saldo yang tertera di dasbor Biteship.',
            'saldo_tercatat.integer'  => 'Tulis angkanya saja, tanpa titik atau "Rp".',
        ]);

        $this->saldo->catat((int) $data['saldo_tercatat'], $data['catatan'] ?? null);

        return back()->with('success',
            'Saldo Biteship tercatat Rp ' . number_format($data['saldo_tercatat'], 0, ',', '.')
            . '. Perhitungan sisa dimulai ulang dari sini.');
    }
}
