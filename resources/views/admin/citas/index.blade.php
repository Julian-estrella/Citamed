@extends('layout.app')

@section('content')
    <header class="mb-8 border-b border-slate-200 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Atención</p>
        <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $title }}</h2>
        <p class="mt-1 text-sm text-slate-500">Citas programadas y su estado actual.</p>
    </header>

    @if (session('success'))
        <p class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</p>
    @endif

    @php
        $canSchedule = $user->hasPermission('programar_citas') || $user->hasPermission('gestionar_citas');
        $isAdmin = $user->isAdmin();
        $listRoute = $isAdmin ? 'admin.appointments' : ($user->isMedico() ? 'medico.appointments' : 'recepcion.appointments');
        $storeRoute = $isAdmin ? 'admin.appointments.store' : 'recepcion.appointments.store';
        $cancelRoute = $isAdmin ? 'admin.appointments.cancel' : 'recepcion.appointments.cancel';
        $canCancel = $user->hasPermission('cancelar_citas') || $user->hasPermission('gestionar_citas');
    @endphp

    @if ($canSchedule)
        <form method="POST" action="{{ route($storeRoute) }}" class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            @csrf
            <h3 class="mb-4 font-bold text-[#191346]">Programar cita</h3>
            @if ($patients->isNotEmpty() && $doctors->isNotEmpty())
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <label class="text-sm font-medium text-slate-700">Paciente
                        <select name="patient_id" required class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                            <option value="">Seleccionar paciente</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->name }}</option>
                            @endforeach
                        </select>
                        @error('patient_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700">Médico
                        <select name="doctor_id" required class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                            <option value="">Seleccionar médico</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                        @error('doctor_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700">Fecha y hora
                        <input type="datetime-local" name="scheduled_at" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('scheduled_at') }}" required class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                        @error('scheduled_at')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700">Motivo
                        <input name="reason" value="{{ old('reason') }}" maxlength="255" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                        @error('reason')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
                <button class="mt-4 rounded-xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white">Guardar cita</button>
            @else
                <p class="text-sm text-slate-500">Registra al menos un paciente activo y un médico activo para programar citas.</p>
            @endif
        </form>
    @endif

    <div class="mb-3 flex flex-wrap items-center justify-end gap-2">
        <form id="appointment-filters" method="GET" action="{{ route($listRoute) }}" class="flex flex-wrap items-center gap-2">
            <button type="submit" class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-semibold text-white"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <a href="{{ route($listRoute) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600">Limpiar</a>
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Fecha y hora</th>
                        <th class="px-4 py-3 font-semibold">Paciente</th>
                        <th class="px-4 py-3 font-semibold">Médico</th>
                        <th class="px-4 py-3 font-semibold">Motivo</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        @if ($canCancel)
                            <th class="px-4 py-3 text-right font-semibold">Acción</th>
                        @endif
                    </tr>
                    <tr class="border-t border-slate-200 bg-white">
                        <th class="px-2 py-2"><input form="appointment-filters" type="date" name="scheduled_date" value="{{ $filters['scheduled_date'] ?? '' }}" aria-label="Filtrar por fecha" class="w-full min-w-36 rounded-lg border-slate-200 text-xs"></th>
                        <th class="px-2 py-2"><input form="appointment-filters" type="search" name="patient" value="{{ $filters['patient'] ?? '' }}" placeholder="Filtrar paciente" aria-label="Filtrar por paciente" class="w-full min-w-32 rounded-lg border-slate-200 text-xs"></th>
                        <th class="px-2 py-2"><input form="appointment-filters" type="search" name="doctor" value="{{ $filters['doctor'] ?? '' }}" placeholder="Filtrar médico" aria-label="Filtrar por médico" class="w-full min-w-32 rounded-lg border-slate-200 text-xs"></th>
                        <th class="px-2 py-2"><input form="appointment-filters" type="search" name="reason" value="{{ $filters['reason'] ?? '' }}" placeholder="Filtrar motivo" aria-label="Filtrar por motivo" class="w-full min-w-32 rounded-lg border-slate-200 text-xs"></th>
                        <th class="px-2 py-2">
                            <select form="appointment-filters" name="status" aria-label="Filtrar por estado" class="w-full min-w-28 rounded-lg border-slate-200 text-xs">
                                <option value="">Todos</option>
                                <option value="{{ \App\Models\Appointment::STATUS_SCHEDULED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_SCHEDULED)>Programada</option>
                                <option value="{{ \App\Models\Appointment::STATUS_CONFIRMED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_CONFIRMED)>Confirmada</option>
                                <option value="{{ \App\Models\Appointment::STATUS_CANCELLED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_CANCELLED)>Cancelada</option>
                            </select>
                        </th>
                        @if ($canCancel)
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3">{{ $appointment->scheduled_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-[#191346]">{{ $appointment->patient->name }}</td>
                            <td class="px-4 py-3">{{ $appointment->doctor?->name ?? 'Médico no disponible' }}</td>
                            <td class="px-4 py-3">{{ $appointment->reason ?: 'Consulta' }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($appointment->status) }}</span></td>
                            @if ($canCancel)
                                <td class="px-4 py-3 text-right">
                                    @if ($appointment->status !== \App\Models\Appointment::STATUS_CANCELLED)
                                        <form method="POST" action="{{ route($cancelRoute, $appointment) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">Cancelar</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">Sin acciones</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canCancel ? 6 : 5 }}" class="px-4 py-8 text-center text-slate-500">No se encontraron citas con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
    </div>
@endsection