<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Semua permission yang ada ────────────────────────────────────────
        $allPermissions = [
            // Dashboard
            'view dashboard',

            // Grup Produk
            'manage products',
            'manage banners',
            'manage discounts',
            'manage orders',
            'manage reviews',       // meninjau ulasan pembeli

            // Grup Keuangan
            'manage customers',
            'view reports',
            'manage returns',       // meninjau pengajuan pengembalian barang
            'manage rpay',          // melihat saldo & mutasi R_Pay seluruh akun
            'process withdrawals',  // memproses pencairan R_Pay ke rekening bank

            // Grup Settings (Super Admin only)
            'manage roles',
            'manage permissions',
            'manage settings',
            'view activity logs',
        ];

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Super Admin: semua permission ───────────────────────────────────
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        // ─── Admin: Grup Produk + Customer, TANPA Settings/Reports/Role ──────
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereIn('name', [
                    'manage products',
                    'manage banners',
                    'manage discounts',
                    'manage orders',
                    'manage reviews',
                    'manage customers',
                    'manage returns',
                    'process withdrawals',
                ])->get()
        );

        /*
         * ─── Management: pengawas, bukan pelaksana ───────────────────────
         *
         * Peran ini dibuat untuk memantau perkembangan penjualan, menelusuri
         * seluruh log aktivitas, dan melihat rincian R_Pay setiap akun.
         *
         * Sengaja TIDAK diberi hak mengubah produk, pesanan, atau pengaturan.
         * Pemisahan itu yang membuat perannya berguna sebagai pengawas: orang
         * yang memeriksa catatan sebaiknya bukan orang yang bisa mengubahnya.
         *
         * Hak "process withdrawals" diberikan karena kamu meminta pencairan
         * dana diproses bersama admin dan akun management.
         */
        $managementRole = Role::firstOrCreate(['name' => 'management', 'guard_name' => 'web']);
        $managementRole->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereIn('name', [
                    'view dashboard',
                    'view reports',
                    'view activity logs',
                    'manage customers',
                    'manage rpay',
                    'process withdrawals',
                ])->get()
        );
    }
}
