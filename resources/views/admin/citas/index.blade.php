@extends('layout.app')
@section('content')
    @php
        $isAdmin = $user->isAdmin();
        $isDoctor = $user->isMedico() && ! $isAdmin;
        $routePrefix = $isAdmin ? 'admin' : ($isDoctor ? 'medico' : 'recepcion');
        $listRoute = $routePrefix.'.appointments';
        $createRoute = $routePrefix.'.appointments.create';
        $editRoute = $routePrefix.'.appointments.edit';
        $cancelRoute = $routePrefix.'.appointments.cancel';
        $confirmRoute = $routePrefix.'.appointments.confirm';
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

                <form method="GET" action="{{ route($listRoute) }}" class="flex flex-wrap items-end gap-2">
                    <label class="min-w-[90px] flex-1 text-xs font-semibold text-slate-600">Vista
                        <select name="view" class="mt-1 w-full rounded-xl border-slate-200 bg-white px-2 py-2 text-sm">
                            <option value="day" @selected($view === 'day')>Día</option>
                            <option value="week" @selected($view === 'week')>Semana</option>
                        </select>
                    </label>
                    <label class="min-w-[130px] flex-1 text-xs font-semibold text-slate-600">Fecha de referencia
                        <input type="date" name="anchor" value="{{ $anchor->format('Y-m-d') }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white px-2 py-2 text-sm">
                    </label>
                    @if (! $user->isMedico())
                        <label class="min-w-[120px] flex-1 text-xs font-semibold text-slate-600">Médico
                            <select name="doctor_id" class="mt-1 w-full rounded-xl border-slate-200 bg-white px-2 py-2 text-sm">
                                <option value="">Todos los médicos</option>
                                @foreach ($filterDoctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) ($filters['doctor_id'] ?? '') === (string) $doctor->id)>{{ $doctor->name }}{{ $doctor->is_active ? '' : ' (inactivo)' }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label class="min-w-[115px] flex-1 text-xs font-semibold text-slate-600">Paciente
                        <input type="search" name="patient" value="{{ $filters['patient'] ?? '' }}" placeholder="Nombre del paciente" class="mt-1 w-full rounded-xl border-slate-200 bg-white px-2 py-2 text-sm">
                    </label>
                    <label class="min-w-[115px] flex-1 text-xs font-semibold text-slate-600">Motivo de consulta
                        <input type="search" name="reason" value="{{ $filters['reason'] ?? '' }}" placeholder="Filtrar motivo" class="mt-1 w-full rounded-xl border-slate-200 bg-white px-2 py-2 text-sm">
                    </label>
                    <label class="min-w-[100px] flex-1 text-xs font-semibold text-slate-600">Estado
                        <select name="status" class="mt-1 w-full rounded-xl border-slate-200 bg-white px-2 py-2 text-sm">
                            <option value="">Todos</option>
                            <option value="{{ \App\Models\Appointment::STATUS_SCHEDULED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_SCHEDULED)>Programada</option>
                            <option value="{{ \App\Models\Appointment::STATUS_CONFIRMED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_CONFIRMED)>Confirmada</option>
                            <option value="{{ \App\Models\Appointment::STATUS_CANCELLED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_CANCELLED)>Cancelada</option>
                        </select>
                    </label>
                    <div class="ml-auto flex items-end gap-2">
                        <button type="submit" aria-label="Filtrar citas" title="Filtrar citas" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#223FAA] text-sm font-semibold text-white"><i class="fa-solid fa-filter" aria-hidden="true"></i></button>
                        <a href="{{ route($listRoute) }}" aria-label="Limpiar filtros" title="Limpiar filtros" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-sm font-medium text-slate-600"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto rounded-b-3xl">
                <table class="w-full min-w-[760px] table-fixed divide-y divide-slate-200 text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="w-[15%] px-2 py-3 font-semibold sm:px-3">Fecha y hora</th>
                            <th class="w-[16%] px-2 py-3 font-semibold sm:px-3">Paciente</th>
                            <th class="w-[16%] px-2 py-3 font-semibold sm:px-3">Médico</th>
                            <th class="w-[25%] px-2 py-3 font-semibold sm:px-3">Motivo de consulta</th>
                            <th class="w-[12%] px-2 py-3 font-semibold sm:px-3">Estado</th>
                            @if ($canReschedule || $canCancel || $canConfirm)<th class="w-[16%] px-2 py-3 text-right font-semibold sm:px-3">Acciones</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td class="whitespace-nowrap px-2 py-3 font-medium sm:px-3">{{ $appointment->scheduled_at->format('d/m/Y H:i') }}</td>
                                <td class="truncate px-2 py-3 font-medium text-[#191346] sm:px-3" title="{{ $appointment->patient->name }}">{{ $appointment->patient->name }}</td>
                                <td class="truncate px-2 py-3 sm:px-3" title="{{ $appointment->doctor?->name ?? 'Médico no disponible' }}">{{ $appointment->doctor?->name ?? 'Médico no disponible' }}</td>
                                <td class="px-2 py-3 sm:px-3">
                                    <p class="truncate" title="{{ $appointment->reason }}">{{ $appointment->reason ?: 'Consulta' }}</p>
                                    @if ($appointment->reschedule_reason)<p class="mt-1 truncate text-xs text-amber-700" title="{{ $appointment->reschedule_reason }}">Reprogramación: {{ $appointment->reschedule_reason }}</p>@endif
                                    @if ($appointment->cancellation_reason)<p class="mt-1 truncate text-xs text-red-700" title="{{ $appointment->cancellation_reason }}">Cancelación: {{ $appointment->cancellation_reason }}</p>@endif
                                </td>
                                <td class="px-2 py-3 sm:px-3"><span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $appointment->status === \App\Models\Appointment::STATUS_CANCELLED ? 'bg-red-50 text-red-700' : ($appointment->status === \App\Models\Appointment::STATUS_CONFIRMED ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">{{ ucfirst($appointment->status) }}</span></td>
                                @if ($canReschedule || $canCancel || $canConfirm)
                                    <td class="px-2 py-3 text-right sm:px-3">
                                        <div class="flex flex-wrap justify-end gap-1">
                                            @if ($appointment->status === \App\Models\Appointment::STATUS_SCHEDULED && $canConfirm)
                                                <form method="POST" action="{{ route($confirmRoute, $appointment) }}">@csrf @method('PATCH')<button aria-label="Confirmar cita" title="Confirmar cita" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700"><i class="fa-solid fa-check" aria-hidden="true"></i></button></form>
                                            @endif
                                            @if ($appointment->status !== \App\Models\Appointment::STATUS_CANCELLED && $canReschedule)
                                                <a href="{{ route($editRoute, $appointment) }}" aria-label="Reprogramar cita" title="Reprogramar cita" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                            @endif
                                            @if ($appointment->status !== \App\Models\Appointment::STATUS_CANCELLED && $canCancel)
                                                <button
                                                    type="button"
                                                    data-cancel-url="{{ route($cancelRoute, $appointment) }}"
                                                    data-patient-name="{{ $appointment->patient->name }}"
                                                    data-appointment-date="{{ $appointment->scheduled_at->format('d/m/Y H:i') }}"
                                                    aria-label="Cancelar cita"
                                                    title="Cancelar cita"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700"
                                                ><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
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

            @if ($canCancel)
                <dialog id="cancel-appointment-modal" aria-labelledby="cancel-appointment-title" class="fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/50">
                    <form id="cancel-appointment-form" method="POST" class="p-6">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 id="cancel-appointment-title" class="text-lg font-bold text-[#191346]">Cancelar cita</h3>
                                <p id="cancel-appointment-details" class="mt-1 text-sm text-slate-500"></p>
                            </div>
                            <button type="button" data-close-cancel-modal aria-label="Cerrar" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                        </div>
                        <label for="cancellation_reason" class="mt-5 block text-sm font-semibold text-slate-700">Motivo de cancelación</label>
                        <textarea id="cancellation_reason" name="cancellation_reason" rows="4" maxlength="500" required class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-[#223FAA] focus:ring-[#223FAA]"></textarea>
                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" data-close-cancel-modal class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Volver</button>
                            <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Confirmar cancelación</button>
                        </div>
                    </form>
                </dialog>
                <script>
                    (() => {
                        const modal = document.getElementById('cancel-appointment-modal');
                        const form = document.getElementById('cancel-appointment-form');
                        const reason = document.getElementById('cancellation_reason');
                        const details = document.getElementById('cancel-appointment-details');

                        document.querySelectorAll('[data-cancel-url]').forEach((button) => {
                            button.addEventListener('click', () => {
                                form.action = button.dataset.cancelUrl;
                                details.textContent = `${button.dataset.patientName} · ${button.dataset.appointmentDate}`;
                                reason.value = '';
                                modal.showModal();
                                reason.focus();
                            });
                        });

                        modal.querySelectorAll('[data-close-cancel-modal]').forEach((button) => {
                            button.addEventListener('click', () => modal.close());
                        });

                        modal.addEventListener('click', (event) => {
                            if (event.target === modal) modal.close();
                        });
                    })();
                </script>
            @endif
        </section>
    </div>
@endsection