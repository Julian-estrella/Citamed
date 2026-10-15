@extends('layout.app')

@section('content')
    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Agenda de consultorio</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $room->label }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $room->specialty }}</p>
            </div>
            <a href="{{ route($listRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Consultorios
            </a>
        </header>

        @php
            $statusClasses = [
                'green' => 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100',
                'yellow' => 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100',
                'red' => 'border-red-200 bg-red-50 text-red-800 hover:bg-red-100',
            ];
            $dayStats = $availability[$anchor->format('Y-m-d')] ?? null;
        @endphp

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-slate-200 p-4 xl:flex-row xl:items-center xl:justify-between sm:p-5">
                <div>
                    <h3 class="text-lg font-bold text-[#191346]">Disponibilidad</h3>
                    <p class="mt-1 text-sm capitalize text-slate-500">{{ $periodTitle }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <nav class="inline-flex rounded-xl bg-slate-100 p-1" aria-label="Vista de disponibilidad">
                        @foreach (['day' => 'Día', 'week' => 'Semana', 'month' => 'Mes'] as $viewKey => $viewLabel)
                            <a href="{{ route($availabilityRoute, ['room' => $room, 'view' => $viewKey, 'anchor' => $anchor->format('Y-m-d')]) }}" class="rounded-lg px-3 py-2 text-xs font-semibold {{ $view === $viewKey ? 'bg-white text-[#223FAA] shadow-sm' : 'text-slate-600 hover:text-[#191346]' }}" @if ($view === $viewKey) aria-current="page" @endif>{{ $viewLabel }}</a>
                        @endforeach
                    </nav>
                    <form method="GET" action="{{ route($availabilityRoute, $room) }}" class="flex items-center gap-2">
                        <input type="hidden" name="view" value="{{ $view }}">
                        <label for="availability-date" class="sr-only">Seleccionar fecha</label>
                        <input id="availability-date" type="date" name="anchor" value="{{ $anchor->format('Y-m-d') }}" class="rounded-lg border-slate-200 text-sm focus:border-[#223FAA] focus:ring-[#223FAA]">
                        <button type="submit" aria-label="Ir a la fecha seleccionada" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#223FAA] text-white hover:bg-[#191346]"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                    </form>
                    <div class="flex gap-1">
                        <a href="{{ route($availabilityRoute, ['room' => $room, 'view' => $view, 'anchor' => $previous]) }}" aria-label="Periodo anterior" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
                        <a href="{{ route($availabilityRoute, ['room' => $room, 'view' => $view, 'anchor' => $next]) }}" aria-label="Periodo siguiente" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-x-5 gap-y-2 border-b border-slate-100 px-4 py-3 text-xs text-slate-600 sm:px-5">
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Alta disponibilidad</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>Disponibilidad limitada</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>Sin horarios libres</span>
            </div>

            @if ($view === 'day')
                @if ($dayStats)
                    <div class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-3 sm:p-5">
                        <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Médicos disponibles</p><p class="mt-1 font-bold text-[#191346]">{{ $dayStats['availableDoctors'] }} / {{ $dayStats['totalDoctors'] }}</p></div>
                        <div class="rounded-xl bg-emerald-50 p-3"><p class="text-xs text-emerald-700">Horarios libres</p><p class="mt-1 font-bold text-emerald-800">{{ $dayStats['freeSlots'] }}</p></div>
                        <div class="rounded-xl bg-red-50 p-3"><p class="text-xs text-red-700">Horarios ocupados</p><p class="mt-1 font-bold text-red-800">{{ $dayStats['occupiedSlots'] }}</p></div>
                    </div>
                    <div class="space-y-4 p-4 sm:p-5">
                        @forelse ($dayStats['doctors'] as $doctorAvailability)
                            @php($profile = $doctorAvailability['profile'])
                            <article class="overflow-hidden rounded-xl border border-slate-200">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
                                    <div>
                                        <h4 class="font-semibold text-[#191346]">{{ $profile->professional_title }} {{ $profile->user->name }}</h4>
                                        <p class="text-xs text-slate-500">{{ $profile->specialty }} · {{ $profile->office }}</p>
                                    </div>
                                    <span class="text-xs font-medium text-slate-600">{{ $doctorAvailability['freeSlots'] }} horarios libres</span>
                                </div>
                                <div class="grid gap-2 p-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                    @foreach ($doctorAvailability['slots'] as $slot)
                                        @if ($slot['appointment'])
                                            <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                                                <p class="text-sm font-semibold text-red-800">{{ $slot['start'] }}–{{ $slot['end'] }} · Ocupado</p>
                                                <p class="mt-1 truncate text-xs text-red-700">{{ $slot['appointment']->patient?->name ?? 'Cita registrada' }}</p>
                                                <p class="mt-1 text-[11px] text-red-600">{{ $slot['appointment']->reason ?: 'Consulta' }}</p>
                                            </div>
                                        @else
                                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                                                <p class="text-sm font-semibold text-emerald-800">{{ $slot['start'] }}–{{ $slot['end'] }}</p>
                                                <p class="mt-1 text-xs text-emerald-700">Disponible</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">Sin médicos programados para este día.</p>
                        @endforelse
                    </div>
                @else
                    <p class="p-8 text-center text-sm text-slate-500">Sin disponibilidad para este día.</p>
                @endif
            @elseif ($view === 'week')
                <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 sm:p-5">
                    @foreach ($calendarDays as $day)
                        @php($stats = $day['stats'])
                        <a href="{{ route($availabilityRoute, ['room' => $room, 'view' => 'day', 'anchor' => $day['date']->format('Y-m-d')]) }}" class="rounded-xl border p-3 transition {{ $statusClasses[$stats['status']] }}">
                            <p class="text-sm font-bold capitalize">{{ $day['date']->locale('es')->translatedFormat('D j') }}</p>
                            <p class="mt-3 text-xs">{{ $stats['availableDoctors'] }} / {{ $stats['totalDoctors'] }} médicos</p>
                            <p class="text-xs">{{ $stats['freeSlots'] }} libres · {{ $stats['occupiedSlots'] }} ocupados</p>
                        </a>
                    @endforeach
                </div>
            @else
                @php($weekLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'])
                <div class="overflow-x-auto p-4 sm:p-5">
                    <div class="min-w-[620px]">
                        <div class="mb-2 grid grid-cols-7 text-center text-xs font-semibold text-slate-500">
                            @foreach ($weekLabels as $weekday)<span class="py-2">{{ $weekday }}</span>@endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-1.5">
                            @foreach ($calendarDays as $day)
                                @if ($day['inPeriod'] && $day['stats'])
                                    <a href="{{ route($availabilityRoute, ['room' => $room, 'view' => 'day', 'anchor' => $day['date']->format('Y-m-d')]) }}" aria-label="{{ $day['date']->format('d/m/Y') }}: {{ $day['stats']['freeSlots'] }} horarios libres" class="min-h-20 rounded-lg border p-2 transition {{ $statusClasses[$day['stats']['status']] }}">
                                        <span class="text-sm font-bold">{{ $day['date']->day }}</span>
                                        <span class="mt-1 block text-[10px] leading-4">{{ $day['stats']['freeSlots'] }} libres</span>
                                    </a>
                                @else
                                    <span class="min-h-20 rounded-lg border border-slate-100 bg-slate-50 p-2 text-sm text-slate-300">{{ $day['date']->day }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection