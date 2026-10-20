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