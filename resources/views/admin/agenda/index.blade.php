@extends('layout.app')

@section('content')
    <header class="mb-8 border-b border-slate-200 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Planificación</p>
        <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $title }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    </header>

    @if (session('success'))
        <p class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</p>
    @endif

    <section class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-4 sm:p-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-lg font-bold text-[#191346]">Disponibilidad médica</h3>
                <p class="mt-1 text-sm capitalize text-slate-500">{{ $calendarTitle }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-xl bg-slate-100 p-1" aria-label="Vista del calendario">
                    @foreach (($user->isRecepcion() ? ['day' => 'Día', 'week' => 'Semana', 'month' => 'Mes'] : ['week' => 'Semana', 'month' => 'Mes', 'year' => 'Año']) as $viewKey => $viewLabel)
                        <a href="{{ route($agendaRoute, ['view' => $viewKey, 'anchor' => $calendarAnchor->format('Y-m-d')]) }}" class="rounded-lg px-3 py-2 text-xs font-semibold {{ $calendarView === $viewKey ? 'bg-white text-[#223FAA] shadow-sm' : 'text-slate-600 hover:text-[#191346]' }}" @if ($calendarView === $viewKey) aria-current="page" @endif>{{ $viewLabel }}</a>
                    @endforeach
                </div>
                @if ($calendarView !== 'day')
                    <form method="GET" action="{{ route($agendaRoute) }}" class="flex items-center gap-2">
                    <input type="hidden" name="view" value="month">
                    <label for="agenda-month" class="sr-only">Seleccionar mes</label>
                    <input id="agenda-month" type="month" name="month" value="{{ $calendarAnchor->format('Y-m') }}" class="rounded-lg border-slate-200 text-sm focus:border-[#223FAA] focus:ring-[#223FAA]">
                    <button type="submit" aria-label="Ir al mes seleccionado" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#223FAA] text-white hover:bg-[#191346]"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                    </form>
                @endif
                <div class="flex gap-1">
                    <a href="{{ route($agendaRoute, ['view' => $calendarView, 'anchor' => $calendarPrevious]) }}" aria-label="Periodo anterior" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
                    <a href="{{ route($agendaRoute, ['view' => $calendarView, 'anchor' => $calendarNext]) }}" aria-label="Periodo siguiente" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-x-5 gap-y-2 border-b border-slate-100 px-4 py-3 text-xs text-slate-600 sm:px-5">
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Alta disponibilidad</span>
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>Disponibilidad limitada</span>
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>Sin horarios libres</span>
        </div>

        @php
            $availabilityClasses = [
                'green' => 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100',
                'yellow' => 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100',
                'red' => 'border-red-200 bg-red-50 text-red-800 hover:bg-red-100',
            ];
            $weekLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        @endphp

        @if ($calendarView === 'year')
            <div class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3 sm:p-5">
                @foreach ($calendarMonths as $calendarMonth)
                    <article class="rounded-xl border border-slate-200 p-3">
                        <a href="{{ route($agendaRoute, ['view' => 'month', 'anchor' => $calendarMonth['month'].'-01']) }}" class="mb-3 block text-sm font-bold capitalize text-[#191346] hover:text-[#223FAA]">{{ $calendarMonth['label'] }}</a>
                        <div class="mb-1 grid grid-cols-7 text-center text-[10px] font-semibold text-slate-400">
                            @foreach ($weekLabels as $weekday)
                                <span>{{ $weekday[0] }}</span>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            @foreach ($calendarMonth['days'] as $day)
                                @if ($day['inPeriod'] && $day['stats'])
                                    <a href="{{ route($agendaDayRoute, $day['date']->format('Y-m-d')) }}" aria-label="{{ $day['date']->format('d/m/Y') }}: {{ $day['stats']['availableDoctors'] }} médicos disponibles, {{ $day['stats']['freeSlots'] }} horarios libres" class="flex aspect-square items-center justify-center rounded-md text-[10px] font-semibold {{ $availabilityClasses[$day['stats']['status']] }}">{{ $day['date']->day }}</a>
                                @else
                                    <span class="flex aspect-square items-center justify-center text-[10px] text-slate-300">{{ $day['date']->day }}</span>
                                @endif
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="overflow-x-auto p-4 sm:p-5">
                <div class="min-w-[700px]">
                    <div class="mb-2 grid grid-cols-7 text-center text-xs font-semibold text-slate-500">
                        @foreach ($weekDayLabels as $weekday)
                            <span class="py-2">{{ $weekday }}</span>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-1.5">
                        @foreach ($calendarDays as $day)
                            @if ($day['inPeriod'] && $day['stats'])
                                <a href="{{ route($agendaDayRoute, $day['date']->format('Y-m-d')) }}" aria-label="{{ $user->isMedico() ? $day['stats']['freeSlots'].' citas disponibles' : $day['date']->format('d/m/Y').': '.$day['stats']['availableDoctors'].' médicos disponibles, '.$day['stats']['freeSlots'].' horarios libres' }}" class="min-h-24 rounded-lg border p-2 transition {{ $availabilityClasses[$day['stats']['status']] }}">
                                    <span class="text-sm font-bold">{{ $day['date']->day }}</span>
                                    @if ($user->isMedico())
                                    <span class="mt-2 block text-[10px] leading-4">{{ $day['stats']['freeSlots'] }} citas disponibles</span>
                                    @else
                                        <span class="mt-2 block text-[10px] leading-4">{{ $day['stats']['availableDoctors'] }}/{{ $day['stats']['totalDoctors'] }} médicos</span>
                                        <span class="block text-[10px] leading-4">{{ $day['stats']['freeSlots'] }} horarios libres</span>
                                    @endif
                                </a>
                            @else
                                <span class="min-h-24 rounded-lg border border-slate-100 bg-slate-50 p-2 text-sm text-slate-300">{{ $day['date']->day }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </section>

@endsection