<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PanelSectionController extends Controller
{
    public function patients()
    {
        $user = Auth::user();
        $query = Patient::query();

        if ($user->isMedico()) {
            $query->where('is_active', true)
                ->whereHas('appointments', fn ($appointments) => $appointments
                    ->where('doctor_id', $user->id)
                    ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED]));
        }

        return view('admin.pacientes.index', [
            'user' => $user,
            'patients' => $query->orderBy('name')->get(),
            'title' => $user->isMedico() ? 'Mis pacientes' : 'Pacientes',
        ]);
    }

    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        Patient::create($validated);

        return redirect()->route($this->patientsRoute())->with('success', 'Paciente registrado correctamente.');
    }

    public function togglePatient(Patient $patient)
    {
        $patient->is_active = ! $patient->is_active;
        $patient->save();

        return redirect()->route($this->patientsRoute())->with('success', 'Estado del paciente actualizado.');
    }

    public function doctors()
    {
        return view('admin.medicos.index', [
            'user' => Auth::user(),
            'doctors' => User::query()
                ->where('role', User::ROLE_MEDICO)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function appointments()
    {
        $user = Auth::user();
        $query = Appointment::query()->with(['patient', 'doctor']);

        if ($user->isMedico()) {
            $query->where('doctor_id', $user->id);
        }

        return view('admin.citas.index', [
            'user' => $user,
            'title' => $user->isMedico() ? 'Mis citas' : 'Citas',
            'appointments' => $query->latest('scheduled_at')->get(),
            'patients' => Patient::where('is_active', true)->orderBy('name')->get(),
            'doctors' => User::query()->where('role', User::ROLE_MEDICO)
                ->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeAppointment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')->where('is_active', true)],
            'doctor_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query
                ->where('role', User::ROLE_MEDICO)->where('is_active', true))],
            'scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        Appointment::create($validated + ['status' => Appointment::STATUS_SCHEDULED]);

        return redirect()->route($this->appointmentsRoute())->with('success', 'Cita programada correctamente.');
    }

    public function cancelAppointment(Appointment $appointment)
    {
        $appointment->status = Appointment::STATUS_CANCELLED;
        $appointment->save();

        return redirect()->route($this->appointmentsRoute())->with('success', 'Cita cancelada correctamente.');
    }

    public function agenda()
    {
        $user = Auth::user();
        $query = Appointment::query()
            ->whereDate('scheduled_at', today())
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->whereHas('patient', fn ($patient) => $patient->where('is_active', true))
            ->whereHas('doctor', fn ($doctor) => $doctor->where('is_active', true))
            ->with(['patient', 'doctor']);

        if ($user->isMedico()) {
            $query->where('doctor_id', $user->id);
        }

        return view('admin.agenda.index', [
            'user' => $user,
            'title' => $user->isMedico() ? 'Mi horario' : 'Agenda',
            'description' => 'Consulta la planificación disponible para tu rol.',
            'appointments' => $query->orderBy('scheduled_at')->get(),
        ]);
    }

    public function availability()
    {
        return view('admin.agenda.index', [
            'user' => Auth::user(),
            'title' => 'Mi disponibilidad',
            'description' => 'Administra los espacios disponibles de atención.',
            'appointments' => collect(),
        ]);
    }

    private function patientsRoute(): string
    {
        return Auth::user()->isAdmin() ? 'admin.patients' : 'recepcion.patients';
    }

    private function appointmentsRoute(): string
    {
        return match (Auth::user()->role) {
            User::ROLE_ADMINISTRADOR => 'admin.appointments',
            User::ROLE_MEDICO => 'medico.appointments',
            default => 'recepcion.appointments',
        };
    }
}