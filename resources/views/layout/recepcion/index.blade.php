@extends('layout.app')

@section('content')
    @php
        $user = Auth::user();
        $userName = $user?->name ?? 'Recepción';
        $roleLabel = 'Recepción';
        $quickActions = [
            ['permission' => 'registrar_pacientes', 'label' => 'Registrar paciente', 'description' => 'Crear un registro de paciente', 'icon' => 'fa-solid fa-user-plus'],
            ['permission' => 'consultar_pacientes', 'label' => 'Consultar pacientes', 'description' => 'Buscar información de pacientes', 'icon' => 'fa-solid fa-magnifying-glass'],
            ['permission' => 'modificar_pacientes', 'label' => 'Modificar pacientes', 'description' => 'Actualizar datos de pacientes', 'icon' => 'fa-solid fa-user-pen'],
            ['permission' => 'gestionar_citas', 'label' => 'Gestionar citas', 'description' => 'Administrar citas de pacientes', 'icon' => 'fa-solid fa-calendar-check'],
            ['permission' => 'programar_citas', 'label' => 'Programar cita', 'description' => 'Agendar una nueva cita', 'icon' => 'fa-solid fa-calendar-plus'],
            ['permission' => 'modificar_citas', 'label' => 'Modificar citas', 'description' => 'Actualizar una cita programada', 'icon' => 'fa-solid fa-calendar-check'],
            ['permission' => 'cancelar_citas', 'label' => 'Cancelar cita', 'description' => 'Cancelar una cita programada', 'icon' => 'fa-solid fa-calendar-xmark'],
            ['permission' => 'consultar_horarios_disponibles_medicos', 'label' => 'Consultar horarios', 'description' => 'Ver disponibilidad de médicos', 'icon' => 'fa-solid fa-clock'],
        ];
        $availableActions = collect($quickActions)
            ->filter(fn (array $action) => $user?->hasPermission($action['permission']) ?? false);
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Panel de recepción</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Bienvenido, {{ $userName }}</h2>
                <p class="mt-1 text-sm text-slate-500">Agenda, pacientes y citas según tus permisos.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 rounded-xl bg-[#eef3ff] px-3 py-2 text-sm font-medium text-[#223FAA]">
                    <i class="fa-solid fa-calendar-day"></i>
                    {{ now()->translatedFormat('l, j \\d\\e F') }}
                </div>
                <x-user-profile-menu :user="$user" :user-name="$userName" :role-label="$roleLabel" />
            </div>
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            @if ($user?->hasPermission('consultar_pacientes') || $user?->hasPermission('registrar_pacientes'))
                <article class="rounded-2xl bg-[#223FAA] p-5 text-white">
                    <p class="text-sm text-white/80">Pacientes registrados</p>
                    <p class="mt-3 text-3xl font-bold">120</p>
                </article>
            @endif
            @if ($user?->hasPermission('gestionar_citas') || $user?->hasPermission('programar_citas'))
                <article class="rounded-2xl bg-[#AC9FE2] p-5 text-[#191346]">
                    <p class="text-sm opacity-80">Citas programadas</p>
                    <p class="mt-3 text-3xl font-bold">36</p>
                </article>
            @endif
            @if ($user?->hasPermission('consultar_horarios_disponibles_medicos'))
                <article class="rounded-2xl bg-[#AAE2E2] p-5 text-[#191346]">
                    <p class="text-sm opacity-80">Horarios disponibles</p>
                    <p class="mt-3 text-3xl font-bold">09</p>
                </article>
            @endif
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            @if ($user?->hasPermission('consultar_agenda'))
                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-[#191346]">Agenda del día</h3>
                            <p class="mt-1 text-sm text-slate-500">Citas y atención programada</p>
                        </div>
                        <i class="fa-solid fa-calendar-check text-xl text-[#223FAA]"></i>
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
                            <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-3">
                                <div>
                                    <p class="font-semibold text-[#191346]">{{ $appointment['patient'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $appointment['doctor'] }}</p>
                                </div>
                                <span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $appointment['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif

            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-[#191346]">Accesos rápidos</h3>
                        <p class="mt-1 text-sm text-slate-500">Acciones habilitadas para tu rol</p>
                    </div>
                    <i class="fa-solid fa-bolt text-[#223FAA]"></i>
                </div>

                @if ($availableActions->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($availableActions as $action)
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#eef3ff] text-[#223FAA]"><i class="{{ $action['icon'] }}"></i></span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $action['label'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $action['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No tienes acciones rápidas asignadas.</p>
                @endif
            </article>
        </section>

        @if ($user?->hasPermission('consultar_horarios_disponibles_medicos'))
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-[#191346] p-5 text-white">
                <div>
                    <h3 class="font-bold">Horarios de médicos</h3>
                    <p class="mt-1 text-sm text-slate-300">Consulta disponibilidad para coordinar la atención.</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-[#AAE2E2]"><i class="fa-solid fa-clock"></i></span>
            </section>
        @endif
    </div>
@endsection
