<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@sellercenter.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Create or update Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@sellercenter.com'],
            [
                'name' => 'Admin Toko',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $admin->assignRole('admin');
    }
}
