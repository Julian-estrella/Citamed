<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ConsultationRoom;
use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\User;
use App\Rules\UniqueClinicPhone;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PanelSectionController extends Controller
{
    public function patients(Request $request)
    {
        $user = Auth::user();
        $query = Patient::query();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort' => ['sometimes', 'in:name'],
            'direction' => ['sometimes', 'in:asc,desc'],
        ]);

        if ($user->isMedico() && ! $user->isAdmin()) {
            $query->where('is_active', true)
                ->whereHas('appointments', fn ($appointments) => $appointments
                    ->where('doctor_id', $user->id)
                    ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED]));
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['blood_type'])) {
            $query->where('blood_type', $filters['blood_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return view('admin.pacientes.index', [
            'user' => $user,
            'patients' => $query->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')->get(),
            'title' => $user->isMedico() ? 'Mis pacientes' : 'Pacientes',
            'filters' => $filters,
            'search' => $filters['search'] ?? '',
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
        $validated = $request->validate($this->patientValidationRules());

        Patient::create($this->normalizePatientData($validated));

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
        $validated = $request->validate($this->patientValidationRules($patient));

        $patient->update($this->normalizePatientData($validated));

        return redirect()->route($this->patientsRoute())->with('success', 'Paciente actualizado correctamente.');
    }

    public function showPatient(Patient $patient)
    {
        $user = Auth::user();
        $appointments = $patient->appointments()->with('doctor');

        if ($user->isMedico() && ! $user->isAdmin()) {
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
        $user = Auth::user();
        if ($user->isRecepcion() && ! $user->hasPermission('gestionar_medicos')) {
            return redirect()->route('recepcion.agenda');
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'in:name,email'],
            'direction' => ['sometimes', 'in:asc,desc'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'office' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $query = User::query()
            ->with('doctorProfile.shifts')
            ->whereHasRole(User::ROLE_MEDICO);

        if (! $user->hasPermission('gestionar_medicos')) {
            $query->where('is_active', true);
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(fn ($matches) => $matches
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%'));
        }

        foreach (['name', 'email'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, 'like', '%'.$filters[$column].'%');
            }
        }

        if (! empty($filters['specialty'])) {
            if ($filters['specialty'] === '__pending') {
                $query->whereDoesntHave('doctorProfile');
            } else {
                $query->whereHas('doctorProfile', fn ($profile) => $profile->where('specialty', $filters['specialty']));
            }
        }

        if (! empty($filters['office'])) {
            if ($filters['office'] === '__pending') {
                $query->whereDoesntHave('doctorProfile');
            } else {
                $query->whereHas('doctorProfile', fn ($profile) => $profile->where('office', $filters['office']));
            }
        }

        if (! empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return view('admin.medicos.index', [
            'user' => $user,
            'doctors' => $query->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')->get(),
            'filters' => $filters,
            'search' => $filters['search'] ?? '',
            'daysOfWeek' => config('clinic.days'),
            'workShifts' => config('clinic.shifts'),
            'createRoute' => $this->doctorCreateRoute(),
            'offices' => $this->consultationRoomNames(activeOnly: false),
            'specialties' => config('clinic.specialties'),
        ]);
    }

    public function toggleDoctorStatus(User $doctor)
    {
        abort_unless($doctor->isMedico(), 404);

        if ($doctor->isSuperAdmin() || (auth()->id() === $doctor->id && $doctor->isActive())) {
            return redirect()->route($this->doctorsRoute())->with('error', 'No puedes desactivar esta cuenta médica.');
        }

        $doctor->is_active = ! $doctor->is_active;
        $doctor->save();

        return redirect()->route($this->doctorsRoute())->with('success', 'Estado de la cuenta médica actualizado.');
    }

    public function destroyDoctor(User $doctor)
    {
        abort_unless($doctor->isMedico(), 404);

        if ($doctor->isSuperAdmin() || auth()->id() === $doctor->id) {
            return redirect()->route($this->doctorsRoute())->with('error', 'No puedes eliminar esta cuenta médica.');
        }

        $doctor->delete();

        return redirect()->route($this->doctorsRoute())->with('success', 'Cuenta médica eliminada. Las citas existentes se conservaron sin médico asignado.');
    }

    public function createDoctor()
    {
        return view('admin.medicos.create', [
            'doctor' => null,
            'profile' => null,
            'offices' => $this->consultationRoomNames(),
            'consultationRooms' => ConsultationRoom::query()->where('is_active', true)->orderBy('room_number')->get(),
            'daysOfWeek' => config('clinic.days'),
            'workShifts' => config('clinic.shifts'),
            'specialties' => config('clinic.specialties'),
            'doctorsRoute' => $this->doctorsRoute(),
            'storeRoute' => $this->doctorStoreRoute(),
        ]);
    }

    public function storeDoctor(Request $request)
    {
        $validator = Validator::make($request->all(), array_merge($this->doctorProfileRules(specialty: $request->input('specialty')), [
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:12', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/'],
        ]));
        $this->addDoctorShiftValidation($validator, $request);
        $validated = $validator->validate();

        $doctor = DB::transaction(function () use ($validated) {
            $doctor = User::create([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'password' => $validated['password'],
                'role' => User::ROLE_MEDICO,
                'is_active' => (bool) $validated['is_active'],
            ]);

            $profile = $doctor->doctorProfile()->create(array_merge(
                $this->doctorProfileData($validated),
                ['license_number' => $this->pendingDoctorLicense()]
            ));
            $profile->license_number = $this->doctorLicenseNumber($validated['specialty'], $profile->id);
            $profile->save();
            $profile->shifts()->createMany($this->doctorScheduleData($validated['shifts']));

            return $doctor;
        });

        return redirect()->route($this->doctorsRoute())
            ->with('success', 'Médico registrado correctamente.');
    }

    public function editDoctor(User $doctor)
    {
        abort_unless($doctor->isMedico(), 404);

        $doctor->load('doctorProfile.shifts');

        return view('admin.medicos.create', [
            'doctor' => $doctor,
            'profile' => $doctor->doctorProfile,
            'offices' => $this->consultationRoomNames($doctor->doctorProfile?->specialty, includeOffice: $doctor->doctorProfile?->office),
            'consultationRooms' => $this->roomsForDoctor($doctor),
            'daysOfWeek' => config('clinic.days'),
            'workShifts' => config('clinic.shifts'),
            'specialties' => config('clinic.specialties'),
            'doctorsRoute' => $this->doctorsRoute(),
            'updateRoute' => $this->doctorUpdateRoute(),
        ]);
    }

    public function updateDoctor(Request $request, User $doctor)
    {
        abort_unless($doctor->isMedico(), 404);

        $validator = Validator::make($request->all(), $this->doctorProfileRules($doctor, $request->input('specialty')));
        $this->addDoctorShiftValidation($validator, $request, $doctor);
        $validated = $validator->validate();

        DB::transaction(function () use ($doctor, $validated) {
            $profile = DoctorProfile::firstOrNew(['user_id' => $doctor->id]);
            $profile->fill($this->doctorProfileData($validated));
            if (! $profile->exists) {
                $profile->license_number = $this->pendingDoctorLicense();
                $profile->save();
            }
            $profile->license_number = $this->doctorLicenseNumber($validated['specialty'], $profile->id);
            $profile->save();

            $profile->shifts()->delete();
            $profile->shifts()->createMany($this->doctorScheduleData($validated['shifts']));

            $doctor->is_active = (bool) $validated['is_active'];
            $doctor->save();
        });

        return redirect()->route($this->doctorsRoute())->with('success', 'Datos del médico actualizados correctamente.');
    }

    public function consultationRooms(Request $request)
    {
        $filters = $request->validate([
            'specialty' => ['nullable', Rule::in(config('clinic.specialties'))],
        ]);
        $query = ConsultationRoom::query()->orderBy('room_number');

        if (! empty($filters['specialty'])) {
            $query->where('specialty', $filters['specialty']);
        }

        $rooms = $query->get()->map(function (ConsultationRoom $room) {
            $room->setRelation('doctorProfiles', DoctorProfile::query()
                ->with(['user', 'shifts'])
                ->where('office', $room->label)
                ->get());

            return $room;
        });
        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';

        return view('admin.consultorios.index', [
            'rooms' => $rooms,
            'filters' => $filters,
            'specialties' => config('clinic.specialties'),
            'listRoute' => $routePrefix.'.consultorios',
            'createRoute' => $routePrefix.'.consultorios.create',
            'editRoute' => $routePrefix.'.consultorios.edit',
            'updateRoute' => $routePrefix.'.consultorios.update',
            'toggleRoute' => $routePrefix.'.consultorios.toggle-status',
            'deleteRoute' => $routePrefix.'.consultorios.destroy',
        ]);
    }

    public function createConsultationRoom()
    {
        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';

        return view('admin.consultorios.create', [
            'specialties' => config('clinic.specialties'),
            'listRoute' => $routePrefix.'.consultorios',
            'storeRoute' => $routePrefix.'.consultorios.store',
        ]);
    }

    public function storeConsultationRoom(Request $request)
    {
        $validated = $request->validate([
            'specialty' => ['required', Rule::in(config('clinic.specialties'))],
        ]);

        $room = DB::transaction(function () use ($validated) {
            $nextNumber = ((int) ConsultationRoom::withTrashed()->lockForUpdate()->max('room_number')) + 1;

            return ConsultationRoom::create([
                'room_number' => $nextNumber,
                'specialty' => $validated['specialty'],
            ]);
        });

        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';

        return redirect()->route($routePrefix.'.consultorios')
            ->with('success', $room->label.' creado correctamente.');
    }

    public function editConsultationRoom(ConsultationRoom $room)
    {
        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';

        return view('admin.consultorios.edit', [
            'room' => $room,
            'specialties' => config('clinic.specialties'),
            'listRoute' => $routePrefix.'.consultorios',
            'updateRoute' => $routePrefix.'.consultorios.update',
        ]);
    }

    public function updateConsultationRoom(Request $request, ConsultationRoom $room)
    {
        $validated = $request->validate([
            'specialty' => ['required', Rule::in(config('clinic.specialties'))],
        ]);

        if (DoctorProfile::query()->where('office', $room->label)->exists() && $validated['specialty'] !== $room->specialty) {
            return back()->withErrors([
                'specialty' => 'Cambia primero a los médicos asignados a otro consultorio antes de editar la especialidad.',
            ])->withInput();
        }

        $room->update(['specialty' => $validated['specialty']]);
        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';

        return redirect()->route($routePrefix.'.consultorios')
            ->with('success', $room->label.' actualizado correctamente.');
    }

    public function toggleConsultationRoom(ConsultationRoom $room)
    {
        $room->is_active = ! $room->is_active;
        $room->save();
        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';
        $status = $room->is_active ? 'activado' : 'desactivado';

        return redirect()->route($routePrefix.'.consultorios')
            ->with('success', $room->label.' '.$status.'.');
    }

    public function destroyConsultationRoom(ConsultationRoom $room)
    {
        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';
        if (DoctorProfile::query()->where('office', $room->label)->exists()) {
            return redirect()->route($routePrefix.'.consultorios')
                ->with('error', 'No se puede borrar un consultorio con médicos asignados. Desactívalo o reasigna primero a los médicos.');
        }

        $room->delete();

        return redirect()->route($routePrefix.'.consultorios')
            ->with('success', $room->label.' eliminado correctamente.');
    }

    public function consultationRoomAvailability(Request $request, ConsultationRoom $room)
    {
        $filters = $request->validate([
            'view' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'anchor' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $view = $filters['view'] ?? 'month';
        $anchor = isset($filters['anchor']) ? CarbonImmutable::parse($filters['anchor']) : CarbonImmutable::today();
        $rangeStart = match ($view) {
            'day' => $anchor->startOfDay(),
            'week' => $anchor->startOfWeek(),
            default => $anchor->startOfMonth(),
        };
        $rangeEnd = match ($view) {
            'day' => $anchor->endOfDay(),
            'week' => $anchor->endOfWeek(),
            default => $anchor->endOfMonth(),
        };
        $availability = $this->doctorAvailabilityForRange($rangeStart, $rangeEnd, Auth::user(), $room->label);
        $calendarDays = [];

        if ($view === 'week' || $view === 'month') {
            $gridStart = $view === 'week' ? $rangeStart : $rangeStart->startOfWeek();
            $gridEnd = $view === 'week' ? $rangeEnd : $rangeEnd->endOfWeek();
            for ($date = $gridStart; $date->lte($gridEnd); $date = $date->addDay()) {
                $calendarDays[] = [
                    'date' => $date,
                    'stats' => $availability['days'][$date->format('Y-m-d')] ?? null,
                    'inPeriod' => $view === 'week' || $date->month === $anchor->month,
                ];
            }
        }

        $previous = match ($view) {
            'day' => $anchor->subDay(),
            'week' => $anchor->subWeek(),
            default => $anchor->subMonth(),
        };
        $next = match ($view) {
            'day' => $anchor->addDay(),
            'week' => $anchor->addWeek(),
            default => $anchor->addMonth(),
        };
        $routePrefix = Auth::user()->isAdmin() ? 'admin' : 'recepcion';

        return view('admin.consultorios.availability', [
            'room' => $room,
            'view' => $view,
            'anchor' => $anchor,
            'periodTitle' => match ($view) {
                'day' => $anchor->locale('es')->translatedFormat('l, j \\d\\e F Y'),
                'week' => $rangeStart->format('d/m/Y').' – '.$rangeEnd->format('d/m/Y'),
                default => $anchor->locale('es')->translatedFormat('F Y'),
            },
            'previous' => $previous->format('Y-m-d'),
            'next' => $next->format('Y-m-d'),
            'availability' => $availability['days'],
            'calendarDays' => $calendarDays,
            'weekDayLabels' => array_values(config('clinic.days')),
            'daysOfWeek' => config('clinic.days'),
            'workShifts' => config('clinic.shifts'),
            'listRoute' => $routePrefix.'.consultorios',
            'availabilityRoute' => $routePrefix.'.consultorios.availability',
        ]);
    }

    public function appointments(Request $request)
    {
        $user = Auth::user();
        $isDoctor = $user->isMedico() && ! $user->isAdmin();
        $routePrefix = $user->isAdmin() ? 'admin' : ($isDoctor ? 'medico' : 'recepcion');
        $query = Appointment::query()->with(['patient', 'doctor']);
        $filters = $request->validate([
            'anchor' => ['nullable', 'date_format:Y-m-d'],
            'view' => ['nullable', Rule::in(['day', 'week'])],
            'doctor_id' => ['nullable', 'integer', $this->doctorExistsRule(activeOnly: false)],
            'patient' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:'.Appointment::STATUS_SCHEDULED.','.Appointment::STATUS_CONFIRMED.','.Appointment::STATUS_CANCELLED],
        ]);
        $view = $filters['view'] ?? 'day';
        $anchor = CarbonImmutable::parse($filters['anchor'] ?? today()->toDateString());
        $rangeStart = $view === 'week' ? $anchor->startOfWeek() : $anchor->startOfDay();
        $rangeEnd = $view === 'week' ? $anchor->endOfWeek() : $anchor->endOfDay();

        if ($isDoctor) {
            $query->where('doctor_id', $user->id);
        }

        if (! empty($filters['doctor_id']) && ! $isDoctor) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        $query->whereBetween('scheduled_at', [$rangeStart, $rangeEnd]);

        if (! empty($filters['patient'])) {
            $query->whereHas('patient', fn ($related) => $related->where('name', 'like', '%'.$filters['patient'].'%'));
        }

        if (! empty($filters['reason'])) {
            $query->where('reason', 'like', '%'.$filters['reason'].'%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return view('admin.citas.index', [
            'user' => $user,
            'title' => $isDoctor ? 'Mis citas' : 'Citas',
            'appointments' => $query->orderBy('scheduled_at')->get(),
            'patients' => Patient::where('is_active', true)->orderBy('name')->get(),
            'doctors' => User::query()->whereHasRole(User::ROLE_MEDICO)
                ->where('is_active', true)->orderBy('name')->get(),
            'filterDoctors' => User::query()->whereHasRole(User::ROLE_MEDICO)->orderBy('name')->get(),
            'filters' => $filters,
            'view' => $view,
            'anchor' => $anchor,
        ]);
    }

    public function createAppointment()
    {
        return view('admin.citas.create', $this->appointmentFormData());
    }

    public function appointmentSlots(Request $request)
    {
        if (Auth::user()->isMedico() && ! Auth::user()->isAdmin()) {
            $request->merge(['doctor_id' => Auth::id()]);
        }

        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', $this->doctorExistsRule()],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_id' => ['nullable', Rule::exists('appointments', 'id')->where(fn ($query) => $query
                ->where('doctor_id', $request->input('doctor_id'))
                ->where('status', '!=', Appointment::STATUS_CANCELLED))],
        ]);

        $except = isset($validated['appointment_id'])
            ? Appointment::find($validated['appointment_id'])
            : null;
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $validated['date']);

        return response()->json([
            'slots' => $this->appointmentSlotsForDate((int) $validated['doctor_id'], $date, $except),
        ]);
    }

    public function storeAppointment(Request $request)
    {
        if (Auth::user()->isMedico() && ! Auth::user()->isAdmin()) {
            $request->merge(['doctor_id' => Auth::id()]);
        }

        $validated = $request->validate([
            'patient_id' => $this->appointmentPatientRules(),
            'doctor_id' => ['required', $this->doctorExistsRule()],
            'scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $this->validateAppointmentAvailability($validated);

        Appointment::create($validated + ['status' => Appointment::STATUS_SCHEDULED]);

        return redirect()->route($this->appointmentsRoute())->with('success', 'Cita programada correctamente.');
    }

    public function editAppointment(Appointment $appointment)
    {
        $this->authorizeDoctorAppointment($appointment);

        return view('admin.citas.edit', $this->appointmentFormData($appointment));
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $this->authorizeDoctorAppointment($appointment);

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return redirect()->route($this->appointmentsRoute())->with('error', 'No se puede reprogramar una cita cancelada.');
        }

        if (Auth::user()->isMedico() && ! Auth::user()->isAdmin()) {
            $request->merge(['doctor_id' => Auth::id()]);
        }

        $validated = $request->validate([
            'patient_id' => $this->appointmentPatientRules(),
            'doctor_id' => ['required', $this->doctorExistsRule()],
            'scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'reason' => ['nullable', 'string', 'max:255'],
            'reschedule_reason' => ['required', 'string', 'max:500'],
        ]);
        $this->validateAppointmentAvailability($validated, $appointment);

        $appointment->fill($validated);
        $appointment->save();

        return redirect()->route($this->appointmentsRoute())->with('success', 'Cita reprogramada correctamente.');
    }

    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        $this->authorizeDoctorAppointment($appointment);

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($appointment->status === Appointment::STATUS_CANCELLED) {
            return redirect()->route($this->appointmentsRoute())->with('error', 'La cita ya está cancelada.');
        }

        $appointment->cancellation_reason = $validated['cancellation_reason'];
        $appointment->status = Appointment::STATUS_CANCELLED;
        $appointment->save();

        return redirect()->route($this->appointmentsRoute())->with('success', 'Cita cancelada correctamente.');
    }

    public function confirmAppointment(Appointment $appointment)
    {
        $this->authorizeDoctorAppointment($appointment);

        if ($appointment->status !== Appointment::STATUS_SCHEDULED) {
            return redirect()->route($this->appointmentsRoute())->with('error', 'Solo se pueden confirmar citas programadas.');
        }

        $appointment->status = Appointment::STATUS_CONFIRMED;
        $appointment->save();

        return redirect()->route($this->appointmentsRoute())->with('success', 'Cita confirmada correctamente.');
    }

    private function appointmentFormData(?Appointment $appointment = null): array
    {
        $user = Auth::user();
        $isDoctor = $user->isMedico() && ! $user->isAdmin();

        return [
            'user' => $user,
            'appointment' => $appointment,
            'patients' => Patient::query()->where('is_active', true)
                ->when($isDoctor, fn ($query) => $query->whereHas('appointments', fn ($appointments) => $appointments->where('doctor_id', $user->id)))
                ->orderBy('name')->get(),
            'doctors' => User::query()->whereHasRole(User::ROLE_MEDICO)
                ->where('is_active', true)
                ->when($isDoctor, fn ($query) => $query->whereKey($user->id))
                ->with('doctorProfile.shifts')->orderBy('name')->get(),
        ];
    }

    private function authorizeDoctorAppointment(Appointment $appointment): void
    {
        $user = Auth::user();
        if ($user->isMedico() && ! $user->isAdmin()) {
            abort_unless($appointment->doctor_id === $user->id, 404);
        }
    }

    private function appointmentPatientRules(): array
    {
        $user = Auth::user();
        $rules = ['required', Rule::exists('patients', 'id')->where('is_active', true)];

        if ($user->isMedico() && ! $user->isAdmin()) {
            $rules[] = Rule::exists('appointments', 'patient_id')->where('doctor_id', $user->id);
        }

        return $rules;
    }

    private function doctorExistsRule(bool $activeOnly = true): Exists
    {
        return Rule::exists('users', 'id')->where(function ($query) use ($activeOnly) {
            $query->where(function ($roles) {
                $roles->where('role', User::ROLE_MEDICO)
                    ->orWhereJsonContains('roles', User::ROLE_MEDICO);
            });

            if ($activeOnly) {
                $query->where('is_active', true);
            }
        });
    }

    private function validateAppointmentAvailability(array $data, ?Appointment $except = null): void
    {
        $scheduledAt = CarbonImmutable::parse($data['scheduled_at']);
        $available = collect($this->appointmentSlotsForDate(
            (int) $data['doctor_id'],
            $scheduledAt->startOfDay(),
            $except
        ))->contains(fn (array $slot) => $slot['time'] === $scheduledAt->format('H:i'));

        if (! $available) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'El bloque seleccionado ya no está disponible para ese médico.',
            ]);
        }
    }

    private function appointmentSlotsForDate(int $doctorId, CarbonImmutable $date, ?Appointment $except = null): array
    {
        $profile = DoctorProfile::query()->with('shifts')
            ->where('user_id', $doctorId)
            ->whereHas('user', fn ($query) => $query->whereHasRole(User::ROLE_MEDICO)->where('is_active', true))
            ->first();

        if (! $profile || ! $profile->appointment_duration) {
            return [];
        }

        $duration = $profile->appointment_duration;
        $appointments = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $date->format('Y-m-d'))
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->get();
        $slots = [];

        foreach ($profile->shifts->where('day_of_week', $date->isoWeekday()) as $shift) {
            $cursor = CarbonImmutable::parse($date->format('Y-m-d').' '.substr((string) $shift->start_time, 0, 5));
            $shiftEnd = CarbonImmutable::parse($date->format('Y-m-d').' '.substr((string) $shift->end_time, 0, 5));

            while ($cursor->addMinutes($duration)->lte($shiftEnd)) {
                $slotEnd = $cursor->addMinutes($duration);
                $isFuture = $cursor->gte(CarbonImmutable::now());
                $isOccupied = $appointments->contains(function (Appointment $appointment) use ($cursor, $slotEnd, $duration) {
                    $appointmentStart = $appointment->scheduled_at->toImmutable();
                    $appointmentEnd = $appointmentStart->addMinutes($duration);

                    return $appointmentStart->lt($slotEnd) && $appointmentEnd->gt($cursor);
                });

                if ($isFuture && ! $isOccupied) {
                    $time = $cursor->format('H:i');
                    $slots[$time] = [
                        'time' => $time,
                        'label' => $time.' - '.$slotEnd->format('H:i'),
                    ];
                }

                $cursor = $slotEnd;
            }
        }

        ksort($slots);

        return array_values($slots);
    }

    public function agenda(Request $request)
    {
        $user = Auth::user();
        $filters = $request->validate([
            'anchor' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'view' => ['nullable', Rule::in($user->isRecepcion() ? ['day', 'week', 'month'] : ['week', 'month', 'year'])],
        ]);
        $calendarView = $filters['view'] ?? ($user->isRecepcion() ? 'day' : 'month');
        $referenceDate = isset($filters['anchor'])
            ? CarbonImmutable::parse($filters['anchor'])
            : (isset($filters['month']) ? CarbonImmutable::createFromFormat('!Y-m', $filters['month']) : CarbonImmutable::today());
        $routePrefix = $user->isAdmin() ? 'admin' : ($user->isMedico() ? 'medico' : 'recepcion');

        if ($user->isRecepcion() && $calendarView === 'day') {
            return $this->agendaDay($referenceDate->format('Y-m-d'));
        }

        return view('admin.agenda.index', array_merge([
            'user' => $user,
            'title' => $user->isMedico() ? 'Mi horario' : 'Agenda',
            'description' => 'Consulta la planificación disponible para tu rol.',
            'agendaRoute' => $routePrefix.'.agenda',
            'agendaDayRoute' => $routePrefix.'.agenda.day',
        ], $this->agendaCalendarData($referenceDate, $calendarView, $user)));
    }

    public function agendaDay(string $date)
    {
        $day = CarbonImmutable::createFromFormat('!Y-m-d', $date);
        abort_unless($day && $day->format('Y-m-d') === $date, 404);

        $user = Auth::user();
        $availability = $this->doctorAvailabilityForDate($day, $user);
        $routePrefix = $user->isAdmin() ? 'admin' : ($user->isMedico() ? 'medico' : 'recepcion');

        return view('admin.agenda.day', [
            'date' => $day,
            'availability' => $availability,
            'daysOfWeek' => config('clinic.days'),
            'user' => $user,
            'listRoute' => $routePrefix.'.agenda',
            'isReception' => $user->isRecepcion(),
        ]);
    }

    private function agendaCalendarData(CarbonImmutable $referenceDate, string $view, User $user): array
    {
        $rangeStart = match ($view) {
            'year' => $referenceDate->startOfYear(),
            'week' => $referenceDate->startOfWeek(),
            default => $referenceDate->startOfMonth(),
        };
        $rangeEnd = match ($view) {
            'year' => $referenceDate->endOfYear(),
            'week' => $referenceDate->endOfWeek(),
            default => $referenceDate->endOfMonth(),
        };
        $availability = $this->doctorAvailabilityForRange($rangeStart, $rangeEnd, $user);
        $calendarStats = $availability['days'];
        $calendarDays = [];
        $calendarMonths = [];

        if ($view === 'year') {
            for ($month = 1; $month <= 12; $month++) {
                $monthStart = $referenceDate->setMonth($month)->startOfMonth();
                $gridStart = $monthStart->startOfWeek();
                $gridEnd = $monthStart->endOfMonth()->endOfWeek();
                $days = [];
                for ($date = $gridStart; $date->lte($gridEnd); $date = $date->addDay()) {
                    $days[] = [
                        'date' => $date,
                        'stats' => $calendarStats[$date->format('Y-m-d')] ?? null,
                        'inPeriod' => $date->month === $month,
                    ];
                }
                $calendarMonths[] = [
                    'label' => $monthStart->locale('es')->translatedFormat('F'),
                    'month' => $monthStart->format('Y-m'),
                    'days' => $days,
                ];
            }
        } else {
            $gridStart = $view === 'week' ? $rangeStart : $rangeStart->startOfWeek();
            $gridEnd = $view === 'week' ? $rangeEnd : $rangeEnd->endOfWeek();
            for ($date = $gridStart; $date->lte($gridEnd); $date = $date->addDay()) {
                $calendarDays[] = [
                    'date' => $date,
                    'stats' => $calendarStats[$date->format('Y-m-d')] ?? null,
                    'inPeriod' => $view === 'week' || $date->month === $referenceDate->month,
                ];
            }
        }

        $previousDate = match ($view) {
            'year' => $referenceDate->subYear(),
            'week' => $referenceDate->subWeek(),
            default => $referenceDate->subMonth(),
        };
        $nextDate = match ($view) {
            'year' => $referenceDate->addYear(),
            'week' => $referenceDate->addWeek(),
            default => $referenceDate->addMonth(),
        };

        return [
            'calendarView' => $view,
            'calendarTitle' => match ($view) {
                'year' => $referenceDate->format('Y'),
                'week' => $rangeStart->format('d/m/Y').' – '.$rangeEnd->format('d/m/Y'),
                default => $referenceDate->locale('es')->translatedFormat('F Y'),
            },
            'calendarAnchor' => $referenceDate,
            'calendarPrevious' => $previousDate->format('Y-m-d'),
            'calendarNext' => $nextDate->format('Y-m-d'),
            'calendarDays' => $calendarDays,
            'calendarMonths' => $calendarMonths,
            'weekDayLabels' => array_values(config('clinic.days')),
        ];
    }

    private function doctorAvailabilityForRange(CarbonImmutable $start, CarbonImmutable $end, User $user, ?string $office = null): array
    {
        $profiles = DoctorProfile::query()
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->with(['user', 'shifts'])
            ->when($user->isMedico() && ! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($office, fn ($query) => $query->where('office', $office))
            ->get();
        $appointments = Appointment::query()
            ->whereBetween('scheduled_at', [$start->startOfDay(), $end->endOfDay()])
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->whereHas('patient', fn ($query) => $query->where('is_active', true))
            ->whereHas('doctor', fn ($query) => $query->where('is_active', true))
            ->with('patient')
            ->get();
        $days = [];

        for ($date = $start->startOfDay(); $date->lte($end->startOfDay()); $date = $date->addDay()) {
            $days[$date->format('Y-m-d')] = $this->buildDoctorAvailability($date, $profiles, $appointments);
        }

        return ['days' => $days, 'profiles' => $profiles, 'appointments' => $appointments];
    }

    private function doctorAvailabilityForDate(CarbonImmutable $date, User $user): array
    {
        $range = $this->doctorAvailabilityForRange($date, $date, $user);

        return $range['days'][$date->format('Y-m-d')];
    }

    private function buildDoctorAvailability(CarbonImmutable $date, $profiles, $appointments): array
    {
        $weekday = $date->isoWeekday();
        $doctors = [];
        $totalSlots = 0;
        $freeSlots = 0;
        $occupiedSlots = 0;
        $expiredSlots = 0;
        $now = CarbonImmutable::now();

        foreach ($profiles as $profile) {
            $shifts = $profile->shifts->where('day_of_week', $weekday);
            if ($shifts->isEmpty()) {
                continue;
            }

            $doctorAppointments = $appointments
                ->where('doctor_id', $profile->user_id)
                ->filter(fn ($appointment) => $appointment->scheduled_at->toDateString() === $date->format('Y-m-d'));
            $slots = [];
            foreach ($shifts as $shift) {
                $cursor = CarbonImmutable::parse($date->format('Y-m-d').' '.substr((string) $shift->start_time, 0, 5));
                $shiftEnd = CarbonImmutable::parse($date->format('Y-m-d').' '.substr((string) $shift->end_time, 0, 5));

                while ($cursor->addMinutes($profile->appointment_duration)->lte($shiftEnd)) {
                    $slotEnd = $cursor->addMinutes($profile->appointment_duration);
                    $appointment = $doctorAppointments->first(function ($item) use ($cursor, $slotEnd, $profile) {
                        $appointmentStart = $item->scheduled_at->toImmutable();
                        $appointmentEnd = $appointmentStart->addMinutes($profile->appointment_duration);

                        return $appointmentStart->lt($slotEnd) && $appointmentEnd->gt($cursor);
                    });
                    $expired = $cursor->lt($now);
                    $slots[] = [
                        'start' => $cursor->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'appointment' => $appointment,
                        'expired' => $expired,
                    ];
                    $cursor = $slotEnd;
                }
            }

            $doctorFreeSlots = collect($slots)->filter(fn (array $slot) => $slot['appointment'] === null && ! $slot['expired'])->count();
            $doctorOccupiedSlots = collect($slots)->filter(fn (array $slot) => $slot['appointment'] !== null)->count();
            $doctorExpiredSlots = collect($slots)->filter(fn (array $slot) => $slot['appointment'] === null && $slot['expired'])->count();
            $totalSlots += count($slots);
            $freeSlots += $doctorFreeSlots;
            $occupiedSlots += $doctorOccupiedSlots;
            $expiredSlots += $doctorExpiredSlots;
            $doctors[] = [
                'profile' => $profile,
                'slots' => $slots,
                'freeSlots' => $doctorFreeSlots,
                'expiredSlots' => $doctorExpiredSlots,
            ];
        }
        $availableDoctors = collect($doctors)->where('freeSlots', '>', 0)->count();
        $availableDoctors = collect($doctors)->where('freeSlots', '>', 0)->count();
        $status = $freeSlots === 0
            ? 'red'
            : (($freeSlots / max($totalSlots, 1)) < 0.4 ? 'yellow' : 'green');

        return [
            'date' => $date,
            'doctors' => $doctors,
            'totalDoctors' => count($doctors),
            'availableDoctors' => $availableDoctors,
            'totalSlots' => $totalSlots,
            'freeSlots' => $freeSlots,
            'occupiedSlots' => $occupiedSlots,
            'expiredSlots' => $expiredSlots,
            'status' => $status,
        ];
    }

    public function availability()
    {
        $user = Auth::user();
        $routePrefix = $user->isAdmin() ? 'admin' : ($user->isMedico() ? 'medico' : 'recepcion');
        $isDoctor = $user->isMedico() && ! $user->isAdmin();

        return view('admin.agenda.index', array_merge([
            'user' => $user,
            'title' => 'Mi disponibilidad',
            'description' => 'Administra los espacios disponibles de atención.',
            'agendaRoute' => $isDoctor ? 'medico.availability' : $routePrefix.'.agenda',
            'agendaDayRoute' => $isDoctor ? 'medico.availability.day' : $routePrefix.'.agenda.day',
        ], $this->agendaCalendarData(CarbonImmutable::today(), 'month', $user)));
    }

    private function doctorProfileRules(?User $doctor = null, ?string $specialty = null): array
    {
        $profile = $doctor?->doctorProfile;
        return [
            'professional_title' => ['required', Rule::in(['Dr.', 'Dra.'])],
            'specialty' => ['required', Rule::in(config('clinic.specialties'))],
            'phone' => ['required', 'regex:/\A[0-9]{1,8}\z/', new UniqueClinicPhone('doctor_profiles', $profile?->id)],
            'appointment_duration' => ['required', 'integer', Rule::in([20, 30, 45])],
            'office' => ['required', Rule::in($this->consultationRoomNames($specialty, includeOffice: $profile?->office))],
            'is_active' => ['required', 'boolean'],
            'shifts' => ['required', 'array', 'min:1', 'max:14'],
            'shifts.*.days' => ['required', 'array', 'min:1', 'max:7'],
            'shifts.*.days.*' => ['required', 'integer', 'between:1,7'],
            'shifts.*.session' => ['required', Rule::in(array_keys(config('clinic.shifts')))],
        ];
    }

    private function addDoctorShiftValidation($validator, Request $request, ?User $doctor = null): void
    {
        $validator->after(function ($validator) use ($request, $doctor) {
            $office = $request->input('office');
            $specialty = $request->input('specialty');
            $shifts = $request->input('shifts', []);
            $workShifts = config('clinic.shifts');
            $assignedShifts = [];
            $allowedRooms = $this->consultationRoomNames($specialty, includeOffice: $doctor?->doctorProfile?->office);

            if (! is_array($shifts) || ! in_array($office, $allowedRooms, true)) {
                return;
            }

            $roomNumber = (int) preg_replace('/\D+/', '', $office);
            $room = ConsultationRoom::query()->where('room_number', $roomNumber)->first();
            if ($room && $specialty !== $room->specialty) {
                $validator->errors()->add('specialty', 'La especialidad del médico debe coincidir con la del consultorio.');
            }
            if ($room && ! $room->is_active && $doctor?->doctorProfile?->office !== $office) {
                $validator->errors()->add('office', 'Selecciona un consultorio activo.');
            }

            foreach ($shifts as $index => $shift) {
                if (! is_array($shift)) {
                    continue;
                }

                $session = $shift['session'] ?? null;

                if (! is_array($shift['days'] ?? null) || ! isset($workShifts[$session])) {
                    continue;
                }

                foreach ($shift['days'] as $day) {
                    if (! is_numeric($day)) {
                        continue;
                    }

                    $assignment = $day.'-'.$session;
                    if (isset($assignedShifts[$assignment])) {
                        $validator->errors()->add("shifts.{$index}.days", 'Este médico ya tiene asignado ese turno en uno de los días seleccionados.');
                        continue;
                    }
                    $assignedShifts[$assignment] = true;

                    $sessionTimes = $workShifts[$session];
                    $conflictQuery = DB::table('doctor_schedules')
                        ->join('doctor_profiles', 'doctor_profiles.id', '=', 'doctor_schedules.doctor_profile_id')
                        ->where('doctor_profiles.office', $office)
                        ->where('doctor_schedules.day_of_week', $day)
                        ->where('doctor_schedules.start_time', '<', $sessionTimes['end'].':00')
                        ->where('doctor_schedules.end_time', '>', $sessionTimes['start'].':00');

                    if ($doctor) {
                        $conflictQuery->where('doctor_profiles.user_id', '!=', $doctor->id);
                    }

                    if ($conflictQuery->exists()) {
                        $validator->errors()->add("shifts.{$index}.days", 'Ese consultorio ya está asignado a otro médico en uno de los días y turnos seleccionados.');
                    }
                }
            }
        });
    }

    private function doctorProfileData(array $validated): array
    {
        return [
            'professional_title' => $validated['professional_title'],
            'specialty' => trim($validated['specialty']),
            'phone' => $validated['phone'],
            'appointment_duration' => $validated['appointment_duration'],
            'office' => $validated['office'],
        ];
    }

    private function doctorLicenseNumber(string $specialty, int $profileId): string
    {
        if ($profileId > 999999) {
            throw new \RuntimeException('Se alcanzó el límite de cédulas profesionales generadas.');
        }

        $words = preg_split('/\s+/', Str::ascii(trim($specialty)), -1, PREG_SPLIT_NO_EMPTY);
        $prefix = count($words) > 1
            ? implode('', array_map(fn (string $word) => $word[0], array_slice($words, 0, 2)))
            : substr($words[0] ?? '', 0, 2);

        return strtoupper($prefix).str_pad((string) $profileId, 6, '0', STR_PAD_LEFT);
    }

    private function pendingDoctorLicense(): string
    {
        return 'PENDING-'.Str::random(20);
    }

    private function doctorScheduleData(array $shifts): array
    {
        $workShifts = config('clinic.shifts');

        $schedules = [];

        foreach ($shifts as $shift) {
            foreach ($shift['days'] as $day) {
                $schedules[] = [
                    'day_of_week' => $day,
                    'start_time' => $workShifts[$shift['session']]['start'],
                    'end_time' => $workShifts[$shift['session']]['end'],
                ];
            }
        }

        return $schedules;
    }

    private function doctorsRoute(): string
    {
        return Auth::user()->isAdmin() ? 'admin.doctors' : 'recepcion.doctors';
    }

    private function consultationRoomNames(?string $specialty = null, bool $activeOnly = true, ?string $includeOffice = null): array
    {
        $query = ConsultationRoom::query();
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        if ($specialty !== null) {
            $query->where('specialty', $specialty);
        }

        $rooms = $query->orderBy('room_number')->get();
        if ($includeOffice && ! $rooms->contains(fn (ConsultationRoom $room) => $room->label === $includeOffice)) {
            $roomNumber = (int) preg_replace('/\D+/', '', $includeOffice);
            $existingRoom = ConsultationRoom::query()->where('room_number', $roomNumber)->first();
            if ($existingRoom && ($specialty === null || $existingRoom->specialty === $specialty)) {
                $rooms->push($existingRoom);
            }
        }

        return $rooms
            ->map(fn (ConsultationRoom $room) => $room->label)
            ->all();
    }

    private function roomsForDoctor(User $doctor): \Illuminate\Support\Collection
    {
        $rooms = ConsultationRoom::query()->where('is_active', true)->orderBy('room_number')->get();
        $office = $doctor->doctorProfile?->office;

        if ($office && ! $rooms->contains(fn (ConsultationRoom $room) => $room->label === $office)) {
            $roomNumber = (int) preg_replace('/\D+/', '', $office);
            $assignedRoom = ConsultationRoom::query()->where('room_number', $roomNumber)->first();
            if ($assignedRoom) {
                $rooms->push($assignedRoom);
            }
        }

        return $rooms;
    }

    private function doctorCreateRoute(): string
    {
        return Auth::user()->isAdmin() ? 'admin.doctors.create' : 'recepcion.doctors.create';
    }

    private function doctorStoreRoute(): string
    {
        return Auth::user()->isAdmin() ? 'admin.doctors.store' : 'recepcion.doctors.store';
    }

    private function doctorUpdateRoute(): string
    {
        return Auth::user()->isAdmin() ? 'admin.doctors.update' : 'recepcion.doctors.update';
    }

    private function patientsRoute(): string
    {
        return Auth::user()->isAdmin() ? 'admin.patients' : 'recepcion.patients';
    }

    private function appointmentsRoute(): string
    {
        $user = Auth::user();

        return $user->isAdmin() ? 'admin.appointments' : ($user->isMedico() ? 'medico.appointments' : 'recepcion.appointments');
    }

    private function patientValidationRules(?Patient $patient = null): array
    {
        $uniqueName = Rule::unique('patients', 'name');
        $uniqueEmail = Rule::unique('patients', 'email');
        if ($patient) {
            $uniqueName->ignore($patient->id);
            $uniqueEmail->ignore($patient->id);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'email' => ['nullable', 'email', 'max:255', $uniqueEmail],
            'phone' => ['nullable', 'regex:/\A[0-9]{1,8}\z/', new UniqueClinicPhone('patients', $patient?->id)],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'string', 'in:Femenino,Masculino,Otro,Prefiero no decirlo'],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', Rule::in(['Madre', 'Padre', 'Otro'])],
            'emergency_contact_relationship_other' => ['nullable', 'required_if:emergency_contact_relationship,Otro', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'regex:/\A[0-9]{1,8}\z/'],
            'blood_type' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies_conditions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    private function normalizePatientData(array $validated): array
    {
        if (($validated['emergency_contact_relationship'] ?? null) === 'Otro') {
            $validated['emergency_contact_relationship'] = $validated['emergency_contact_relationship_other'];
        }

        unset($validated['emergency_contact_relationship_other']);

        return $validated;
    }
}