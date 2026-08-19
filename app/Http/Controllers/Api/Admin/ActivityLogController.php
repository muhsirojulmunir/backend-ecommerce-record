<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use App\Http\Resources\Admin\ActivityLogResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = Activity::with('causer')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'logs' => ActivityLogResource::collection($logs),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ]
        ]);
    }
}
