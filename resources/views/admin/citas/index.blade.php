@extends('layout.app')
@section('content')
    @php
        $isAdmin = $user->isAdmin();
        $listRoute = $isAdmin ? 'admin.appointments' : ($user->isMedico() ? 'medico.appointments' : 'recepcion.appointments');
        $createRoute = $isAdmin ? 'admin.appointments.create' : 'recepcion.appointments.create';
        $editRoute = $isAdmin ? 'admin.appointments.edit' : 'recepcion.appointments.edit';
        $cancelRoute = $isAdmin ? 'admin.appointments.cancel' : 'recepcion.appointments.cancel';
        $confirmRoute = $isAdmin ? 'admin.appointments.confirm' : 'recepcion.appointments.confirm';
        $canSchedule = $user->hasPermission('programar_citas') || $user->hasPermission('gestionar_citas');
        $canReschedule = $user->hasPermission('modificar_citas') || $user->hasPermission('gestionar_citas');
        $canCancel = $user->hasPermission('cancelar_citas') || $user->hasPermission('gestionar_citas');
        $canConfirm = $canReschedule;
        $previousAnchor = $view === 'week' ? $anchor->subWeek()->format('Y-m-d') : $anchor->subDay()->format('Y-m-d');
        $nextAnchor = $view === 'week' ? $anchor->addWeek()->format('Y-m-d') : $anchor->addDay()->format('Y-m-d');
        $periodLabel = $view === 'week'
            ? $anchor->startOfWeek()->format('d/m/Y').' - '.$anchor->endOfWeek()->format('d/m/Y')
            : $anchor->locale('es')->translatedFormat('l, j \\d\\e F Y');
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Panel de atención</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $title }}</h2>
            </div>
            @if ($canSchedule)
                <a href="{{ route($createRoute) }}" class="inline-flex items-center gap-2 self-start rounded-2xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20 md:self-auto">
                    <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i>Nueva cita
                </a>
            @endif
        </header>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-slate-200 p-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-[#191346]">Agenda de citas</h3>
                        <p class="text-sm capitalize text-slate-500">{{ $periodLabel }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route($listRoute, array_merge(request()->query(), ['anchor' => $previousAnchor])) }}" aria-label="Periodo anterior" title="Periodo anterior" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
                        <a href="{{ route($listRoute, array_merge(request()->query(), ['anchor' => today()->toDateString()])) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700">Hoy</a>
                        <a href="{{ route($listRoute, array_merge(request()->query(), ['anchor' => $nextAnchor])) }}" aria-label="Periodo siguiente" title="Periodo siguiente" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                    </div>
                </div>

                <form method="GET" action="{{ route($listRoute) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
                    <label class="text-xs font-semibold text-slate-600">Vista
                        <select name="view" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                            <option value="day" @selected($view === 'day')>Día</option>
                            <option value="week" @selected($view === 'week')>Semana</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold text-slate-600">Fecha de referencia
                        <input type="date" name="anchor" value="{{ $anchor->format('Y-m-d') }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                    </label>
                    @if (! $user->isMedico())
                        <label class="text-xs font-semibold text-slate-600">Médico
                            <select name="doctor_id" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                                <option value="">Todos los médicos</option>
                                @foreach ($filterDoctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) ($filters['doctor_id'] ?? '') === (string) $doctor->id)>{{ $doctor->name }}{{ $doctor->is_active ? '' : ' (inactivo)' }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label class="text-xs font-semibold text-slate-600">Paciente
                        <input type="search" name="patient" value="{{ $filters['patient'] ?? '' }}" placeholder="Nombre del paciente" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                    </label>
                    <label class="text-xs font-semibold text-slate-600">Motivo de consulta
                        <input type="search" name="reason" value="{{ $filters['reason'] ?? '' }}" placeholder="Filtrar motivo" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                    </label>
                    <label class="text-xs font-semibold text-slate-600">Estado
                        <select name="status" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                            <option value="">Todos</option>
                            <option value="{{ \App\Models\Appointment::STATUS_SCHEDULED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_SCHEDULED)>Programada</option>
                            <option value="{{ \App\Models\Appointment::STATUS_CONFIRMED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_CONFIRMED)>Confirmada</option>
                            <option value="{{ \App\Models\Appointment::STATUS_CANCELLED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_CANCELLED)>Cancelada</option>
                        </select>
                    </label>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[#223FAA] px-3 text-sm font-semibold text-white"><i class="fa-solid fa-filter" aria-hidden="true"></i>Filtrar</button>
                        <a href="{{ route($listRoute) }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-3 text-sm font-medium text-slate-600">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto rounded-b-3xl">
                <table class="w-full min-w-[900px] divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Fecha y hora</th>
                            <th class="px-4 py-3 font-semibold">Paciente</th>
                            <th class="px-4 py-3 font-semibold">Médico</th>
                            <th class="px-4 py-3 font-semibold">Motivo de consulta</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            @if ($canReschedule || $canCancel || $canConfirm)<th class="px-4 py-3 text-right font-semibold">Acciones</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $appointment->scheduled_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 font-medium text-[#191346]">{{ $appointment->patient->name }}</td>
                                <td class="px-4 py-3">{{ $appointment->doctor?->name ?? 'Médico no disponible' }}</td>
                                <td class="max-w-56 px-4 py-3">
                                    <p class="truncate" title="{{ $appointment->reason }}">{{ $appointment->reason ?: 'Consulta' }}</p>
                                    @if ($appointment->reschedule_reason)<p class="mt-1 truncate text-xs text-amber-700" title="{{ $appointment->reschedule_reason }}">Reprogramación: {{ $appointment->reschedule_reason }}</p>@endif
                                    @if ($appointment->cancellation_reason)<p class="mt-1 truncate text-xs text-red-700" title="{{ $appointment->cancellation_reason }}">Cancelación: {{ $appointment->cancellation_reason }}</p>@endif
                                </td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $appointment->status === \App\Models\Appointment::STATUS_CANCELLED ? 'bg-red-50 text-red-700' : ($appointment->status === \App\Models\Appointment::STATUS_CONFIRMED ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">{{ ucfirst($appointment->status) }}</span></td>
                                @if ($canReschedule || $canCancel || $canConfirm)
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($appointment->status === \App\Models\Appointment::STATUS_SCHEDULED && $canConfirm)
                                                <form method="POST" action="{{ route($confirmRoute, $appointment) }}">@csrf @method('PATCH')<button class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Confirmar</button></form>
                                            @endif
                                            @if ($appointment->status !== \App\Models\Appointment::STATUS_CANCELLED && $canReschedule)
                                                <a href="{{ route($editRoute, $appointment) }}" aria-label="Reprogramar cita" title="Reprogramar cita" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                            @endif
                                            @if ($appointment->status !== \App\Models\Appointment::STATUS_CANCELLED && $canCancel)
                                                <details class="relative text-left">
                                                    <summary class="cursor-pointer list-none rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Cancelar</summary>
                                                    <form method="POST" action="{{ route($cancelRoute, $appointment) }}" class="absolute right-0 z-20 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
                                                        @csrf @method('PATCH')
                                                        <label class="block text-xs font-semibold text-slate-700">Motivo de cancelación<textarea name="cancellation_reason" rows="3" maxlength="500" required class="mt-1 w-full rounded-lg border-slate-200 text-sm"></textarea></label>
                                                        <button class="mt-2 w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white">Confirmar cancelación</button>
                                                    </form>
                                                </details>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ ($canReschedule || $canCancel || $canConfirm) ? 6 : 5 }}" class="px-4 py-10 text-center text-slate-500">No hay citas para este periodo y filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection