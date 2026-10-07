@extends('layout.app')

@section('content')
    @php
        $user = Auth::user();
    @endphp

    <div class="flex min-h-full w-full flex-col">
        <header class="mb-8 flex items-center justify-between border-b border-slate-200 pb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#223FAA]">Panel administrativo</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Bienvenido, {{ $user?->name ?? 'Usuario' }}</h2>
            </div>
            <div class="flex items-center gap-3 rounded-2xl bg-slate-100 px-3 py-2">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-[#223FAA] via-[#AC9FE2] to-[#AAE2E2] text-sm font-bold text-white">
                    {{ strtoupper(substr(($user?->name ?? 'U'), 0, 2)) }}
                </div>
                <div class="text-sm text-slate-700">
                    <p class="font-semibold">{{ $user?->name ?? 'Usuario' }}</p>
                    <p class="text-xs text-slate-500">Administrador</p>
                </div>
            </div>
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-3xl bg-[#223FAA] p-5 text-white shadow-lg shadow-[#223FAA]/20">
                <p class="text-sm text-white/80">Pacientes</p>
                <p class="mt-3 text-3xl font-bold">1,284</p>
            </article>
            <article class="rounded-3xl bg-[#AC9FE2] p-5 text-[#191346] shadow-lg shadow-[#AC9FE2]/20">
                <p class="text-sm opacity-80">Citas hoy</p>
                <p class="mt-3 text-3xl font-bold">48</p>
            </article>
            <article class="rounded-3xl bg-[#AAE2E2] p-5 text-[#191346] shadow-lg shadow-[#AAE2E2]/20">
                <p class="text-sm opacity-80">Médicos</p>
                <p class="mt-3 text-3xl font-bold">12</p>
            </article>
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-[#191346]">Próximas citas</h3>
                    <button class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-medium text-white">Ver agenda</button>
                </div>
                <div class="space-y-3">
                    @php
                        $appointments = [
                            ['time' => '08:30', 'patient' => 'Andrea López', 'doctor' => 'Dr. Ramírez'],
                            ['time' => '10:15', 'patient' => 'José García', 'doctor' => 'Dra. Silva'],
                            ['time' => '12:00', 'patient' => 'María Pérez', 'doctor' => 'Dr. Ortega'],
                        ];
                    @endphp

                    @foreach ($appointments as $appointment)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-3">
                            <div>
                                <p class="font-semibold text-[#191346]">{{ $appointment['patient'] }}</p>
                                <p class="text-sm text-slate-500">{{ $appointment['doctor'] }}</p>
                            </div>
                            <span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $appointment['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl bg-[#191346] p-5 text-white shadow-xl shadow-slate-300/20">
                <h3 class="text-xl font-bold">Resumen rápido</h3>
                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Pacientes pendientes</p>
                        <p class="mt-2 text-2xl font-bold">24</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Citas confirmadas</p>
                        <p class="mt-2 text-2xl font-bold">36</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Agenda ocupada</p>
                        <p class="mt-2 text-2xl font-bold">72%</p>
                    </div>
                </div>
            </article>
        </section>
    </div>
@endsection
