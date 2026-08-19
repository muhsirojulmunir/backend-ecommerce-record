<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RolePermissionService;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    protected $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    public function index(): JsonResponse
    {
        $permissions = $this->rolePermissionService->getPermissions();

        return response()->json([
            'permissions' => $permissions->pluck('name'),
        ]);
    }
}
