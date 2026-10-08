@extends('layout.app')

@section('content')
    @php
        $user = Auth::user();
        $userName = $user?->name ?? 'Médico';
        $roleLabel = 'Médico';
        $quickActions = [
            ['permission' => 'consultar_citas', 'label' => 'Mis citas', 'description' => 'Consultar las citas asignadas', 'icon' => 'fa-solid fa-calendar-check'],
            ['permission' => 'consultar_pacientes_que_atiende', 'label' => 'Mis pacientes', 'description' => 'Consultar los pacientes asignados', 'icon' => 'fa-solid fa-user-injured'],
            ['permission' => 'gestionar_horario_atencion', 'label' => 'Gestionar horario', 'description' => 'Actualizar tu horario de atención', 'icon' => 'fa-solid fa-clock'],
            ['permission' => 'gestionar_disponibilidad', 'label' => 'Disponibilidad', 'description' => 'Administrar tus espacios disponibles', 'icon' => 'fa-solid fa-calendar-plus'],
            ['permission' => 'consultar_historial_citas_pacientes', 'label' => 'Historial de citas', 'description' => 'Revisar citas anteriores de pacientes', 'icon' => 'fa-solid fa-file-medical'],
        ];
        $availableActions = collect($quickActions)
            ->filter(fn (array $action) => $user?->hasPermission($action['permission']) ?? false);
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Panel médico</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Bienvenido, {{ $userName }}</h2>
                <p class="mt-1 text-sm text-slate-500">Agenda y herramientas de atención médica.</p>
            </div>
            <x-user-profile-menu :user="$user" :user-name="$userName" :role-label="$roleLabel" />
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            @if ($user?->hasPermission('consultar_citas'))
                <article class="rounded-2xl bg-[#223FAA] p-5 text-white">
                    <p class="text-sm text-white/80">Citas de hoy</p>
                    <p class="mt-3 text-3xl font-bold">07</p>
                </article>
            @endif
            @if ($user?->hasPermission('consultar_pacientes_que_atiende'))
                <article class="rounded-2xl bg-[#AC9FE2] p-5 text-[#191346]">
                    <p class="text-sm opacity-80">Pacientes atendidos</p>
                    <p class="mt-3 text-3xl font-bold">18</p>
                </article>
            @endif
            @if ($user?->hasPermission('gestionar_disponibilidad'))
                <article class="rounded-2xl bg-[#AAE2E2] p-5 text-[#191346]">
                    <p class="text-sm opacity-80">Disponibilidad</p>
                    <p class="mt-3 text-3xl font-bold">92%</p>
                </article>
            @endif
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            @if ($user?->hasPermission('consultar_citas'))
                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-[#191346]">Agenda del médico</h3>
                            <p class="mt-1 text-sm text-slate-500">Citas asignadas para hoy</p>
                        </div>
                        <i class="fa-solid fa-calendar-check text-xl text-[#223FAA]"></i>
                    </div>
                    <div class="space-y-3">
                        @php
                            $schedule = [
                                ['time' => '08:30', 'patient' => 'Andrea López', 'type' => 'Consulta general'],
                                ['time' => '10:15', 'patient' => 'José García', 'type' => 'Control cardiaco'],
                                ['time' => '15:40', 'patient' => 'Sofía Ruiz', 'type' => 'Dolor de espalda'],
                            ];
                        @endphp

                        @foreach ($schedule as $item)
                            <div class="flex items-center justify-between rounded-xl bg-white p-3 ring-1 ring-slate-200">
                                <div>
                                    <p class="font-semibold text-[#191346]">{{ $item['patient'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $item['type'] }}</p>
                                </div>
                                <span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $item['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif

            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-[#191346]">Accesos rápidos</h3>
                        <p class="mt-1 text-sm text-slate-500">Funciones habilitadas para tu rol</p>
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
    </div>
@endsection
