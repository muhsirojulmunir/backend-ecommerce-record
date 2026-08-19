<?php

namespace App\Services\Admin;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Models\OrderReturn;

class OrderService
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getAllOrders(array $filters = [])
    {
        // Custom filters map to state machine
        if (isset($filters['tab'])) {
            switch ($filters['tab']) {
                case 'unpaid':
                    $filters['status'] = 'pending';
                    $filters['payment_status'] = 'unpaid';
                    break;
                case 'processing':
                    $filters['status'] = 'processing';
                    break;
                case 'shipped':
                    $filters['status'] = 'shipped';
                    break;
                case 'completed':
                    $filters['status'] = 'completed';
                    break;
                case 'cancelled':
                    $filters['status'] = 'cancelled';
                    break;
                case 'returned':
                    // handled specifically
                    break;
            }
        }

        if (isset($filters['tab']) && $filters['tab'] === 'returned') {
            return OrderReturn::with(['order', 'user'])->orderBy('id', 'desc')->paginate(15);
        }

        return $this->orderRepository->paginate(15, $filters);
    }

    public function getOrderById(int $id)
    {
        return $this->orderRepository->findById($id);
    }

    public function updateOrderStatus(int $id, array $data)
    {
        $order = $this->orderRepository->findById($id);

        if (isset($data['status'])) {
            $order->status = $data['status'];
        }

        if (isset($data['payment_status'])) {
            $order->payment_status = $data['payment_status'];
        }

        if (isset($data['tracking_number'])) {
            $order->tracking_number = $data['tracking_number'];
            // If tracking number is set, automatically advance status to shipped
            $order->status = 'shipped';
        }

        $order->save();
        return $order->load(['user', 'items', 'payment']);
    }

    public function handleReturnRequest(int $returnId, array $data)
    {
        $request = OrderReturn::findOrFail($returnId);
        $request->status = $data['status']; // approved or rejected
        $request->admin_notes = $data['admin_notes'] ?? null;
        $request->resolved_at = now();
        $request->save();

        // Update the order accordingly
        if ($data['status'] === 'approved') {
            $order = $request->order;
            if ($request->type === 'cancellation') {
                $order->status = 'cancelled';
                $order->payment_status = 'unpaid'; // refund state or cancelled
            } else {
                $order->status = 'cancelled';
                $order->payment_status = 'refunded';
            }
            $order->save();
        }

        return $request->load(['order', 'user']);
    }
}
