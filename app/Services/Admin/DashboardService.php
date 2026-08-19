<?php

namespace App\Services\Admin;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use Spatie\Activitylog\Models\Activity;
use App\Models\Product;
use App\Models\User;

class DashboardService
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getSummary()
    {
        // 1. Total sales and total orders
        $orders = $this->orderRepository->all();
        $totalSales = $orders->where('payment_status', 'paid')->sum('grand_total');
        $totalOrdersCount = $orders->count();

        // 2. Count by status
        $statusCounts = $this->orderRepository->countByStatus();

        // 3. Total customers and products
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();

        // 4. Recent activity log
        $recentActivities = Activity::with('causer')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // 5. Recent orders
        $recentOrders = $orders->take(5);

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrdersCount,
            'total_customers' => $totalCustomers,
            'total_products' => $totalProducts,
            'status_counts' => $statusCounts,
            'recent_orders' => $recentOrders,
            'recent_activities' => $recentActivities,
        ];
    }
}
