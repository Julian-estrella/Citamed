<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminUser = User::where('name', 'Julian Estrella')->first();

        if ($adminUser) {
            $adminUser->update([
                'role' => 'administrador',
                'password' => $adminUser->password ?: bcrypt('admin123'),
            ]);
        }

        User::where('name', '!=', 'Julian Estrella')
            ->where('role', 'administrador')
            ->update(['role' => 'recepcion']);

        User::updateOrCreate(
            ['email' => 'medico@medico.com'],
            [
                'name' => 'Dr. Médico',
                'password' => bcrypt('password'),
                'role' => 'medico',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'recepcion@recepcion.com'],
            [
                'name' => 'Recepcionista',
                'password' => bcrypt('password'),
                'role' => 'recepcion',
                'email_verified_at' => now(),
            ]
        );
    }
}
