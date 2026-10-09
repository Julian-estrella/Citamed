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
    public function patients(Request $request)
    {
        $user = Auth::user();
        $query = Patient::query();
        $filters = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'birth_date' => ['nullable', 'date_format:Y-m-d'],
            'gender' => ['nullable', 'in:Femenino,Masculino,Otro,Prefiero no decirlo'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        if ($user->isMedico()) {
            $query->where('is_active', true)
                ->whereHas('appointments', fn ($appointments) => $appointments
                    ->where('doctor_id', $user->id)
                    ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED]));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($patients) use ($search) {
                $patients->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('emergency_contact_name', 'like', "%{$search}%");
            });
        }

        foreach (['name', 'address', 'phone', 'email'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, 'like', '%'.$filters[$column].'%');
            }
        }

        if (! empty($filters['birth_date'])) {
            $query->whereDate('birth_date', $filters['birth_date']);
        }

        foreach (['gender', 'blood_type'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        if (! empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return view('admin.pacientes.index', [
            'user' => $user,
            'patients' => $query->orderBy('name')->get(),
            'title' => $user->isMedico() ? 'Mis pacientes' : 'Pacientes',
            'search' => $request->input('search'),
            'filters' => $filters,
        ]);
    }

    public function createPatient()
    {
        return view('admin.pacientes.create', [
            'user' => Auth::user(),
        ]);
    }

    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'string', 'in:Femenino,Masculino,Otro,Prefiero no decirlo'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'blood_type' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies_conditions' => ['nullable', 'string', 'max:5000'],
        ]);

        Patient::create($validated);

        return redirect()->route($this->patientsRoute())->with('success', 'Paciente registrado correctamente.');
    }

    public function editPatient(Patient $patient)
    {
        return view('admin.pacientes.edit', [
            'user' => Auth::user(),
            'patient' => $patient,
        ]);
    }

    public function updatePatient(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'string', 'in:Femenino,Masculino,Otro,Prefiero no decirlo'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'blood_type' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies_conditions' => ['nullable', 'string', 'max:5000'],
        ]);

        $patient->update($validated);

        return redirect()->route($this->patientsRoute())->with('success', 'Paciente actualizado correctamente.');
    }

    public function showPatient(Patient $patient)
    {
        $user = Auth::user();
        $appointments = $patient->appointments()->with('doctor');

        if ($user->isMedico()) {
            abort_unless(
                $patient->is_active && $patient->appointments()
                    ->where('doctor_id', $user->id)
                    ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
                    ->exists(),
                404
            );

            $appointments->where('doctor_id', $user->id);
        }

        return view('admin.pacientes.show', [
            'user' => $user,
            'patient' => $patient,
            'appointments' => $appointments->latest('scheduled_at')->get(),
        ]);
    }

    public function togglePatient(Patient $patient)
    {
        $patient->is_active = ! $patient->is_active;
        $patient->save();

        return redirect()->route($this->patientsRoute())->with('success', 'Estado del paciente actualizado.');
    }

    public function destroyPatient(Patient $patient)
    {
        if ($patient->appointments()->exists()) {
            return redirect()->route($this->patientsRoute())
                ->with('error', 'No se puede eliminar un paciente con citas registradas. Puedes desactivarlo.');
        }

        $patient->delete();

        return redirect()->route($this->patientsRoute())->with('success', 'Paciente eliminado correctamente.');
    }

    public function doctors(Request $request)
    {
        $filters = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
        ]);
        $query = User::query()
            ->where('role', User::ROLE_MEDICO)
            ->where('is_active', true);

        foreach (['name', 'email'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, 'like', '%'.$filters[$column].'%');
            }
        }

        return view('admin.medicos.index', [
            'user' => Auth::user(),
            'doctors' => $query->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function appointments(Request $request)
    {
        $user = Auth::user();
        $query = Appointment::query()->with(['patient', 'doctor']);
        $filters = $request->validate([
            'scheduled_date' => ['nullable', 'date_format:Y-m-d'],
            'patient' => ['nullable', 'string', 'max:255'],
            'doctor' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:'.Appointment::STATUS_SCHEDULED.','.Appointment::STATUS_CONFIRMED.','.Appointment::STATUS_CANCELLED],
        ]);

        if ($user->isMedico()) {
            $query->where('doctor_id', $user->id);
        }

        if (! empty($filters['scheduled_date'])) {
            $query->whereDate('scheduled_at', $filters['scheduled_date']);
        }

        foreach (['patient' => 'patient', 'doctor' => 'doctor'] as $filter => $relation) {
            if (! empty($filters[$filter])) {
                $query->whereHas($relation, fn ($related) => $related->where('name', 'like', '%'.$filters[$filter].'%'));
            }
        }

        if (! empty($filters['reason'])) {
            $query->where('reason', 'like', '%'.$filters['reason'].'%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return view('admin.citas.index', [
            'user' => $user,
            'title' => $user->isMedico() ? 'Mis citas' : 'Citas',
            'appointments' => $query->latest('scheduled_at')->get(),
            'patients' => Patient::where('is_active', true)->orderBy('name')->get(),
            'doctors' => User::query()->where('role', User::ROLE_MEDICO)
                ->where('is_active', true)->orderBy('name')->get(),
            'filters' => $filters,
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

    public function agenda(Request $request)
    {
        $user = Auth::user();
        $filters = $request->validate([
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'patient' => ['nullable', 'string', 'max:255'],
            'doctor' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:'.Appointment::STATUS_SCHEDULED.','.Appointment::STATUS_CONFIRMED],
        ]);
        $query = Appointment::query()
            ->whereDate('scheduled_at', today())
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->whereHas('patient', fn ($patient) => $patient->where('is_active', true))
            ->whereHas('doctor', fn ($doctor) => $doctor->where('is_active', true))
            ->with(['patient', 'doctor']);

        if ($user->isMedico()) {
            $query->where('doctor_id', $user->id);
        }

        if (! empty($filters['scheduled_time'])) {
            $query->whereTime('scheduled_at', $filters['scheduled_time'].':00');
        }

        foreach (['patient' => 'patient', 'doctor' => 'doctor'] as $filter => $relation) {
            if (! empty($filters[$filter])) {
                $query->whereHas($relation, fn ($related) => $related->where('name', 'like', '%'.$filters[$filter].'%'));
            }
        }

        if (! empty($filters['reason'])) {
            $query->where('reason', 'like', '%'.$filters['reason'].'%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return view('admin.agenda.index', [
            'user' => $user,
            'title' => $user->isMedico() ? 'Mi horario' : 'Agenda',
            'description' => 'Consulta la planificación disponible para tu rol.',
            'appointments' => $query->orderBy('scheduled_at')->get(),
            'filters' => $filters,
        ]);
    }

    public function availability()
    {
        return view('admin.agenda.index', [
            'user' => Auth::user(),
            'title' => 'Mi disponibilidad',
            'description' => 'Administra los espacios disponibles de atención.',
            'appointments' => collect(),
            'filters' => [],
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