<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_has_admin_role(): void
    {
        $user = User::factory()->create([
            'name' => 'Julian Estrella',
            'email' => 'julian@citamed.com',
            'role' => 'administrador',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasRole('administrador'));
        $this->assertFalse($user->hasRole('medico'));
    }

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create([
            'name' => 'Médico',
            'email' => 'medico@medico.com',
            'role' => 'medico',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(403);
    }
}
