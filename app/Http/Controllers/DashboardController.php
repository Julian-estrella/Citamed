<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin()
    {
        $activeTodayAppointments = $this->activeAppointments()
            ->whereDate('scheduled_at', today())
            ->with(['patient', 'doctor'])
            ->orderBy('scheduled_at')
            ->get();
        $todayAppointments = $this->appointmentsForToday()
            ->with(['patient', 'doctor'])
            ->orderBy('scheduled_at')
            ->get();

        return view('admin.index', [
            'patientCount' => Patient::where('is_active', true)->count(),
            'todayAppointmentCount' => $activeTodayAppointments->count(),
            'doctorCount' => $this->activeDoctorCount(),
            'confirmedAppointmentCount' => $activeTodayAppointments->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'scheduledAppointmentCount' => $activeTodayAppointments->where('status', Appointment::STATUS_SCHEDULED)->count(),
            'cancelledAppointmentCount' => Appointment::query()->whereDate('scheduled_at', today())
                ->where('status', Appointment::STATUS_CANCELLED)->count(),
            'todayAppointments' => $todayAppointments,
        ]);
    }

    public function doctor()
    {
        $user = Auth::user();
        $activeTodayAppointments = $this->activeAppointments()
            ->where('doctor_id', $user->id)
            ->whereDate('scheduled_at', today())
            ->with('patient')
            ->orderBy('scheduled_at')
            ->get();
        $todayAppointments = $this->appointmentsForToday()
            ->where('doctor_id', $user->id)
            ->with('patient')
            ->orderBy('scheduled_at')
            ->get();

        $patientCount = $this->activeAppointments()
            ->where('doctor_id', $user->id)
            ->whereHas('patient', fn ($query) => $query->where('is_active', true))
            ->distinct('patient_id')
            ->count('patient_id');

        return view('layout.medico.index', [
            'todayAppointmentCount' => $activeTodayAppointments->count(),
            'patientCount' => $patientCount,
            'todayAppointments' => $todayAppointments,
        ]);
    }

    public function reception()
    {
        $activeTodayAppointments = $this->activeAppointments()
            ->whereDate('scheduled_at', today())
            ->count();
        $todayAppointments = $this->appointmentsForToday()
            ->with(['patient', 'doctor'])
            ->orderBy('scheduled_at')
            ->get();

        return view('layout.recepcion.index', [
            'patientCount' => Patient::where('is_active', true)->count(),
            'todayAppointmentCount' => $activeTodayAppointments,
            'doctorCount' => $this->activeDoctorCount(),
            'todayAppointments' => $todayAppointments,
        ]);
    }

    private function appointmentsForToday()
    {
        return Appointment::query()
            ->whereDate('scheduled_at', today())
            ->whereHas('patient', fn ($query) => $query->where('is_active', true))
            ->whereHas('doctor', fn ($query) => $query->where('is_active', true))
            ->whereIn('status', [
                Appointment::STATUS_SCHEDULED,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CANCELLED,
            ]);
    }

    private function activeAppointments()
    {
        return Appointment::query()
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->whereHas('patient', fn ($query) => $query->where('is_active', true))
            ->whereHas('doctor', fn ($query) => $query->where('is_active', true));
    }

    private function activeDoctorCount(): int
    {
        return User::query()
            ->whereHasRole(User::ROLE_MEDICO)
            ->where('is_active', true)
            ->count();
    }
}