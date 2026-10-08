<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_panels_allowed_by_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'administrador',
        ]);

        $doctor = User::factory()->create([
            'role' => 'medico',
        ]);

        $recepcionista = User::factory()->create([
            'role' => 'recepcion',
        ]);

        $this->assertTrue($admin->canAccessPanel('admin.panel'));
        $this->assertFalse($admin->canAccessPanel('medico.dashboard'));

        $this->assertTrue($doctor->canAccessPanel('medico.dashboard'));
        $this->assertFalse($doctor->canAccessPanel('admin.panel'));

        $this->assertTrue($recepcionista->canAccessPanel('recepcion.dashboard'));
        $this->assertFalse($recepcionista->canAccessPanel('admin.panel'));
    }

    public function test_dashboard_redirects_each_role_to_its_own_panel(): void
    {
        $roles = [
            'administrador' => 'admin.panel',
            'medico' => 'medico.dashboard',
            'recepcion' => 'recepcion.dashboard',
        ];

        foreach ($roles as $role => $panelRoute) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertRedirect(route($panelRoute));
        }
    }

    public function test_reception_dashboard_shows_only_actions_allowed_by_its_permissions(): void
    {
        $recepcionista = User::factory()->create(['role' => 'recepcion']);

        $this->actingAs($recepcionista)
            ->get(route('recepcion.dashboard'))
            ->assertOk()
            ->assertSee('Registrar paciente')
            ->assertSee('Programar cita')
            ->assertSee('Consultar horarios')
            ->assertDontSee('Gestión de usuarios');
    }

    public function test_doctor_dashboard_shows_profile_menu_and_permitted_actions(): void
    {
        $doctor = User::factory()->create(['role' => 'medico']);

        $this->actingAs($doctor)
            ->get(route('medico.dashboard'))
            ->assertOk()
            ->assertSee($doctor->name)
            ->assertSee('Médico')
            ->assertSee('Modificar usuario')
            ->assertSee('Cerrar sesión')
            ->assertSee('Mis citas')
            ->assertSee('Gestionar horario')
            ->assertDontSee('Registrar paciente');
    }

    public function test_reception_dashboard_shows_profile_menu(): void
    {
        $recepcionista = User::factory()->create(['role' => 'recepcion']);

        $this->actingAs($recepcionista)
            ->get(route('recepcion.dashboard'))
            ->assertOk()
            ->assertSee($recepcionista->name)
            ->assertSee('Recepción')
            ->assertSee('Modificar usuario')
            ->assertSee('Cerrar sesión');
    }
}
