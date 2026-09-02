<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\PelacakanService;

/**
 * Pelacakan paket untuk panel admin.
 */
class AdminWebPelacakanController extends Controller
{
    public function __construct(private PelacakanService $pelacakan)
    {
    }

    /** Paket keluar: dari toko menuju pembeli. */
        public function pesanan(int $id)
    {
        $order = Order::findOrFail($id);
        $result = $this->pelacakan->lacak($order->tracking_number, $order->courier);

        // Otomatisasi: Jika status pelacakan kurir BiteShip sudah sampai tujuan (delivered), otomatis ubah status pesanan menjadi completed
        if (!empty($result['status']) && $result['status'] === 'delivered' && $order->status === 'shipped') {
            $order->status = 'completed';
            $order->save();
        }

        return response()->json(
            $result + ['arah' => 'keluar']
        );
    }

    /**
 * Paket kembali: dari pembeli menuju toko.
 */
    public function pengembalian(int $id)
    {
        $retur = OrderReturn::findOrFail($id);

        return response()->json(
            $this->pelacakan->lacak($retur->return_tracking_number, $retur->return_courier)
                + ['arah' => 'kembali']
        );
    }
}
