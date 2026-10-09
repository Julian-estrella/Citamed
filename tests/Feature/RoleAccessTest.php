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
        $this->actingAs($admin)->get(route('admin.patients'))->assertOk()->assertSee('Pacientes registrados')->assertSee('Nuevo paciente');
        $this->actingAs($admin)->get(route('admin.patients.create'))->assertOk()->assertSee('Registrar paciente');
        $this->actingAs($admin)->get(route('admin.doctors'))->assertOk();
        $this->actingAs($admin)->get(route('admin.appointments'))->assertOk();
        $this->actingAs($admin)->get(route('admin.agenda'))->assertOk();

        $this->actingAs($doctor)->get(route('medico.patients'))->assertOk()->assertSee('Mis pacientes');
        $this->actingAs($doctor)->get(route('medico.appointments'))->assertOk()->assertSee('Mis citas');
        $this->actingAs($doctor)->get(route('medico.agenda'))->assertOk();
        $this->actingAs($doctor)->get(route('medico.availability'))->assertOk();
        $this->actingAs($doctor)->get(route('admin.patients'))->assertForbidden();

        $this->actingAs($reception)->get(route('recepcion.patients'))->assertOk();
        $this->actingAs($reception)->get(route('recepcion.patients.create'))->assertOk()->assertSee('Registrar paciente');
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

    public function test_list_tables_can_filter_users_patients_and_appointments_by_column(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        User::factory()->create([
            'name' => 'Medico Encontrado',
            'email' => 'encontrado@example.test',
            'role' => 'medico',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Medico Excluido',
            'role' => 'medico',
            'is_active' => false,
        ]);

        $this->actingAs($admin)->get(route('admin.users', [
            'name' => 'Encontrado',
            'role' => 'medico',
            'status' => 'active',
        ]))
            ->assertOk()
            ->assertSee('Medico Encontrado')
            ->assertDontSee('Medico Excluido');

        $this->get(route('admin.doctors', ['name' => 'Medico Encontrado']))
            ->assertOk()
            ->assertSee('Medico Encontrado')
            ->assertDontSee('Medico Excluido');

        Patient::create([
            'name' => 'Paciente Encontrado',
            'address' => 'Calle Ejemplo 123',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
            'blood_type' => 'O+',
            'is_active' => true,
        ]);
        Patient::create([
            'name' => 'Paciente Excluido',
            'birth_date' => '1990-06-15',
            'gender' => 'Masculino',
            'blood_type' => 'A+',
            'is_active' => true,
        ]);

        $this->get(route('admin.patients', [
            'address' => 'Ejemplo',
            'blood_type' => 'O+',
            'gender' => 'Femenino',
            'status' => 'active',
        ]))
            ->assertOk()
            ->assertSee('Paciente Encontrado')
            ->assertDontSee('Paciente Excluido');

        $doctor = User::factory()->create(['role' => 'medico', 'name' => 'Doctora Filtro']);
        $appointmentPatient = Patient::create(['name' => 'Paciente Cita Encontrada']);
        $otherPatient = Patient::create(['name' => 'Paciente Cita Excluida', 'is_active' => false]);
        Appointment::create([
            'patient_id' => $appointmentPatient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => '2026-10-10 10:00:00',
            'reason' => 'Control anual',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
        Appointment::create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => '2026-10-10 11:00:00',
            'reason' => 'Consulta general',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $this->actingAs($admin)->get(route('admin.appointments', [
            'scheduled_date' => '2026-10-10',
            'reason' => 'Control',
            'status' => Appointment::STATUS_SCHEDULED,
        ]))
            ->assertOk()
            ->assertSee('Paciente Cita Encontrada')
            ->assertDontSee('Paciente Cita Excluida');

        $agendaDoctor = User::factory()->create(['role' => 'medico', 'name' => 'Doctora Agenda']);
        $agendaPatient = Patient::create(['name' => 'Paciente Agenda Encontrado']);
        $otherAgendaPatient = Patient::create(['name' => 'Paciente Agenda Excluido']);
        Appointment::create([
            'patient_id' => $agendaPatient->id,
            'doctor_id' => $agendaDoctor->id,
            'scheduled_at' => today()->setTime(9, 30),
            'reason' => 'Control diario',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
        Appointment::create([
            'patient_id' => $otherAgendaPatient->id,
            'doctor_id' => $agendaDoctor->id,
            'scheduled_at' => today()->setTime(10, 0),
            'reason' => 'Consulta diaria',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->get(route('admin.agenda', [
            'scheduled_time' => '09:30',
            'status' => Appointment::STATUS_SCHEDULED,
        ]))
            ->assertOk()
            ->assertSee('Paciente Agenda Encontrado')
            ->assertDontSee('Paciente Agenda Excluido');
    }

    public function test_patient_actions_allow_editing_and_records_but_protect_assigned_access_and_deletion(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $reception = User::factory()->create(['role' => 'recepcion']);
        $doctor = User::factory()->create(['role' => 'medico']);
        $patient = Patient::create([
            'name' => 'Paciente Para Editar',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
            'address' => 'Calle Inicial 1',
            'blood_type' => 'O+',
            'is_active' => true,
        ]);
        $unassignedPatient = Patient::create([
            'name' => 'Paciente No Asignado',
            'is_active' => true,
        ]);
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => now()->addDay(),
            'reason' => 'Revisión',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($admin)->get(route('admin.patients.edit', $patient))
            ->assertOk()
            ->assertSee('Editar paciente')
            ->assertSee('Calle Inicial 1');
        $this->actingAs($admin)->get(route('admin.patients.show', $patient))
            ->assertOk()
            ->assertSee('Expediente del paciente')
            ->assertSee('Revisión');

        $this->actingAs($reception)->put(route('recepcion.patients.update', $patient), [
            'name' => 'Paciente Actualizado',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
            'address' => 'Calle Actualizada 2',
            'blood_type' => 'A+',
        ])->assertRedirect(route('recepcion.patients'));
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Paciente Actualizado',
            'address' => 'Calle Actualizada 2',
            'blood_type' => 'A+',
        ]);

        $this->actingAs($doctor)->get(route('medico.patients.show', $patient))->assertOk();
        $this->get(route('medico.patients.show', $unassignedPatient))->assertNotFound();

        $this->actingAs($admin)->delete(route('admin.patients.destroy', $patient))
            ->assertRedirect(route('admin.patients'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('patients', ['id' => $patient->id]);

        $patientWithoutAppointments = Patient::create(['name' => 'Paciente Eliminable']);
        $this->delete(route('admin.patients.destroy', $patientWithoutAppointments))
            ->assertRedirect(route('admin.patients'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('patients', ['id' => $patientWithoutAppointments->id]);
    }

    public function test_reception_can_register_patients_and_schedule_then_cancel_appointments(): void
    {
        $reception = User::factory()->create(['role' => 'recepcion']);
        $doctor = User::factory()->create(['role' => 'medico']);

        $this->actingAs($reception)->post(route('recepcion.patients.store'), [
            'name' => 'Nuevo Paciente',
            'email' => 'paciente@example.test',
            'phone' => '55512345',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
            'address' => 'Calle Salud 123',
            'emergency_contact_name' => 'Contacto de emergencia',
            'emergency_contact_relationship' => 'Hermana',
            'emergency_contact_phone' => '55567890',
            'blood_type' => 'O+',
            'allergies_conditions' => 'Alergia a la penicilina',
        ])->assertRedirect(route('recepcion.patients'));

        $patient = Patient::where('email', 'paciente@example.test')->firstOrFail();
        $this->assertSame('1990-06-15', $patient->birth_date->toDateString());
        $this->assertSame('Femenino', $patient->gender);
        $this->assertSame('Calle Salud 123', $patient->address);
        $this->assertSame('Contacto de emergencia', $patient->emergency_contact_name);
        $this->assertSame('Hermana', $patient->emergency_contact_relationship);
        $this->assertSame('55567890', $patient->emergency_contact_phone);
        $this->assertSame('O+', $patient->blood_type);
        $this->assertSame('Alergia a la penicilina', $patient->allergies_conditions);
        $this->assertSame($patient->birth_date->age, $patient->age);

        $this->actingAs($reception)->get(route('recepcion.patients', ['search' => 'Nuevo Paciente']))
            ->assertOk()
            ->assertSee('Nuevo Paciente')
            ->assertSee('O+')
            ->assertDontSee('Alergia a la penicilina')
            ->assertDontSee('Contacto de emergencia');

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
