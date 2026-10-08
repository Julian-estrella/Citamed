@extends('layout.app')

@section('content')
    @php
        $user = Auth::user();
        $userName = $user?->name ?? 'Usuario';
        $roleLabel = match ($user?->role ?? '') {
            'administrador' => 'Administrador',
            'medico' => 'Médico',
            'recepcion' => 'Recepción',
            default => 'Usuario',
        };
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Panel administrativo</p>
                <h2 class="mt-3 text-3xl font-bold text-[#191346]">Bienvenido, {{ $userName }}</h2>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
                <div class="relative">
                    <button type="button" class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#eef3ff] text-[#223FAA] transition hover:bg-[#e0e9ff]">
                        <i class="fa-solid fa-bell"></i>
                    </button>
                    <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-[#223FAA] text-[10px] font-bold text-white">3</span>
                </div>

                <x-user-profile-menu :user="$user" :user-name="$userName" :role-label="$roleLabel" />
            </div>
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-[22px] bg-[#223FAA] p-5 text-white shadow-lg shadow-[#223FAA]/20">
                <p class="text-sm text-white/80">Pacientes</p>
                <p class="mt-3 text-3xl font-bold">1,284</p>
            </article>
            <article class="rounded-[22px] bg-[#AC9FE2] p-5 text-[#191346] shadow-lg shadow-[#AC9FE2]/20">
                <p class="text-sm opacity-80">Citas hoy</p>
                <p class="mt-3 text-3xl font-bold">48</p>
            </article>
            <article class="rounded-[22px] bg-[#AAE2E2] p-5 text-[#191346] shadow-lg shadow-[#AAE2E2]/20">
                <p class="text-sm opacity-80">Médicos</p>
                <p class="mt-3 text-3xl font-bold">12</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-[26px] border border-slate-200 bg-slate-50 p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-[#191346]">Agenda del día</h3>
                    <button class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-medium text-white">Ver agenda</button>
                </div>

                <div class="space-y-3">
                    @php
                        $schedule = [
                            ['time' => '08:30', 'patient' => 'Andrea López', 'doctor' => 'Dr. Ramírez', 'type' => 'Consulta general'],
                            ['time' => '10:15', 'patient' => 'José García', 'doctor' => 'Dra. Silva', 'type' => 'Control cardiaco'],
                            ['time' => '12:00', 'patient' => 'María Pérez', 'doctor' => 'Dr. Ortega', 'type' => 'Seguimiento'],
                        ];
                    @endphp

                    @foreach ($schedule as $item)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                            <div>
                                <p class="font-semibold text-[#191346]">{{ $item['patient'] }}</p>
                                <p class="text-sm text-slate-500">{{ $item['doctor'] }} · {{ $item['type'] }}</p>
                            </div>
                            <span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $item['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[26px] bg-[#191346] p-5 text-white shadow-xl shadow-slate-300/20">
                <h3 class="text-2xl font-bold">Resumen rápido</h3>
                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Pacientes pendientes</p>
                        <p class="mt-2 text-3xl font-bold">24</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Citas confirmadas</p>
                        <p class="mt-2 text-3xl font-bold">36</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Agenda ocupada</p>
                        <p class="mt-2 text-3xl font-bold">72%</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <article class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-[#191346]">Próximas citas</h3>
                    <button class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-medium text-white">Ver más</button>
                </div>

                <div class="space-y-3">
                    @php
                        $appointments = [
                            ['time' => '08:30', 'patient' => 'Andrea López', 'doctor' => 'Dr. Ramírez'],
                            ['time' => '10:15', 'patient' => 'José García', 'doctor' => 'Dra. Silva'],
                            ['time' => '12:00', 'patient' => 'María Pérez', 'doctor' => 'Dr. Ortega'],
                            ['time' => '15:40', 'patient' => 'Sofía Ruiz', 'doctor' => 'Dra. Cortés'],
                        ];
                    @endphp

                    @foreach ($appointments as $appointment)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div>
                                <p class="font-semibold text-[#191346]">{{ $appointment['patient'] }}</p>
                                <p class="text-sm text-slate-500">{{ $appointment['doctor'] }}</p>
                            </div>
                            <span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $appointment['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-[#191346]">Accesos rápidos</h3>
                    <i class="fa-solid fa-bolt text-[#223FAA]"></i>
                </div>

                <div class="space-y-3">
                    <a href="#" class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 text-sm font-medium text-slate-700">
                        <span><i class="fa-solid fa-user-plus mr-2 text-[#223FAA]"></i>Registrar paciente</span>
                        <i class="fa-solid fa-arrow-right text-slate-400"></i>
                    </a>
                    <a href="#" class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 text-sm font-medium text-slate-700">
                        <span><i class="fa-solid fa-calendar-plus mr-2 text-[#223FAA]"></i>Agendar cita</span>
                        <i class="fa-solid fa-arrow-right text-slate-400"></i>
                    </a>
                    <a href="#" class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 text-sm font-medium text-slate-700">
                        <span><i class="fa-solid fa-user-injured mr-2 text-[#223FAA]"></i>Consultar pacientes</span>
                        <i class="fa-solid fa-arrow-right text-slate-400"></i>
                    </a>
                    <a href="#" class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 text-sm font-medium text-slate-700">
                        <span><i class="fa-solid fa-stethoscope mr-2 text-[#223FAA]"></i>Ver médicos</span>
                        <i class="fa-solid fa-arrow-right text-slate-400"></i>
                    </a>
                </div>
            </article>
        </section>
    </div>
@endsection
