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
        $todayAppointments = $this->activeAppointments()
            ->whereDate('scheduled_at', today())
            ->with(['patient', 'doctor'])
            ->orderBy('scheduled_at')
            ->get();

        return view('admin.index', [
            'patientCount' => Patient::where('is_active', true)->count(),
            'todayAppointmentCount' => $todayAppointments->count(),
            'doctorCount' => $this->activeDoctorCount(),
            'confirmedAppointmentCount' => $todayAppointments->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'scheduledAppointmentCount' => $todayAppointments->where('status', Appointment::STATUS_SCHEDULED)->count(),
            'cancelledAppointmentCount' => Appointment::query()->whereDate('scheduled_at', today())
                ->where('status', Appointment::STATUS_CANCELLED)->count(),
            'todayAppointments' => $todayAppointments,
        ]);
    }

    public function doctor()
    {
        $user = Auth::user();
        $todayAppointments = $this->activeAppointments()
            ->where('doctor_id', $user->id)
            ->whereDate('scheduled_at', today())
            ->with('patient')
            ->orderBy('scheduled_at')
            ->get();

        $patientCount = $this->activeAppointments()
            ->where('doctor_id', $user->id)
            ->whereHas('patient', fn ($query) => $query->where('is_active', true))
            ->distinct('patient_id')
            ->count('patient_id');

        return view('layout.medico.index', [
            'todayAppointmentCount' => $todayAppointments->count(),
            'patientCount' => $patientCount,
            'todayAppointments' => $todayAppointments,
        ]);
    }

    public function reception()
    {
        $todayAppointments = $this->activeAppointments()
            ->whereDate('scheduled_at', today())
            ->with(['patient', 'doctor'])
            ->orderBy('scheduled_at')
            ->get();

        return view('layout.recepcion.index', [
            'patientCount' => Patient::where('is_active', true)->count(),
            'todayAppointmentCount' => $todayAppointments->count(),
            'doctorCount' => $this->activeDoctorCount(),
            'todayAppointments' => $todayAppointments,
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
            ->where('role', User::ROLE_MEDICO)
            ->where('is_active', true)
            ->count();
    }
}