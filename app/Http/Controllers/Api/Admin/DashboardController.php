<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use App\Http\Resources\Admin\OrderResource;
use App\Http\Resources\Admin\ActivityLogResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): JsonResponse
    {
        $summary = $this->dashboardService->getSummary();

        return response()->json([
            'summary' => [
                'total_sales' => $summary['total_sales'],
                'total_orders' => $summary['total_orders'],
                'total_customers' => $summary['total_customers'],
                'total_products' => $summary['total_products'],
                'status_counts' => $summary['status_counts'],
            ],
            'recent_orders' => OrderResource::collection($summary['recent_orders']),
            'recent_activities' => ActivityLogResource::collection($summary['recent_activities']),
        ]);
    }
}
