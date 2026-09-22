<?php

namespace App\Services\Customer;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Models\OrderReturn;
use App\Models\Cart;
use App\Models\User;
use App\Services\DuitkuService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected $orderRepository;
    protected $productRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }

    public function getCustomerOrders(int $userId)
    {
        return $this->orderRepository->getByUserId($userId);
    }

    public function getOrderDetail(int $userId, int $id)
    {
        $order = $this->orderRepository->findById($id);
        if ($order->user_id !== $userId) {
            throw new \Exception('Akses ditolak. Anda tidak berhak melihat pesanan ini.');
        }
        return $order;
    }

    public function checkout(int $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            // 1. Ambil cart beserta item-itemnya
            $cart = Cart::with('items.product', 'items.variant')->where('user_id', $userId)->first();
            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            $orderItems = [];
            $totalPrice  = 0;

            // 2. Validasi stok, kurangi stok, hitung total
            foreach ($cart->items as $item) {
                $product = $item->product;
                $variant = $item->variant;

                $price       = $product->price;
                $variantInfo = null;

                if ($variant) {
                    if ($variant->stock < $item->quantity) {
                        throw new \Exception("Stok produk {$product->name} (Varian: {$variant->size} - {$variant->color}) tidak mencukupi.");
                    }
                    $variant->stock -= $item->quantity;
                    $variant->save();

                    $price      += $variant->price_adjustment;
                    $variantInfo = "Size: {$variant->size} - Color: {$variant->color}";
                } else {
                    if ($product->stock < $item->quantity) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                    }
                    $product->stock -= $item->quantity;
                    $product->save();
                }

                $subtotal    = $price * $item->quantity;
                $totalPrice += $subtotal;

                $orderItems[] = [
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'product_name'       => $product->name,
                    'variant_info'       => $variantInfo,
                    'quantity'           => $item->quantity,
                    'price'              => $price,
                ];
            }

            // 3. Hitung grand total
            $shippingCost  = $data['shipping_cost'] ?? 0;
            $grandTotal    = $totalPrice + $shippingCost;
            $paymentMethod = $data['payment_method'] ?? 'COD';

            // 4. Buat Order dengan status unpaid
            $orderData = [
                'user_id'          => $userId,
                'order_number'     => 'ORD-' . strtoupper(Str::random(10)),
                'total_price'      => $totalPrice,
                'shipping_cost'    => $shippingCost,
                'grand_total'      => $grandTotal,
                'status'           => 'pending',
                'shipping_address' => $data['shipping_address'],
                'courier'          => $data['courier'] ?? null,
                'payment_method'   => $paymentMethod,
                'payment_status'   => 'unpaid',
                'notes'            => $data['notes'] ?? null,
                'items'            => $orderItems,
                'payment'          => [
                    'payment_method' => $paymentMethod,
                    'amount'         => $grandTotal,
                    'status'         => 'pending',
                ],
            ];

            $order = $this->orderRepository->create($orderData);

            // 5. Jika bukan COD atau MANUAL_BCA → buat transaksi Duitku
            $skipDuitku = in_array(strtoupper($paymentMethod), ['COD', 'MANUAL_BCA'], true);

            if (! $skipDuitku) {
                $user   = User::find($userId);
                $duitku = app(DuitkuService::class);

                // Validasi: metode ini tidak sedang maintenance
                $maintenanceMethods = config('duitku.maintenance_methods', []);
                if (in_array($paymentMethod, $maintenanceMethods, true)) {
                    throw new \Exception('Metode pembayaran ini sedang dalam pemeliharaan (maintenance). Silakan pilih metode lain.');
                }

                $duitkuResult = $duitku->createTransaction([
                    'order_number'    => $order->order_number,
                    'amount'          => $grandTotal,
                    'payment_method'  => $paymentMethod,
                    'customer_name'   => $user->name  ?? 'Customer',
                    'customer_email'  => $user->email ?? '',
                    'customer_phone'  => $user->phone ?? '',
                    'product_details' => 'Pembelian di RECORD Official Store',
                    'items'           => $orderItems,
                    'expired_minutes' => config('duitku.expiry_minutes', 1440),
                ]);

                if (! $duitkuResult['success']) {
                    // Rollback transaksi DB karena Duitku gagal
                    throw new \Exception('Gagal membuat transaksi pembayaran: ' . $duitkuResult['message']);
                }

                // Simpan info Duitku ke order
                $order->update([
                    'duitku_reference'   => $duitkuResult['reference'],
                    'duitku_payment_url' => $duitkuResult['paymentUrl'],
                    'duitku_va_number'   => $duitkuResult['vaNumber'],
                ]);

                // Pasang ke object agar tersedia di response
                $order->duitku_reference   = $duitkuResult['reference'];
                $order->duitku_payment_url = $duitkuResult['paymentUrl'];
                $order->duitku_va_number   = $duitkuResult['vaNumber'];
                $order->duitku_qr_code     = $duitkuResult['qrCode'] ?? null;
            }

            // 6. Kosongkan keranjang
            $cart->items()->delete();

            return $order;
        });
    }

    public function requestReturnOrCancellation(int $userId, int $orderId, array $data)
    {
        $order = $this->orderRepository->findById($orderId);

        if ($order->user_id !== $userId) {
            throw new \Exception('Akses ditolak. Anda tidak berhak membatalkan pesanan ini.');
        }

        if ($data['type'] === 'cancellation' && $order->status !== 'pending') {
            throw new \Exception('Pesanan hanya dapat dibatalkan jika status masih Pending.');
        }

        if ($data['type'] === 'return' && $order->status !== 'completed') {
            throw new \Exception('Pengembalian barang hanya dapat diajukan jika pesanan sudah Selesai.');
        }

        return OrderReturn::create([
            'order_id' => $order->id,
            'user_id'  => $userId,
            'type'     => $data['type'],
            'reason'   => $data['reason'],
            'status'   => 'pending',
        ]);
    }
}
