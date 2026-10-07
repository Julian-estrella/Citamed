@extends('layout.app')

@section('content')
    @php
        $user = Auth::user();
    @endphp

    <div class="flex min-h-full w-full flex-col">
        <header class="mb-8 border-b border-slate-200 pb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Panel médico</p>
            <h2 class="mt-2 text-3xl font-bold text-[#191346]">Bienvenido, {{ $user?->name ?? 'Médico' }}</h2>
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-3xl bg-[#223FAA] p-5 text-white">
                <p class="text-sm text-white/80">Citas de hoy</p>
                <p class="mt-3 text-3xl font-bold">07</p>
            </article>
            <article class="rounded-3xl bg-[#AC9FE2] p-5 text-[#191346]">
                <p class="text-sm opacity-80">Pacientes atendidos</p>
                <p class="mt-3 text-3xl font-bold">18</p>
            </article>
            <article class="rounded-3xl bg-[#AAE2E2] p-5 text-[#191346]">
                <p class="text-sm opacity-80">Disponibilidad</p>
                <p class="mt-3 text-3xl font-bold">92%</p>
            </article>
        </section>

        <section class="mt-8 rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-xl font-bold text-[#191346]">Agenda del médico</h3>
            <div class="mt-4 space-y-3">
                @php
                    $schedule = [
                        ['time' => '08:30', 'patient' => 'Andrea López', 'type' => 'Consulta general'],
                        ['time' => '10:15', 'patient' => 'José García', 'type' => 'Control cardiaco'],
                        ['time' => '15:40', 'patient' => 'Sofía Ruiz', 'type' => 'Dolor de espalda'],
                    ];
                @endphp

                @foreach ($schedule as $item)
                    <div class="flex items-center justify-between rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                        <div>
                            <p class="font-semibold text-[#191346]">{{ $item['patient'] }}</p>
                            <p class="text-sm text-slate-500">{{ $item['type'] }}</p>
                        </div>
                        <span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $item['time'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
