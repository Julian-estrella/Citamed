<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_can_schedule_an_appointment_during_a_doctors_shift(): void
    {
        $reception = User::factory()->create(['role' => User::ROLE_RECEPCION]);
        $patient = Patient::create(['name' => 'Paciente Disponible', 'is_active' => true]);
        $doctor = $this->createDoctorWithShift();
        $scheduledAt = $this->scheduledAt('10:00');

        $this->actingAs($reception)->post(route('recepcion.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $scheduledAt->format('Y-m-d\\TH:i'),
            'reason' => 'Consulta inicial',
        ])->assertRedirect(route('recepcion.appointments'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
    }

    public function test_scheduling_rejects_times_outside_the_shift_and_existing_conflicts(): void
    {
        $reception = User::factory()->create(['role' => User::ROLE_RECEPCION]);
        $patient = Patient::create(['name' => 'Paciente de Prueba', 'is_active' => true]);
        $doctor = $this->createDoctorWithShift();
        $appointmentAt = $this->scheduledAt('10:00');

        $this->actingAs($reception)->post(route('recepcion.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $this->scheduledAt('13:00')->format('Y-m-d\\TH:i'),
        ])->assertSessionHasErrors('scheduled_at');
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $appointmentAt,
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->post(route('recepcion.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $appointmentAt->format('Y-m-d\\TH:i'),
        ])->assertSessionHasErrors('scheduled_at');
    }

    public function test_available_slots_follow_shift_duration_working_days_and_existing_appointments(): void
    {
        $reception = User::factory()->create(['role' => User::ROLE_RECEPCION]);
        $patient = Patient::create(['name' => 'Paciente con Turno', 'is_active' => true]);
        $doctor = $this->createDoctorWithShift();
        $scheduledAt = $this->scheduledAt('10:00');
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $scheduledAt,
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($reception)->getJson(route('recepcion.appointments.slots', [
            'doctor_id' => $doctor->id,
            'date' => $scheduledAt->format('Y-m-d'),
        ]))->assertOk();

        $this->assertSame(
            ['09:00', '09:30', '10:30', '11:00', '11:30', '15:00', '15:30'],
            collect($response->json('slots'))->pluck('time')->all()
        );

        $this->getJson(route('recepcion.appointments.slots', [
            'doctor_id' => $doctor->id,
            'date' => $scheduledAt->addDay()->format('Y-m-d'),
        ]))->assertOk()->assertExactJson(['slots' => []]);
    }

    public function test_inactive_doctors_cannot_be_selected_for_scheduling(): void
    {
        $reception = User::factory()->create(['role' => User::ROLE_RECEPCION]);
        $patient = Patient::create(['name' => 'Paciente de Prueba', 'is_active' => true]);
        $doctor = User::factory()->create(['role' => User::ROLE_MEDICO, 'is_active' => false]);

        $this->actingAs($reception)->get(route('recepcion.appointments.create'))
            ->assertOk()
            ->assertDontSee($doctor->name);

        $this->post(route('recepcion.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $this->scheduledAt('10:00')->format('Y-m-d\\TH:i'),
        ])->assertSessionHasErrors('doctor_id');
    }

    public function test_appointment_form_shows_doctor_specialty_and_working_days(): void
    {
        $reception = User::factory()->create(['role' => User::ROLE_RECEPCION]);
        $doctor = $this->createDoctorWithShift();

        $this->actingAs($reception)->get(route('recepcion.appointments.create'))
            ->assertOk()
            ->assertSee($doctor->name.' — Medicina general')
            ->assertSee('data-working-days="['.$this->scheduledAt('10:00')->isoWeekday().']"', false)
            ->assertSee('Días que trabaja')
            ->assertSee('Días que no trabaja');
    }

    public function test_appointments_can_be_rescheduled_cancelled_and_confirmed_with_reasons(): void
    {
        $reception = User::factory()->create(['role' => User::ROLE_RECEPCION]);
        $patient = Patient::create(['name' => 'Paciente de Cambios', 'is_active' => true]);
        $doctor = $this->createDoctorWithShift();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $this->scheduledAt('10:00'),
            'reason' => 'Control',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($reception)->put(route('recepcion.appointments.update', $appointment), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $this->scheduledAt('10:30')->format('Y-m-d\\TH:i'),
            'reason' => 'Control',
            'reschedule_reason' => 'Conflicto con otra actividad.',
        ])->assertRedirect(route('recepcion.appointments'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'scheduled_at' => $this->scheduledAt('10:30')->format('Y-m-d H:i:s'),
            'reschedule_reason' => 'Conflicto con otra actividad.',
        ]);

        $this->patch(route('recepcion.appointments.confirm', $appointment))->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => Appointment::STATUS_CONFIRMED]);

        $this->patch(route('recepcion.appointments.cancel', $appointment), [
            'cancellation_reason' => 'El paciente no puede asistir.',
        ])->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
            'cancellation_reason' => 'El paciente no puede asistir.',
        ]);
    }

    public function test_doctors_can_manage_only_their_own_appointments(): void
    {
        $doctor = $this->createDoctorWithShift();
        $otherDoctor = $this->createDoctorWithShift();
        $patient = Patient::create(['name' => 'Paciente del médico', 'is_active' => true]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $this->scheduledAt('10:00'),
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
        $otherAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'scheduled_at' => $this->scheduledAt('10:00'),
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($doctor)->get(route('medico.appointments', [
            'anchor' => $this->scheduledAt('10:00')->format('Y-m-d'),
        ]))
            ->assertOk()
            ->assertSee('Nueva cita')
            ->assertSee($patient->name);
        $this->get(route('medico.appointments.create'))
            ->assertOk()
            ->assertSee($doctor->name.' — Medicina general')
            ->assertSee('value="'.$doctor->id.'"', false);

        $this->post(route('medico.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'scheduled_at' => $this->scheduledAt('09:00')->format('Y-m-d\\TH:i'),
            'reason' => 'Consulta de seguimiento',
        ])->assertRedirect(route('medico.appointments'));

        $createdAppointment = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('scheduled_at', $this->scheduledAt('09:00'))
            ->firstOrFail();

        $this->put(route('medico.appointments.update', $createdAppointment), [
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'scheduled_at' => $this->scheduledAt('10:30')->format('Y-m-d\\TH:i'),
            'reason' => 'Consulta de seguimiento',
            'reschedule_reason' => 'Cambio solicitado por el paciente.',
        ])->assertRedirect(route('medico.appointments'));
        $this->assertDatabaseHas('appointments', [
            'id' => $createdAppointment->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $this->scheduledAt('10:30')->format('Y-m-d H:i:s'),
        ]);

        $this->patch(route('medico.appointments.cancel', $createdAppointment), [
            'cancellation_reason' => 'No podrá asistir.',
        ])->assertRedirect(route('medico.appointments'));
        $this->assertDatabaseHas('appointments', [
            'id' => $createdAppointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $this->get(route('medico.appointments.edit', $otherAppointment))->assertNotFound();
        $this->patch(route('medico.appointments.cancel', $otherAppointment), [
            'cancellation_reason' => 'Intento no autorizado.',
        ])->assertNotFound();
    }

    public function test_doctor_availability_uses_personal_free_slot_labels(): void
    {
        $doctor = $this->createDoctorWithShift();

        $this->actingAs($doctor)->get(route('medico.availability'))
            ->assertOk()
            ->assertSee('8 citas disponibles')
            ->assertDontSee('1/1 médicos')
            ->assertSee(route('medico.availability.day', $this->scheduledAt('10:00')->format('Y-m-d')));
    }

    public function test_appointment_list_can_show_a_week_for_a_selected_doctor_and_roles_are_enforced(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
        $doctor = $this->createDoctorWithShift();
        $patient = Patient::create(['name' => 'Paciente en Agenda', 'is_active' => true]);
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => $this->scheduledAt('10:00'),
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($admin)->get(route('admin.appointments', [
            'view' => 'week',
            'anchor' => $this->scheduledAt('10:00')->format('Y-m-d'),
            'doctor_id' => $doctor->id,
        ]))->assertOk()->assertSee('Paciente en Agenda')->assertSee($doctor->name)->assertSee('Agenda de citas');

        $medicalUser = User::factory()->create(['role' => User::ROLE_MEDICO]);
        $this->actingAs($medicalUser)->get(route('admin.appointments.create'))->assertForbidden();
    }

    public function test_cancel_action_opens_a_centered_reason_dialog_with_dimmed_backdrop(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
        $patient = Patient::create(['name' => 'Paciente para cancelar', 'is_active' => true]);
        $doctor = $this->createDoctorWithShift();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => CarbonImmutable::now()->addHour(),
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->actingAs($admin)->get(route('admin.appointments'))
            ->assertOk()
            ->assertSee('id="cancel-appointment-modal"', false)
            ->assertSee('backdrop:bg-slate-950/50', false)
            ->assertSee('data-cancel-url="'.route('admin.appointments.cancel', $appointment).'"', false);
    }

    public function test_cancelled_appointments_are_highlighted_in_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMINISTRADOR]);
        $patient = Patient::create(['name' => 'Paciente con cita cancelada', 'is_active' => true]);
        $doctor = $this->createDoctorWithShift();
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'scheduled_at' => CarbonImmutable::today()->setTime(10, 0),
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $this->actingAs($admin)->get(route('admin.panel'))
            ->assertOk()
            ->assertSee('Paciente con cita cancelada')
            ->assertSee('border-red-200 bg-red-50', false)
            ->assertSee('Cancelada');
    }

    private function createDoctorWithShift(): User
    {
        $doctor = User::factory()->create(['role' => User::ROLE_MEDICO, 'is_active' => true]);
        $profile = DoctorProfile::create([
            'user_id' => $doctor->id,
            'professional_title' => 'Dr.',
            'license_number' => 'LIC-'.$doctor->id,
            'specialty' => 'Medicina general',
            'phone' => str_pad((string) $doctor->id, 8, '0', STR_PAD_LEFT),
            'appointment_duration' => 30,
            'office' => 'Consultorio 1',
        ]);

        $profile->shifts()->create([
            'day_of_week' => $this->scheduledAt('10:00')->isoWeekday(),
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);
        $profile->shifts()->create([
            'day_of_week' => $this->scheduledAt('10:00')->isoWeekday(),
            'start_time' => '15:00',
            'end_time' => '16:00',
        ]);

        return $doctor;
    }

    private function scheduledAt(string $time): CarbonImmutable
    {
        return CarbonImmutable::now()->addDay()->startOfDay()->setTimeFromTimeString($time);
    }
}