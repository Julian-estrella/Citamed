<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Patient;
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

    public function test_section_routes_are_limited_to_roles_with_the_required_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $doctor = User::factory()->create(['role' => 'medico']);
        $reception = User::factory()->create(['role' => 'recepcion']);

        $this->actingAs($admin)->get(route('admin.panel'))
            ->assertOk()
            ->assertSee('Panel administrativo')
            ->assertSee('Gestión de usuarios')
            ->assertSee('Pacientes')
            ->assertSee('Médicos')
            ->assertSee('Citas')
            ->assertSee('Agenda');
        $this->actingAs($admin)->get(route('admin.patients'))->assertOk()->assertSee('No hay pacientes para mostrar');
        $this->actingAs($admin)->get(route('admin.doctors'))->assertOk();
        $this->actingAs($admin)->get(route('admin.appointments'))->assertOk();
        $this->actingAs($admin)->get(route('admin.agenda'))->assertOk();

        $this->actingAs($doctor)->get(route('medico.patients'))->assertOk()->assertSee('Mis pacientes');
        $this->actingAs($doctor)->get(route('medico.appointments'))->assertOk()->assertSee('Mis citas');
        $this->actingAs($doctor)->get(route('medico.agenda'))->assertOk();
        $this->actingAs($doctor)->get(route('medico.availability'))->assertOk();
        $this->actingAs($doctor)->get(route('admin.patients'))->assertForbidden();

        $this->actingAs($reception)->get(route('recepcion.patients'))->assertOk();
        $this->actingAs($reception)->get(route('recepcion.doctors'))->assertOk();
        $this->actingAs($reception)->get(route('recepcion.appointments'))->assertOk();
        $this->actingAs($reception)->get(route('recepcion.agenda'))->assertOk();
        $this->actingAs($reception)->get(route('medico.patients'))->assertForbidden();
    }

    public function test_dashboard_counts_and_lists_follow_active_patients_and_today_appointments(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $doctor = User::factory()->create(['role' => 'medico', 'name' => 'Doctora Activa']);
        $patient = Patient::create(['name' => 'Paciente Dinamico', 'is_active' => true]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => now()->startOfDay()->addHours(10),
            'reason' => 'Control',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($admin)->get(route('admin.panel'))
            ->assertOk()
            ->assertSee('Paciente Dinamico')
            ->assertSee('Doctora Activa')
            ->assertSee('Pacientes activos')
            ->assertSee('Citas hoy')
            ->assertSee('Médicos activos');

        $this->actingAs($admin)->patch(route('admin.patients.toggle-status', $patient))->assertRedirect();

        $this->actingAs($admin)->get(route('admin.panel'))
            ->assertOk()
            ->assertDontSee('Paciente Dinamico');

        $this->actingAs($admin)->patch(route('admin.appointments.cancel', $appointment))->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }

    public function test_reception_can_register_patients_and_schedule_then_cancel_appointments(): void
    {
        $reception = User::factory()->create(['role' => 'recepcion']);
        $doctor = User::factory()->create(['role' => 'medico']);

        $this->actingAs($reception)->post(route('recepcion.patients.store'), [
            'name' => 'Nuevo Paciente',
            'email' => 'paciente@example.test',
            'phone' => '55512345',
        ])->assertRedirect(route('recepcion.patients'));

        $patient = Patient::where('email', 'paciente@example.test')->firstOrFail();

        $this->actingAs($reception)->post(route('recepcion.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => now()->addHour()->format('Y-m-d\\TH:i'),
            'reason' => 'Consulta inicial',
        ])->assertRedirect(route('recepcion.appointments'));

        $appointment = Appointment::firstOrFail();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($reception)->patch(route('recepcion.appointments.cancel', $appointment))->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }
}
