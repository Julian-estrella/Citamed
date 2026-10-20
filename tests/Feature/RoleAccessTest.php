<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
        $this->assertFalse($recepcionista->hasPermission('gestionar_medicos'));
        $this->assertFalse($recepcionista->hasPermission('gestionar_usuarios'));
        $this->assertTrue($recepcionista->hasPermission('consultar_horarios_disponibles_medicos'));
    }

    public function test_inactive_users_have_no_permissions_or_panel_access(): void
    {
        $inactiveUser = User::factory()->create([
            'role' => 'administrador',
            'is_active' => false,
        ]);

        $this->assertSame([], $inactiveUser->permissions());
        $this->assertSame([], $inactiveUser->availablePanels());
        $this->assertFalse($inactiveUser->hasPermission('visualizar_panel_principal'));
        $this->assertFalse($inactiveUser->canAccessPanel('admin.panel'));

        $this->actingAs($inactiveUser)
            ->get(route('admin.panel'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'email' => 'tu usuario esta inactivo contactate con administración',
            ]);
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
            ->assertSee(route('recepcion.patients.create'))
            ->assertSee(route('recepcion.appointments.create'))
            ->assertSee('Consultar horarios')
            ->assertDontSee('Gestión de usuarios');
    }

    public function test_admin_quick_actions_link_directly_to_create_forms(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);

        $this->actingAs($admin)
            ->get(route('admin.panel'))
            ->assertOk()
            ->assertSee('Registrar paciente')
            ->assertSee(route('admin.patients.create'))
            ->assertSee('Registrar médico')
            ->assertSee(route('admin.doctors.create'))
            ->assertSee('Programar cita')
            ->assertSee(route('admin.appointments.create'));
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
            ->assertSee('Programar cita')
            ->assertSee('Disponibilidad')
            ->assertDontSee(route('medico.agenda'))
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
        $this->actingAs($admin)->get(route('admin.patients'))
            ->assertOk()
            ->assertSee('Pacientes registrados')
            ->assertDontSee('Nuevo médico')
            ->assertSee('Nuevo paciente')
            ->assertSee('Hoy')
            ->assertSee('Modificar usuario')
            ->assertSee('bg-[#e8ebf4]');
        $this->actingAs($admin)->get(route('admin.patients.create'))->assertOk()->assertSee('Registrar paciente');
        $this->actingAs($admin)->get(route('admin.doctors'))->assertOk()->assertSee('Nuevo médico');
        $this->actingAs($admin)->get(route('admin.appointments'))->assertOk();
        $this->actingAs($admin)->get(route('admin.agenda'))->assertOk();

        $this->actingAs($doctor)->get(route('medico.patients'))
            ->assertOk()
            ->assertSee('Mis pacientes')
            ->assertSee('Hoy')
            ->assertSee('Cerrar sesión')
            ->assertSee('bg-[#e8ebf4]');
        $this->actingAs($doctor)->get(route('medico.appointments'))->assertOk()->assertSee('Mis citas');
        $this->actingAs($doctor)->get(route('medico.agenda'))->assertOk();
        $this->actingAs($doctor)->get(route('medico.availability'))->assertOk();
        $this->actingAs($doctor)->get(route('admin.patients'))->assertForbidden();

        $this->actingAs($reception)->get(route('recepcion.patients'))
            ->assertOk()
            ->assertSee('Hoy')
            ->assertSee('Modificar usuario')
            ->assertSee('bg-[#e8ebf4]');
        $this->actingAs($reception)->get(route('recepcion.patients.create'))->assertOk()->assertSee('Registrar paciente');
        $this->actingAs($reception)->get(route('recepcion.doctors'))
            ->assertRedirect(route('recepcion.agenda'));
        $this->get(route('recepcion.doctors.create'))->assertForbidden();
        $this->get(route('recepcion.consultorios'))->assertForbidden();
        $this->actingAs($reception)->get(route('recepcion.appointments'))->assertOk();
        $this->actingAs($reception)->get(route('recepcion.agenda'))
            ->assertOk()
            ->assertSee('Horarios disponibles por médico')
            ->assertSee('Día')
            ->assertSee('Semana')
            ->assertSee('Mes');
        $this->get(route('recepcion.agenda', ['view' => 'week']))->assertOk()->assertSee('Semana');
        $this->get(route('recepcion.agenda', ['view' => 'month']))->assertOk()->assertSee('Mes');
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

        $this->actingAs($admin)->patch(route('admin.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Paciente solicita cancelar.',
        ])->assertRedirect();
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

        $userListResponse = $this->actingAs($admin)->get(route('admin.users', [
            'search' => 'Encontrado',
            'role' => 'medico',
            'status' => 'active',
        ]))
            ->assertOk()
            ->assertSee('Medico Encontrado')
            ->assertDontSee('Nuevo médico')
            ->assertDontSee('Medico Excluido')
            ->assertSee('Hoy')
            ->assertSee('bg-[#e8ebf4]')
            ->assertDontSee('Filtrar nombre')
            ->assertDontSee('Filtrar correo')
            ->assertSee('Filtrar por rol')
            ->assertSee('Filtrar por estado')
            ->assertSee('fa-chevron-down')
            ->assertDontSee('<select', false)
            ->assertDontSee('Limpiar filtros')
            ->assertSee('aria-label="Quitar búsqueda de usuarios"', false);

        $this->get(route('admin.doctors', ['name' => 'Medico Encontrado']))
            ->assertOk()
            ->assertSee('Medico Encontrado')
            ->assertDontSee('Medico Excluido');

        $this->get(route('admin.doctors', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Medico Excluido')
            ->assertDontSee('Medico Encontrado');

        User::factory()->create([
            'name' => 'AAA Doctor',
            'email' => 'zeta@example.test',
            'role' => 'medico',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'ZZZ Doctor',
            'email' => 'alpha@example.test',
            'role' => 'medico',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Medico Correo',
            'email' => 'unico@example.test',
            'role' => 'medico',
            'is_active' => true,
        ]);

        $doctorSearchResponse = $this->get(route('admin.doctors', [
            'search' => 'unico@example.test',
            'sort' => 'email',
            'direction' => 'asc',
        ]))
            ->assertOk()
            ->assertSee('Medico Correo')
            ->assertDontSee('Medico Encontrado')
            ->assertSee('aria-label="Quitar filtro de búsqueda"', false)
            ->assertSee('name="search"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="name"', false);

        $this->assertSame(1, substr_count($doctorSearchResponse->getContent(), 'name="search"'));

        $emailSortedDoctors = $this->get(route('admin.doctors', [
            'sort' => 'email',
            'direction' => 'asc',
        ]))->assertOk()->getContent();

        $this->assertTrue(strpos($emailSortedDoctors, 'ZZZ Doctor') < strpos($emailSortedDoctors, 'AAA Doctor'));

        $nameSortedDoctors = $this->get(route('admin.doctors', [
            'sort' => 'name',
            'direction' => 'asc',
        ]))->assertOk()->getContent();

        $this->assertTrue(strpos($nameSortedDoctors, 'AAA Doctor') < strpos($nameSortedDoctors, 'ZZZ Doctor'));

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

        $patientListResponse = $this->get(route('admin.patients', [
            'blood_type' => 'O+',
            'status' => 'active',
            'search' => 'Encontrado',
        ]))
            ->assertOk()
            ->assertSee('Paciente Encontrado')
            ->assertDontSee('Paciente Excluido')
            ->assertSee('Buscar paciente por nombre...')
            ->assertSee('name="search"', false)
            ->assertSee('Filtrar por tipo de sangre')
            ->assertSee('Filtrar por estado')
            ->assertSee('fa-chevron-down')
            ->assertDontSee('<select', false)
            ->assertDontSee('Aplicar filtros')
            ->assertDontSee('Limpiar filtros')
            ->assertSee('Acciones')
            ->assertSee('aria-label="Quitar búsqueda de pacientes"', false);

        $this->assertSame(1, substr_count($patientListResponse->getContent(), 'title="Ver expediente"'));

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
            'anchor' => '2026-10-10',
            'reason' => 'Control',
            'status' => Appointment::STATUS_SCHEDULED,
        ]))
            ->assertOk()
            ->assertSee('Paciente Cita Encontrada')
            ->assertDontSee('Paciente Cita Excluida');

        $this->get(route('admin.agenda'))
            ->assertOk()
            ->assertSee('Disponibilidad médica')
            ->assertDontSee('agenda-filters')
            ->assertDontSee('No se encontraron citas para esos filtros.');
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
        $scheduledAt = now()->addDay()->startOfDay()->setTime(10, 0);
        $profile = \App\Models\DoctorProfile::create([
            'user_id' => $doctor->id,
            'professional_title' => 'Dr.',
            'license_number' => 'LIC-ROLE-TEST',
            'specialty' => 'Medicina general',
            'phone' => '11111111',
            'appointment_duration' => 30,
            'office' => 'Consultorio 1',
        ]);
        $profile->shifts()->create([
            'day_of_week' => $scheduledAt->isoWeekday(),
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        $this->actingAs($reception)->post(route('recepcion.patients.store'), [
            'name' => 'Nuevo Paciente',
            'email' => 'paciente@example.test',
            'phone' => '55512345',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
            'address' => 'Calle Salud 123',
            'emergency_contact_name' => 'Contacto de emergencia',
            'emergency_contact_relationship' => 'Otro',
            'emergency_contact_relationship_other' => 'Hermana',
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
            ->assertSee('Contacto')
            ->assertSee('Tipo de sangre')
            ->assertSee('Estado')
            ->assertDontSee('Edad / nacimiento')
            ->assertDontSee('Sexo / género')
            ->assertDontSee('Alergia a la penicilina')
            ->assertDontSee('Contacto de emergencia');

        $this->actingAs($reception)->get(route('recepcion.patients.show', $patient))
            ->assertOk()
            ->assertSee('Calle Salud 123')
            ->assertSee('Alergia a la penicilina')
            ->assertSee('Hermana');

        $this->actingAs($reception)->post(route('recepcion.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $scheduledAt->format('Y-m-d\\TH:i'),
            'reason' => 'Consulta inicial',
        ])->assertRedirect(route('recepcion.appointments'));

        $appointment = Appointment::firstOrFail();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($reception)->patch(route('recepcion.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Paciente solicita cancelar.',
        ])->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }

    public function test_patient_contacts_and_required_fields_are_validated(): void
    {
        $reception = User::factory()->create(['role' => 'recepcion']);
        $validData = [
            'name' => 'Paciente de Validación',
            'email' => 'validacion@example.test',
            'phone' => '55512345',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
            'emergency_contact_phone' => '55567890',
        ];

        $this->actingAs($reception)->post(route('recepcion.patients.store'), array_merge($validData, [
            'phone' => '555123456A',
        ]))->assertSessionHasErrors('phone');

        $this->post(route('recepcion.patients.store'), array_merge($validData, [
            'emergency_contact_phone' => '555 6789012',
        ]))->assertSessionHasErrors('emergency_contact_phone');

        $this->post(route('recepcion.patients.store'), array_merge($validData, [
            'email' => 'correo-invalido',
        ]))->assertSessionHasErrors('email');

        $this->post(route('recepcion.patients.store'), array_merge($validData, [
            'emergency_contact_relationship' => 'Otro',
        ]))->assertSessionHasErrors('emergency_contact_relationship_other');

        $this->post(route('recepcion.patients.store'), [
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
        ])->assertSessionHasErrors(['name']);

        $patient = Patient::create(['name' => 'Paciente para edición']);
        $this->put(route('recepcion.patients.update', $patient), array_merge($validData, [
            'phone' => '123456789X',
        ]))->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('patients', 1);
    }

    public function test_patient_and_doctor_contact_phones_are_limited_to_eight_digits_and_unique_across_records(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $reception = User::factory()->create(['role' => 'recepcion']);
        User::factory()->create(['phone' => '55511122']);
        Patient::create(['name' => 'Paciente con teléfono', 'phone' => '55522233']);
        $doctor = User::factory()->create(['role' => 'medico']);
        $doctor->doctorProfile()->create([
            'professional_title' => 'Dr.',
            'license_number' => 'CED-30001',
            'specialty' => 'Medicina general',
            'phone' => '55533344',
            'appointment_duration' => 30,
            'office' => 'Consultorio 1',
        ]);

        $userData = [
            'name' => 'Nuevo usuario',
            'email' => 'nuevo@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role' => 'recepcion',
            'is_active' => '1',
        ];
        $patientData = [
            'name' => 'Paciente nuevo',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
        ];
        $this->actingAs($reception)->post(route('recepcion.patients.store'), $patientData + ['phone' => '55511122'])
            ->assertSessionHasErrors('phone');
        $this->post(route('recepcion.patients.store'), $patientData + ['phone' => '123456789'])
            ->assertSessionHasErrors('phone');

        $doctorData = [
            'name' => 'Doctora nueva',
            'email' => 'doctora.nueva@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'professional_title' => 'Dra.',
            'license_number' => 'CED-30002',
            'specialty' => 'Medicina general',
            'appointment_duration' => 30,
            'office' => 'Consultorio 2',
            'is_active' => '1',
            'shifts' => [['days' => ['1'], 'session' => 'matutino']],
        ];
        $this->actingAs($admin)->post(route('admin.doctors.store'), $doctorData + ['phone' => '55522233'])
            ->assertSessionHasErrors('phone');
        $this->post(route('admin.doctors.store'), $doctorData + ['phone' => '123456789'])
            ->assertSessionHasErrors('phone');
    }

    public function test_user_management_does_not_collect_display_or_update_phone_numbers(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $existingUser = User::factory()->create([
            'name' => 'Usuario existente',
            'email' => 'usuario.existente@example.test',
            'phone' => '55511122',
        ]);

        $this->actingAs($admin)->get(route('admin.users.create'))
            ->assertOk()
            ->assertDontSee('name="phone"', false);
        $this->get(route('admin.users.edit', $existingUser))
            ->assertOk()
            ->assertDontSee('name="phone"', false);
        $this->get(route('admin.users'))
            ->assertOk()
            ->assertDontSee('Teléfono')
            ->assertDontSee('55511122');

        $this->post(route('admin.users.store'), [
            'name' => 'Usuario nuevo',
            'email' => 'usuario.nuevo@example.test',
            'phone' => '55522233',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role' => 'recepcion',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'email' => 'usuario.nuevo@example.test',
            'phone' => null,
        ]);

        $this->put(route('admin.users.update', $existingUser), [
            'name' => $existingUser->name,
            'email' => $existingUser->email,
            'phone' => '55533344',
            'role' => $existingUser->role,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'phone' => '55511122',
        ]);
    }

    public function test_duplicate_user_and_patient_identity_fields_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $existingUser = User::factory()->create([
            'name' => 'Usuario Duplicado',
            'email' => 'usuario.duplicado@example.test',
        ]);
        $newUserData = [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo.usuario@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role' => 'medico',
            'is_active' => '1',
        ];

        $this->actingAs($admin)->post(route('admin.users.store'), array_merge($newUserData, [
            'name' => $existingUser->name,
        ]))->assertSessionHasErrors('name');

        $this->post(route('admin.users.store'), array_merge($newUserData, [
            'email' => $existingUser->email,
        ]))->assertSessionHasErrors('email');

        $this->put(route('admin.users.update', $existingUser), [
            'name' => $existingUser->name,
            'email' => $existingUser->email,
            'role' => $existingUser->role,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users'));

        $reception = User::factory()->create(['role' => 'recepcion']);
        $existingPatient = Patient::create([
            'name' => 'Paciente Duplicado',
            'email' => 'paciente.duplicado@example.test',
            'phone' => '55522233',
            'birth_date' => '1990-06-15',
            'gender' => 'Femenino',
        ]);
        $newPatientData = [
            'name' => 'Paciente Nuevo',
            'email' => 'paciente.nuevo@example.test',
            'phone' => '55544466',
            'birth_date' => '1990-06-15',
            'gender' => 'Masculino',
        ];

        $this->actingAs($reception)->post(route('recepcion.patients.store'), array_merge($newPatientData, [
            'name' => $existingPatient->name,
        ]))->assertSessionHasErrors('name');

        $this->post(route('recepcion.patients.store'), array_merge($newPatientData, [
            'email' => $existingPatient->email,
        ]))->assertSessionHasErrors('email');

        $this->post(route('recepcion.patients.store'), array_merge($newPatientData, [
            'phone' => $existingPatient->phone,
        ]))->assertSessionHasErrors('phone');

        $this->put(route('recepcion.patients.update', $existingPatient), [
            'name' => $existingPatient->name,
            'email' => $existingPatient->email,
            'phone' => $existingPatient->phone,
            'birth_date' => $existingPatient->birth_date->toDateString(),
            'gender' => $existingPatient->gender,
        ])->assertRedirect(route('recepcion.patients'));
    }

    public function test_new_doctor_accounts_redirect_to_professional_profile_setup(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $payload = [
            'name' => 'Doctora Nueva',
            'email' => 'doctora.nueva@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role' => 'medico',
            'is_active' => '1',
        ];

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $payload);
        $doctor = User::where('email', $payload['email'])->firstOrFail();

        $response->assertRedirect(route('admin.doctors.edit', $doctor));
        $this->assertDatabaseHas('users', ['id' => $doctor->id, 'role' => 'medico']);

        $this->get(route('admin.doctors.edit', $doctor))
            ->assertOk()
            ->assertSee('Completar registro médico')
            ->assertSee('Cédula profesional')
            ->assertSee('Días y turnos de atención');
    }

    public function test_new_users_can_have_multiple_roles_and_doctors_still_redirect_to_profile_setup(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $payload = [
            'name' => 'Usuario Médico Administrador',
            'email' => 'usuario.medico@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['administrador', 'medico'],
            'is_active' => '1',
        ];

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $payload);
        $user = User::where('email', $payload['email'])->firstOrFail();

        $response->assertRedirect(route('admin.doctors.edit', $user));
        $this->assertSame(['administrador', 'medico'], $user->assignedRoles());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isMedico());
        $this->assertTrue($user->hasPermission('gestionar_usuarios'));
        $this->assertTrue($user->hasPermission('consultar_citas'));
        $this->actingAs($admin)->get(route('admin.doctors'))
            ->assertOk()
            ->assertSee($user->name);

        $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['medico', 'recepcion'],
            'is_active' => '1',
        ])->assertRedirect(route('admin.users'));

        $user->refresh();
        $this->assertSame(['medico', 'recepcion'], $user->assignedRoles());
        $this->assertTrue($user->isMedico());
        $this->assertTrue($user->isRecepcion());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->hasPermission('gestionar_disponibilidad'));
        $this->assertTrue($user->hasPermission('registrar_pacientes'));
        $this->assertTrue($user->hasPermission('programar_citas'));
        $this->assertFalse($user->hasPermission('gestionar_usuarios'));
        $panelKeys = array_column($user->availablePanels(), 'key');
        $this->assertContains('medico.dashboard', $panelKeys);
        $this->assertContains('recepcion.dashboard', $panelKeys);
    }

    public function test_doctor_profile_validates_identity_fields_phone_and_office_schedule_conflicts(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $firstDoctor = User::factory()->create(['role' => 'medico']);
        $secondDoctor = User::factory()->create(['role' => 'medico']);
        $profileData = [
            'professional_title' => 'Dra.',
            'license_number' => 'CED-10001',
            'specialty' => 'Medicina general',
            'phone' => '12345678',
            'appointment_duration' => '30',
            'office' => 'Consultorio 1',
            'is_active' => '1',
            'shifts' => [['days' => ['1'], 'session' => 'matutino']],
        ];

        $this->actingAs($admin)->put(route('admin.doctors.update', $firstDoctor), $profileData)
            ->assertRedirect(route('admin.doctors'));

        $this->put(route('admin.doctors.update', $secondDoctor), array_merge($profileData, [
            'license_number' => 'CED-10002',
            'phone' => '87654321',
            'shifts' => [['days' => ['1'], 'session' => 'matutino']],
        ]))->assertSessionHasErrors('shifts.0.days');

        $this->put(route('admin.doctors.update', $secondDoctor), array_merge($profileData, [
            'license_number' => 'CED-10002',
            'phone' => '12345678',
            'office' => 'Consultorio 2',
        ]))->assertSessionHasErrors('phone');

        $this->put(route('admin.doctors.update', $secondDoctor), array_merge($profileData, [
            'license_number' => 'CED-10001',
            'phone' => '87654321',
            'office' => 'Consultorio 2',
        ]))->assertRedirect(route('admin.doctors'));

        $this->assertDatabaseHas('doctor_profiles', [
            'user_id' => $secondDoctor->id,
            'license_number' => 'MG000002',
        ]);

        $this->put(route('admin.doctors.update', $secondDoctor), array_merge($profileData, [
            'license_number' => 'CED-10002',
            'phone' => '123456789',
            'office' => 'Consultorio 2',
        ]))->assertSessionHasErrors('phone');

        $this->put(route('admin.doctors.update', $secondDoctor), array_merge($profileData, [
            'license_number' => 'CED-10002',
            'phone' => '1234',
            'office' => 'Consultorio 2',
        ]))->assertRedirect(route('admin.doctors'));

        $this->assertDatabaseHas('doctor_profiles', [
            'user_id' => $secondDoctor->id,
            'phone' => '1234',
        ]);

        $this->get(route('admin.doctors', [
            'specialty' => 'Medicina general',
            'office' => 'Consultorio 2',
        ]))
            ->assertOk()
            ->assertSee($secondDoctor->name)
            ->assertDontSee($firstDoctor->name);
    }

    public function test_only_admin_can_register_doctors_with_fixed_offices_and_shifts(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $reception = User::factory()->create(['role' => 'recepcion']);
        $doctorData = [
            'name' => 'Doctora Consultorio Uno',
            'email' => 'doctora.uno@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'professional_title' => 'Dra.',
            'license_number' => 'CED-20001',
            'specialty' => 'Medicina general',
            'phone' => '76543210',
            'appointment_duration' => '30',
            'office' => 'Consultorio 1',
            'is_active' => '1',
            'shifts' => [['days' => ['1'], 'session' => 'matutino']],
        ];

        $this->actingAs($admin)->post(route('admin.doctors.store'), $doctorData)
            ->assertRedirect(route('admin.doctors'));

        $firstDoctor = User::where('email', $doctorData['email'])->firstOrFail();
        $this->assertDatabaseHas('doctor_profiles', [
            'user_id' => $firstDoctor->id,
            'license_number' => 'MG000001',
        ]);
        $this->assertDatabaseHas('doctor_schedules', [
            'doctor_profile_id' => $firstDoctor->doctorProfile->id,
            'day_of_week' => 1,
            'start_time' => '07:00',
            'end_time' => '14:00',
        ]);

        $this->actingAs($reception)->post(route('recepcion.doctors.store'), $doctorData)
            ->assertForbidden();
        $this->get(route('recepcion.doctors.edit', $firstDoctor))->assertForbidden();
    }

    public function test_consultation_rooms_are_filterable_numbered_and_show_weekly_availability(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);

        $this->actingAs($admin)->get(route('admin.consultorios'))
            ->assertOk()
            ->assertSee('Consultorio 1')
            ->assertSee('Especialidad')
            ->assertSee('Ver agenda');

        $room = \App\Models\ConsultationRoom::query()->where('room_number', 1)->firstOrFail();
        $this->get(route('admin.consultorios.availability', ['room' => $room, 'view' => 'day', 'anchor' => today()->toDateString()]))
            ->assertOk()
            ->assertSee('Día')
            ->assertSee('Sin médicos programados');
        $this->get(route('admin.consultorios.availability', ['room' => $room, 'view' => 'week', 'anchor' => today()->toDateString()]))
            ->assertOk()
            ->assertSee('Semana')
            ->assertSee('horarios libres');
        $this->get(route('admin.consultorios.availability', ['room' => $room, 'view' => 'month', 'anchor' => today()->toDateString()]))
            ->assertOk()
            ->assertSee('Mes')
            ->assertSee('Alta disponibilidad');

        $this->post(route('admin.consultorios.store'), ['specialty' => 'Radiología'])
            ->assertRedirect(route('admin.consultorios'));
        $room = \App\Models\ConsultationRoom::query()->where('room_number', 6)->firstOrFail();
        $this->put(route('admin.consultorios.update', $room), ['specialty' => 'Nutrición'])
            ->assertRedirect(route('admin.consultorios'));
        $this->assertDatabaseHas('consultation_rooms', ['room_number' => 6, 'specialty' => 'Nutrición']);

        $this->patch(route('admin.consultorios.toggle-status', $room))
            ->assertRedirect(route('admin.consultorios'));
        $this->assertDatabaseHas('consultation_rooms', ['room_number' => 6, 'is_active' => false]);

        $this->get(route('admin.consultorios', ['specialty' => 'Nutrición']))
            ->assertOk()
            ->assertSee('Consultorio 6')
            ->assertDontSee('Consultorio 1');

        $this->delete(route('admin.consultorios.destroy', $room))
            ->assertRedirect(route('admin.consultorios'));
        $this->assertSoftDeleted('consultation_rooms', ['id' => $room->id]);
        $this->post(route('admin.consultorios.store'), ['specialty' => 'Radiología'])
            ->assertRedirect(route('admin.consultorios'));
        $this->assertDatabaseHas('consultation_rooms', ['room_number' => 7, 'specialty' => 'Radiología']);
    }

    public function test_consultation_room_availability_changes_detail_by_day_week_and_month(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $doctor = User::factory()->create(['role' => 'medico', 'name' => 'Doctora por consultorio']);
        $profile = $doctor->doctorProfile()->create([
            'professional_title' => 'Dra.',
            'license_number' => 'CED-ROOM-CAL',
            'specialty' => 'Medicina general',
            'phone' => '74747474',
            'appointment_duration' => 30,
            'office' => 'Consultorio 1',
        ]);
        $profile->shifts()->create([
            'day_of_week' => today()->addDay()->dayOfWeekIso,
            'start_time' => '07:00',
            'end_time' => '08:00',
        ]);
        $patient = Patient::create(['name' => 'Paciente del consultorio']);
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => today()->addDay()->setTime(7, 0),
            'reason' => 'Control',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
        $room = \App\Models\ConsultationRoom::query()->where('room_number', 1)->firstOrFail();
        $anchor = today()->addDay()->toDateString();

        $this->actingAs($admin)->get(route('admin.consultorios.availability', ['room' => $room, 'view' => 'day', 'anchor' => $anchor]))
            ->assertOk()
            ->assertSee('Doctora por consultorio')
            ->assertSee('Paciente del consultorio')
            ->assertSee('07:00–07:30 · Ocupado')
            ->assertSee('07:30–08:00')
            ->assertSee('Periodo anterior')
            ->assertSee('Periodo siguiente');

        $this->get(route('admin.consultorios.availability', ['room' => $room, 'view' => 'week', 'anchor' => $anchor]))
            ->assertOk()
            ->assertSee('1 / 1 médicos')
            ->assertSee('1 libres · 1 ocupados')
            ->assertDontSee('Doctora por consultorio');

        $this->get(route('admin.consultorios.availability', ['room' => $room, 'view' => 'month', 'anchor' => $anchor]))
            ->assertOk()
            ->assertSee('1 libres')
            ->assertDontSee('Paciente del consultorio')
            ->assertDontSee('Doctora por consultorio');
    }

    public function test_doctors_can_only_be_assigned_to_active_rooms_with_their_specialty(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $room = \App\Models\ConsultationRoom::query()->where('room_number', 2)->firstOrFail();
        $room->update(['specialty' => 'Radiología']);

        $this->actingAs($admin)->get(route('admin.doctors.create'))
            ->assertOk()
            ->assertSee('data-specialty="Radiología"', false)
            ->assertSee('Consultorio 2 · Radiología');

        $doctorData = [
            'name' => 'Doctor de radiología',
            'email' => 'radiologia@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'professional_title' => 'Dr.',
            'license_number' => 'CED-RAD-1',
            'specialty' => 'Medicina general',
            'phone' => '75757575',
            'appointment_duration' => 30,
            'office' => 'Consultorio 2',
            'is_active' => '1',
            'shifts' => [['days' => ['1'], 'session' => 'matutino']],
        ];
        $this->post(route('admin.doctors.store'), $doctorData)->assertSessionHasErrors('office');

        $doctorData['specialty'] = 'Radiología';
        $compatibleResponse = $this->post(route('admin.doctors.store'), $doctorData);
        $compatibleResponse->assertRedirect(route('admin.doctors'))->assertSessionHasNoErrors();
        $doctor = User::where('email', 'radiologia@example.test')->firstOrFail();

        $this->put(route('admin.consultorios.update', $room), ['specialty' => 'Pediatría'])
            ->assertSessionHasErrors('specialty');

        $this->patch(route('admin.consultorios.toggle-status', $room))->assertRedirect(route('admin.consultorios'));
        $this->get(route('admin.doctors.create'))
            ->assertOk()
            ->assertDontSee('Consultorio 2 · Radiología');
        $this->delete(route('admin.consultorios.destroy', $room))
            ->assertRedirect(route('admin.consultorios'))
            ->assertSessionHas('error');
        $this->assertNotNull($doctor->doctorProfile()->first());
    }

    public function test_doctor_directory_can_toggle_and_delete_accounts_without_deleting_appointments(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $doctor = User::factory()->create(['role' => 'medico', 'name' => 'Doctor Acciones']);
        $patient = Patient::create(['name' => 'Paciente con cita']);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => now()->addDay(),
            'reason' => 'Consulta de seguimiento',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($admin)->get(route('admin.doctors'))
            ->assertOk()
            ->assertSee('Contacto')
            ->assertSee('Acciones')
            ->assertSee('table-fixed')
            ->assertDontSee('min-w-[950px]')
            ->assertSee('aria-label="Desactivar cuenta de Doctor Acciones"', false)
            ->assertSee('aria-label="Eliminar médico Doctor Acciones"', false);

        $this->patch(route('admin.doctors.toggle-status', $doctor))
            ->assertRedirect(route('admin.doctors'));
        $this->assertFalse($doctor->fresh()->isActive());

        $this->patch(route('admin.doctors.toggle-status', $doctor))
            ->assertRedirect(route('admin.doctors'));
        $this->assertTrue($doctor->fresh()->isActive());

        $this->delete(route('admin.doctors.destroy', $doctor))
            ->assertRedirect(route('admin.doctors'));
        $this->assertDatabaseMissing('users', ['id' => $doctor->id]);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'doctor_id' => null,
        ]);
    }

    public function test_agenda_calendar_shows_period_views_availability_colors_and_day_slots(): void
    {
        $admin = User::factory()->create(['role' => 'administrador']);
        $doctor = User::factory()->create(['role' => 'medico', 'name' => 'Doctora Calendario']);
        $profile = $doctor->doctorProfile()->create([
            'professional_title' => 'Dra.',
            'license_number' => 'CED-CAL-1',
            'specialty' => 'Medicina general',
            'phone' => '85858585',
            'appointment_duration' => 30,
            'office' => 'Consultorio 1',
        ]);
        $weekday = today()->dayOfWeekIso;
        $nextWeekday = today()->addDay()->dayOfWeekIso;
        $profile->shifts()->createMany([
            ['day_of_week' => $weekday, 'start_time' => '07:00', 'end_time' => '14:00'],
            ['day_of_week' => $nextWeekday, 'start_time' => '07:00', 'end_time' => '14:00'],
        ]);
        $patient = Patient::create(['name' => 'Paciente Agenda', 'is_active' => true]);
        for ($slot = 0; $slot < 10; $slot++) {
            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'scheduled_at' => today()->setTime(7, 0)->addMinutes($slot * 30),
                'reason' => 'Consulta de agenda',
                'status' => Appointment::STATUS_SCHEDULED,
            ]);
        }

        $anchor = today()->toDateString();
        $this->actingAs($admin)->get(route('admin.agenda', ['view' => 'month', 'anchor' => $anchor]))
            ->assertOk()
            ->assertSee('Disponibilidad médica')
            ->assertSee('border-amber-200 bg-amber-50')
            ->assertSee('border-emerald-200 bg-emerald-50')
            ->assertSee('border-red-200 bg-red-50');
        $this->get(route('admin.agenda', ['view' => 'week', 'anchor' => $anchor]))->assertOk()->assertSee('Semana');
        $this->get(route('admin.agenda', ['view' => 'year', 'anchor' => $anchor]))->assertOk()->assertSee('Año');

        $this->get(route('admin.agenda.day', $anchor))
            ->assertOk()
            ->assertSee('Doctora Calendario')
            ->assertSee('Paciente Agenda')
            ->assertSee('07:00–07:30 · Ocupado')
            ->assertSee('07:30–08:00')
            ->assertSee('Disponible');

        $reception = User::factory()->create(['role' => 'recepcion']);
        $this->actingAs($reception)->get(route('recepcion.agenda.day', $anchor))
            ->assertOk()
            ->assertSee('Horarios disponibles por médico')
            ->assertSee('No disponible')
            ->assertDontSee('Paciente Agenda')
            ->assertSee('Día')
            ->assertSee('Semana')
            ->assertSee('Mes');
    }

    public function test_past_available_slots_are_expired_and_not_counted_as_free(): void
    {
        Carbon::setTestNow(now()->setTime(10, 0));

        try {
            $admin = User::factory()->create(['role' => 'administrador']);
            $doctor = User::factory()->create(['role' => 'medico', 'name' => 'Doctora Horarios']);
            $profile = $doctor->doctorProfile()->create([
                'professional_title' => 'Dra.',
                'license_number' => 'CED-EXP-1',
                'specialty' => 'Medicina general',
                'phone' => '89898989',
                'appointment_duration' => 45,
                'office' => 'Consultorio 1',
            ]);
            $weekday = today()->dayOfWeekIso;
            $profile->shifts()->create([
                'day_of_week' => $weekday,
                'start_time' => '07:00',
                'end_time' => '12:15',
            ]);

            $anchor = today()->toDateString();
            $this->actingAs($admin)->get(route('admin.agenda.day', $anchor))
                ->assertOk()
                ->assertSee('Expirado')
                ->assertSee('border-slate-200 bg-slate-100', false)
                ->assertSee('10:00–10:45')
                ->assertSee('3 espacios libres');

            $room = \App\Models\ConsultationRoom::query()->where('room_number', 1)->firstOrFail();
            $this->get(route('admin.consultorios.availability', ['room' => $room, 'view' => 'day', 'anchor' => $anchor]))
                ->assertOk()
                ->assertSee('Expirado')
                ->assertSee('border-slate-200 bg-slate-100', false)
                ->assertSee('3 horarios libres');
        } finally {
            Carbon::setTestNow();
        }
    }
}
