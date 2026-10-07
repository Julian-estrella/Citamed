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

    public function test_roles_have_the_expected_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $medico = User::factory()->create(['role' => 'medico']);
        $recepcion = User::factory()->create(['role' => 'recepcion']);

        $this->assertTrue($admin->hasPermission('gestionar_usuarios'));
        $this->assertTrue($admin->hasPermission('visualizar_panel_principal'));
        $this->assertTrue($medico->hasPermission('consultar_citas'));
        $this->assertTrue($medico->hasPermission('consultar_historial_citas_pacientes'));
        $this->assertTrue($recepcion->hasPermission('registrar_pacientes'));
        $this->assertTrue($recepcion->hasPermission('programar_citas'));

        $this->assertFalse($medico->hasPermission('gestionar_usuarios'));
        $this->assertFalse($recepcion->hasPermission('consultar_panel_principal_medico'));
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
