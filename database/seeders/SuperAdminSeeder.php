<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super admin',
                'password' => Hash::make('Aa#12345'),
                'role' => 'administrador',
                'is_active' => true,
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}