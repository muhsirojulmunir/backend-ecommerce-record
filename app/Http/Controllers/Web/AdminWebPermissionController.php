<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminWebPermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::withCount('roles')->orderBy('id')->get();
        $roles       = Role::with('permissions')->orderBy('id')->get();

        // Matriks role × permission untuk tabel centang di halaman
        $matrix = $roles->mapWithKeys(fn (Role $role) => [
            $role->id => $role->permissions->pluck('name')->all(),
        ]);

        return view('admin.permissions', [
            'permissionGroups' => PermissionCatalog::grouped(),
            'permissions'      => $permissions,
            'roles'            => $roles,
            'matrix'           => $matrix,
            'stats'            => [
                'total'    => $permissions->count(),
                'custom'   => $permissions->filter(fn ($p) => PermissionCatalog::describe($p)['is_custom'])->count(),
                'unused'   => $permissions->where('roles_count', 0)->count(),
                'roles'    => $roles->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:80', 'regex:/^[a-z0-9 _-]+$/', Rule::unique('permissions', 'name')],
            'roles'   => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ], [
            'name.regex'  => 'Nama permission hanya boleh huruf kecil, angka, spasi, dan tanda hubung. Contoh: manage returns',
            'name.unique' => 'Permission dengan nama ini sudah ada.',
        ]);

        $permission = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);

        // Super admin otomatis mendapat setiap permission baru
        $roleIds = collect($data['roles'] ?? []);
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $roleIds->push($superAdmin->id);
        }

        foreach (Role::whereIn('id', $roleIds->unique())->get() as $role) {
            $role->givePermissionTo($permission);
        }

        $this->flushPermissionCache();

        return redirect()->route('admin.permissions')
            ->with('success', "Permission \"{$permission->name}\" berhasil dibuat.");
    }

    public function update(Request $request, int $id)
    {
        $permission = Permission::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9 _-]+$/', Rule::unique('permissions', 'name')->ignore($permission->id)],
        ], [
            'name.regex' => 'Nama permission hanya boleh huruf kecil, angka, spasi, dan tanda hubung.',
        ]);

        // Permission bawaan dipakai langsung di kode (@can / middleware), jadi namanya dikunci
        if (! PermissionCatalog::describe($permission)['is_custom']) {
            return redirect()->route('admin.permissions')
                ->with('error', "Permission \"{$permission->name}\" dipakai langsung di dalam kode aplikasi, jadi namanya tidak bisa diubah. Hanya permission buatan sendiri yang bisa diganti nama.");
        }

        $old = $permission->name;
        $permission->update(['name' => $data['name']]);
        $this->flushPermissionCache();

        return redirect()->route('admin.permissions')
            ->with('success', "Permission \"{$old}\" berhasil diganti nama menjadi \"{$permission->name}\".");
    }

    public function destroy(int $id)
    {
        $permission = Permission::withCount('roles')->findOrFail($id);

        if (! PermissionCatalog::describe($permission)['is_custom']) {
            return redirect()->route('admin.permissions')
                ->with('error', "Permission \"{$permission->name}\" adalah bawaan sistem dan dipakai langsung di kode, jadi tidak bisa dihapus.");
        }

        $name = $permission->name;
        $permission->delete();
        $this->flushPermissionCache();

        return redirect()->route('admin.permissions')
            ->with('success', "Permission \"{$name}\" berhasil dihapus.");
    }

    /**
     * Simpan seluruh matriks role × permission sekaligus.
     */
    public function syncMatrix(Request $request)
    {
        $data = $request->validate([
            'matrix'     => ['array'],
            'matrix.*'   => ['array'],
            'matrix.*.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $submitted = $data['matrix'] ?? [];
        $changed   = 0;

        foreach (Role::all() as $role) {
            // Super admin dikunci penuh supaya akses tidak bisa terkunci dari dalam
            if ($role->name === 'super_admin') {
                $role->syncPermissions(Permission::pluck('name'));
                continue;
            }

            $wanted  = collect($submitted[$role->id] ?? [])->unique()->sort()->values();
            $current = $role->permissions->pluck('name')->sort()->values();

            if ($wanted->all() !== $current->all()) {
                $role->syncPermissions($wanted->all());
                $changed++;
            }
        }

        $this->flushPermissionCache();

        return redirect()->route('admin.permissions')->with(
            'success',
            $changed > 0
                ? "Matriks hak akses tersimpan. {$changed} role diperbarui."
                : 'Tidak ada perubahan pada matriks hak akses.'
        );
    }

    private function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
