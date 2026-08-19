<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Services\Admin\RolePermissionService;
use App\Http\Resources\Admin\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    public function index(): JsonResponse
    {
        $roles = $this->rolePermissionService->getRoles();

        return response()->json([
            'roles' => RoleResource::collection($roles),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->rolePermissionService->createRole($request->validated());

        return response()->json([
            'message' => 'Role berhasil ditambahkan.',
            'role' => new RoleResource($role),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = $this->rolePermissionService->updateRole($id, $request->all());

        return response()->json([
            'message' => 'Role berhasil diperbarui.',
            'role' => new RoleResource($role),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->rolePermissionService->deleteRole($id);
            return response()->json([
                'message' => 'Role berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
