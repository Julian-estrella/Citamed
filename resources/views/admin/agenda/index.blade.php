@extends('layout.app')

@section('content')
    @php
        $isAvailability = $title === 'Mi disponibilidad';
        $listRoute = $user->isMedico() ? ($isAvailability ? 'medico.availability' : 'medico.agenda') : ($user->isAdmin() ? 'admin.agenda' : 'recepcion.agenda');
    @endphp

    <header class="mb-8 border-b border-slate-200 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Planificación</p>
        <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $title }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    </header>

    @if (session('success'))
        <p class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</p>
    @endif

    @if (!$isAvailability)
        <div class="mb-3 flex justify-end">
            <form id="agenda-filters" method="GET" action="{{ route($listRoute) }}" class="flex items-center gap-2">
                <button type="submit" class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-semibold text-white"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route($listRoute) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600">Limpiar</a>
            </form>
        </div>
    @endif

    @if (!$isAvailability || $appointments->isNotEmpty())
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Hora</th>
                        <th class="px-4 py-3 font-semibold">Paciente</th>
                        <th class="px-4 py-3 font-semibold">Médico</th>
                        <th class="px-4 py-3 font-semibold">Motivo</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                    </tr>
                    @if (!$isAvailability)
                        <tr class="border-t border-slate-200 bg-white">
                            <th class="px-2 py-2"><input form="agenda-filters" type="time" name="scheduled_time" value="{{ $filters['scheduled_time'] ?? '' }}" aria-label="Filtrar por hora" class="w-full min-w-28 rounded-lg border-slate-200 text-xs"></th>
                            <th class="px-2 py-2"><input form="agenda-filters" type="search" name="patient" value="{{ $filters['patient'] ?? '' }}" placeholder="Filtrar paciente" aria-label="Filtrar por paciente" class="w-full min-w-32 rounded-lg border-slate-200 text-xs"></th>
                            <th class="px-2 py-2"><input form="agenda-filters" type="search" name="doctor" value="{{ $filters['doctor'] ?? '' }}" placeholder="Filtrar médico" aria-label="Filtrar por médico" class="w-full min-w-32 rounded-lg border-slate-200 text-xs"></th>
                            <th class="px-2 py-2"><input form="agenda-filters" type="search" name="reason" value="{{ $filters['reason'] ?? '' }}" placeholder="Filtrar motivo" aria-label="Filtrar por motivo" class="w-full min-w-32 rounded-lg border-slate-200 text-xs"></th>
                            <th class="px-2 py-2">
                                <select form="agenda-filters" name="status" aria-label="Filtrar por estado" class="w-full min-w-28 rounded-lg border-slate-200 text-xs">
                                    <option value="">Todos</option>
                                    <option value="{{ \App\Models\Appointment::STATUS_SCHEDULED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_SCHEDULED)>Programada</option>
                                    <option value="{{ \App\Models\Appointment::STATUS_CONFIRMED }}" @selected(($filters['status'] ?? '') === \App\Models\Appointment::STATUS_CONFIRMED)>Confirmada</option>
                                </select>
                            </th>
                        </tr>
                    @endif
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold">{{ $appointment->scheduled_at->format('H:i') }}</td>
                            <td class="px-4 py-3">{{ $appointment->patient->name }}</td>
                            <td class="px-4 py-3">{{ $appointment->doctor?->name ?? 'Médico no disponible' }}</td>
                            <td class="px-4 py-3">{{ $appointment->reason ?: 'Consulta' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($appointment->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No se encontraron citas para esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
            <i class="fa-solid fa-calendar-days text-2xl text-slate-400"></i>
            <h3 class="mt-3 font-semibold text-slate-800">Agenda sin registros</h3>
            <p class="mt-1 text-sm text-slate-500">Las citas activas de hoy aparecerán aquí.</p>
        </div>
    @endif
@endsection