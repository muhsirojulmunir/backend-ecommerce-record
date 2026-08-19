<?php

namespace App\Services\Admin;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionService
{
    public function getRoles()
    {
        return Role::with('permissions')->get();
    }

    public function getPermissions()
    {
        return Permission::all();
    }

    public function createRole(array $data)
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function updateRole(int $id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->name = $data['name'];
        $role->save();

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function deleteRole(int $id): bool
    {
        $role = Role::findOrFail($id);
        if (in_array($role->name, ['super_admin', 'admin'])) {
            throw new \Exception('Role bawaan tidak boleh dihapus.');
        }
        return $role->delete();
    }

    public function assignPermissionsToRole(int $roleId, array $permissions)
    {
        $role = Role::findOrFail($roleId);
        $role->syncPermissions($permissions);
        return $role->load('permissions');
    }
}
