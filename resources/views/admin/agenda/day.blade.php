@extends('layout.app')

@section('content')
    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Detalle de disponibilidad</p>
                <h2 class="mt-2 text-3xl font-bold capitalize text-[#191346]">{{ $date->locale('es')->translatedFormat('l, j \d\e F Y') }}</h2>
                <p class="mt-1 text-sm text-slate-500">Horarios libres y ocupados por médico.</p>
            </div>
            <a href="{{ route($listRoute, ['view' => 'month', 'anchor' => $date->format('Y-m-d')]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Volver al calendario
            </a>
        </header>

        <section class="grid gap-4 sm:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Médicos disponibles</p>
                <p class="mt-2 text-2xl font-bold text-[#191346]">{{ $availability['availableDoctors'] }} <span class="text-sm font-medium text-slate-500">de {{ $availability['totalDoctors'] }}</span></p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Horarios libres</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $availability['freeSlots'] }}</p>
            </article>
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
                    <span class="rounded-full bg-[#eef3ff] px-3 py-1 text-xs font-semibold text-[#223FAA]">{{ $doctorAvailability['freeSlots'] }} espacios libres</span>
                </div>
                <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($doctorAvailability['slots'] as $slot)
                        @if ($slot['appointment'])
                            <article class="rounded-xl border border-red-200 bg-red-50 p-3">
                                <p class="text-sm font-bold text-red-800">{{ $slot['start'] }}–{{ $slot['end'] }} · Ocupado</p>
                                <p class="mt-1 truncate text-xs text-red-700">{{ $slot['appointment']->patient?->name ?? 'Cita registrada' }}</p>
                                <p class="mt-1 text-[11px] text-red-600">{{ $slot['appointment']->reason ?: 'Consulta' }}</p>
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