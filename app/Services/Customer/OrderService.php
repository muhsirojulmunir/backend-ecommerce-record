<?php

namespace App\Services\Customer;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Models\ProductVariant;
use App\Models\OrderReturn;
use App\Models\Cart;
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
            // 1. Get or create Cart
            $cart = Cart::with('items.product', 'items.variant')->where('user_id', $userId)->first();
            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            $orderItems = [];
            $totalPrice = 0;

            // 2. Process cart items, validate stock, calculate totals
            foreach ($cart->items as $item) {
                $product = $item->product;
                $variant = $item->variant;

                $price = $product->price;
                $variantInfo = null;

                if ($variant) {
                    if ($variant->stock < $item->quantity) {
                        throw new \Exception("Stok produk {$product->name} (Varian: {$variant->size} - {$variant->color}) tidak mencukupi.");
                    }
                    // Subtract variant stock
                    $variant->stock -= $item->quantity;
                    $variant->save();

                    // Adjust price if needed
                    $price += $variant->price_adjustment;
                    $variantInfo = "Size: {$variant->size} - Color: {$variant->color}";
                } else {
                    if ($product->stock < $item->quantity) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                    }
                    $product->stock -= $item->quantity;
                    $product->save();
                }

                $subtotal = $price * $item->quantity;
                $totalPrice += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'product_name' => $product->name,
                    'variant_info' => $variantInfo,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ];
            }

            // Calculate Grand Total
            $shippingCost = $data['shipping_cost'] ?? 0;
            $grandTotal = $totalPrice + $shippingCost;

            // 3. Create Order
            $orderData = [
                'user_id' => $userId,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_price' => $totalPrice,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'courier' => $data['courier'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'COD',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'items' => $orderItems,
            ];

            // Scaffold payment if not COD or ready for gateway
            $orderData['payment'] = [
                'payment_method' => $orderData['payment_method'],
                'amount' => $grandTotal,
                'status' => 'pending',
            ];

            $order = $this->orderRepository->create($orderData);

            // 4. Clear the cart
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

        // Validate state
        if ($data['type'] === 'cancellation' && $order->status !== 'pending') {
            throw new \Exception('Pesanan hanya dapat dibatalkan jika status masih Pending.');
        }

        if ($data['type'] === 'return' && $order->status !== 'completed') {
            throw new \Exception('Pengembalian barang hanya dapat diajukan jika pesanan sudah Selesai.');
        }

        return OrderReturn::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'type' => $data['type'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
    }
}
