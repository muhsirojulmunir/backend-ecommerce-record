<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function all(array $filters = [])
    {
        $query = Order::with(['user', 'items.product', 'items.variant', 'payment', 'returnRequest', 'returns']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (filled($filters['search'] ?? null)) {
            $kata = trim($filters['search']);

            // Dicari pada nomor pesanan, NOMOR RESI, atau nama pembeli.
            $query->where(function ($q) use ($kata) {
                $q->where('order_number', 'like', "%{$kata}%")
                  ->orWhere('tracking_number', 'like', "%{$kata}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$kata}%"));
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Order::with(['user', 'items.product', 'items.variant', 'payment', 'returnRequest', 'returns'])->findOrFail($id);
    }

    public function findByOrderNumber(string $orderNumber)
    {
        return Order::with(['user', 'items.product', 'items.variant', 'payment', 'returnRequest', 'returns'])
            ->where('order_number', $orderNumber)->firstOrFail();
    }

    public function create(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create($item);
                }
            }

            if (isset($data['payment'])) {
                $order->payment()->create($data['payment']);
            }

            return $order->load(['items', 'payment']);
        });
    }

    public function updateStatus(int $id, string $status): bool
    {
        $order = Order::findOrFail($id);
        $order->status = $status;
        return $order->save();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = Order::with(['user', 'items.product', 'items.variant', 'payment', 'returnRequest', 'returns']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (filled($filters['search'] ?? null)) {
            $kata = trim($filters['search']);

            // Dicari pada nomor pesanan, NOMOR RESI, atau nama pembeli.
            $query->where(function ($q) use ($kata) {
                $q->where('order_number', 'like', "%{$kata}%")
                  ->orWhere('tracking_number', 'like', "%{$kata}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$kata}%"));
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function getByUserId(int $userId)
    {
        return Order::with(['items.product', 'items.variant', 'payment', 'returnRequest'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function countByStatus(): array
    {
        return [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'returned' => Order::whereHas('returnRequest')->count(),
        ];
    }
}
