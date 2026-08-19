<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminWebRoleController extends Controller
{
    /** Role bawaan sistem yang tidak boleh dihapus atau diganti namanya. */
    private const PROTECTED_ROLES = ['super_admin', 'admin', 'customer'];

    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderByRaw("FIELD(name, 'super_admin', 'admin', 'customer') DESC")
            ->orderBy('name')
            ->get();

        return view('admin.roles', [
            'roles'           => $roles,
            'permissionGroups' => PermissionCatalog::grouped(),
            'protectedRoles'  => self::PROTECTED_ROLES,
            'totalPermissions' => Permission::count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')],
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ], [
            'name.regex'  => 'Nama role hanya boleh huruf kecil, angka, dan garis bawah. Contoh: staff_gudang',
            'name.unique' => 'Role dengan nama ini sudah ada.',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        $this->flushPermissionCache();

        return redirect()->route('admin.roles')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat dengan " . count($data['permissions'] ?? []) . ' hak akses.');
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $rules = [
            'permissions'   => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];

        // Nama role bawaan dikunci supaya pengecekan @can di seluruh aplikasi tidak rusak
        if (! $this->isProtected($role)) {
            $rules['name'] = ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role->id)];
        }

        $data = $request->validate($rules, [
            'name.regex' => 'Nama role hanya boleh huruf kecil, angka, dan garis bawah.',
        ]);

        if (isset($data['name'])) {
            $role->name = $data['name'];
            $role->save();
        }

        // Super admin selalu memegang seluruh permission — mencegah admin mengunci dirinya sendiri
        if ($role->name === 'super_admin') {
            $role->syncPermissions(Permission::pluck('name'));
            $this->flushPermissionCache();

            return redirect()->route('admin.roles')
                ->with('success', 'Role super_admin selalu memiliki seluruh hak akses, jadi daftarnya dikembalikan ke lengkap.');
        }

        $role->syncPermissions($data['permissions'] ?? []);
        $this->flushPermissionCache();

        return redirect()->route('admin.roles')
            ->with('success', "Hak akses role \"{$role->name}\" berhasil diperbarui.");
    }

    public function destroy(int $id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($this->isProtected($role)) {
            return redirect()->route('admin.roles')
                ->with('error', "Role \"{$role->name}\" adalah role bawaan sistem dan tidak bisa dihapus.");
        }

        if ($role->users_count > 0) {
            return redirect()->route('admin.roles')
                ->with('error', "Role \"{$role->name}\" masih dipakai {$role->users_count} pengguna. Pindahkan pengguna tersebut ke role lain dulu.");
        }

        $name = $role->name;
        $role->delete();
        $this->flushPermissionCache();

        return redirect()->route('admin.roles')->with('success', "Role \"{$name}\" berhasil dihapus.");
    }

    /**
     * Daftar pengguna yang memegang sebuah role (dipakai modal "Lihat Pengguna").
     */
    public function users(int $id)
    {
        $role = Role::findOrFail($id);

        $users = User::role($role->name)
            ->select('id', 'name', 'email', 'role', 'avatar')
            ->orderBy('name')
            ->get();

        return view('admin.roles-users', compact('role', 'users'));
    }

    private function isProtected(Role $role): bool
    {
        return in_array($role->name, self::PROTECTED_ROLES, true);
    }

    private function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
