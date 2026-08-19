<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getCustomersFinancials(array $filters = [])
    {
        $query = User::where('role', 'customer')
            ->withCount(['orders' => function ($q) {
                $q->where('payment_status', 'paid');
            }])
            ->withSum(['orders' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'grand_total');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('orders_sum_grand_total', 'desc')->paginate(15);
    }

    public function getSalesReport(array $filters = [])
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();

        // Total sales inside range
        $sales = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $totalRevenue = $sales->sum('grand_total');
        $totalOrders = $sales->count();

        // Sales by day
        $dailySales = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(grand_total) as revenue'),
            DB::raw('COUNT(id) as orders_count')
        )
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Top Selling Products
        $topProducts = OrderItem::select(
            'product_name',
            DB::raw('SUM(quantity) as units_sold'),
            DB::raw('SUM(price * quantity) as total_sales')
        )
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('payment_status', 'paid')
                  ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->groupBy('product_name')
            ->orderBy('units_sold', 'desc')
            ->limit(10)
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'daily_sales' => $dailySales,
            'top_products' => $topProducts,
        ];
    }
}
