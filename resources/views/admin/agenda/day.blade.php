@extends('layout.app')

@section('content')
    <div class="flex min-h-full w-full flex-col gap-6">
        @php
            $calendarRoute = $user->isMedico() && ! $user->isAdmin() ? 'medico.availability' : $listRoute;
            $dayRoute = $user->isMedico() && ! $user->isAdmin() ? 'medico.availability.day' : $listRoute.'.day';
        @endphp
        <header class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Detalle de disponibilidad</p>
                <h2 class="mt-2 text-3xl font-bold capitalize text-[#191346]">{{ $date->locale('es')->translatedFormat('l, j \d\e F Y') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $isReception ? 'Horarios disponibles por médico.' : 'Horarios libres y ocupados por médico.' }}</p>
            </div>
            @if ($isReception)
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex rounded-xl bg-slate-100 p-1" aria-label="Vista de disponibilidad">
                        @foreach (['day' => 'Día', 'week' => 'Semana', 'month' => 'Mes'] as $viewKey => $viewLabel)
                            <a href="{{ route($listRoute, ['view' => $viewKey, 'anchor' => $date->format('Y-m-d')]) }}" class="rounded-lg px-3 py-2 text-xs font-semibold {{ $viewKey === 'day' ? 'bg-white text-[#223FAA] shadow-sm' : 'text-slate-600 hover:text-[#191346]' }}" @if ($viewKey === 'day') aria-current="page" @endif>{{ $viewLabel }}</a>
                        @endforeach
                    </div>
                    <a href="{{ route($dayRoute, $date->subDay()->format('Y-m-d')) }}" aria-label="Día anterior" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
                    <a href="{{ route($dayRoute, $date->addDay()->format('Y-m-d')) }}" aria-label="Día siguiente" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                </div>
            @else
                <a href="{{ route($calendarRoute, ['view' => 'month', 'anchor' => $date->format('Y-m-d')]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    Volver al calendario
                </a>
            @endif
        </header>

        <section class="grid gap-4 {{ $user->isMedico() ? 'sm:grid-cols-2' : 'sm:grid-cols-3' }}">
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">{{ $user->isMedico() ? 'Citas disponibles' : 'Médicos disponibles' }}</p>
                <p class="mt-2 text-2xl font-bold text-[#191346]">{{ $user->isMedico() ? $availability['freeSlots'] : $availability['availableDoctors'].' de '.$availability['totalDoctors'] }}</p>
            </article>
            @unless ($user->isMedico())
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Horarios libres</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $availability['freeSlots'] }}</p>
            </article>
            @endunless
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Horarios ocupados</p>
                <p class="mt-2 text-2xl font-bold text-red-700">{{ $availability['occupiedSlots'] }}</p>
            </article>
        </section>

        @forelse ($availability['doctors'] as $doctorAvailability)
            @php($profile = $doctorAvailability['profile'])
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
                    <div>
                        <h3 class="font-bold text-[#191346]">{{ $profile->professional_title }} {{ $profile->user->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $profile->specialty }} · {{ $profile->office }}</p>
                    </div>
                    <span class="rounded-full bg-[#eef3ff] px-3 py-1 text-xs font-semibold text-[#223FAA]">{{ $user->isMedico() ? $doctorAvailability['freeSlots'].' citas disponibles' : $doctorAvailability['freeSlots'].' espacios libres' }}</span>
                </div>
                <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($doctorAvailability['slots'] as $slot)
                        @if ($slot['appointment'])
                            <article class="rounded-xl border {{ $isReception ? 'border-slate-200 bg-slate-50' : 'border-red-200 bg-red-50' }} p-3">
                                <p class="text-sm font-bold {{ $isReception ? 'text-slate-600' : 'text-red-800' }}">{{ $slot['start'] }}–{{ $slot['end'] }} · {{ $isReception ? 'No disponible' : 'Ocupado' }}</p>
                                @unless ($isReception)
                                    <p class="mt-1 truncate text-xs text-red-700">{{ $slot['appointment']->patient?->name ?? 'Cita registrada' }}</p>
                                    <p class="mt-1 text-[11px] text-red-600">{{ $slot['appointment']->reason ?: 'Consulta' }}</p>
                                @endunless
                            </article>
                        @elseif ($slot['expired'])
                            <article class="rounded-xl border border-slate-200 bg-slate-100 p-3">
                                <p class="text-sm font-bold text-slate-600">{{ $slot['start'] }}–{{ $slot['end'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">Expirado</p>
                            </article>
                        @else
                            <article class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                                <p class="text-sm font-bold text-emerald-800">{{ $slot['start'] }}–{{ $slot['end'] }}</p>
                                <p class="mt-1 text-xs text-emerald-700">Disponible</p>
                            </article>
                        @endif
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                <i class="fa-solid fa-calendar-xmark text-2xl text-slate-400" aria-hidden="true"></i>
                <h3 class="mt-3 font-semibold text-[#191346]">Sin médicos programados</h3>
                <p class="mt-1 text-sm text-slate-500">No hay turnos registrados para este día.</p>
            </div>
        @endforelse
    </div>
@endsection