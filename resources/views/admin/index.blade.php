@extends('layout.app')

@section('content')
    @php
        $user = Auth::user();
        $userName = $user?->name ?? 'Administrador';
        $roleLabel = 'Administrador';
        $quickActions = [
            ['permission' => 'gestionar_pacientes', 'label' => 'Consultar pacientes', 'route' => route('admin.patients'), 'icon' => 'fa-solid fa-user-injured'],
            ['permission' => 'gestionar_citas', 'label' => 'Agendar cita', 'route' => route('admin.appointments'), 'icon' => 'fa-solid fa-calendar-plus'],
            ['permission' => 'gestionar_medicos', 'label' => 'Ver médicos', 'route' => route('admin.doctors'), 'icon' => 'fa-solid fa-user-doctor'],
        ];
        $availableActions = collect($quickActions)
            ->filter(fn (array $action) => $user?->hasPermission($action['permission']) ?? false);
        $upcomingAppointments = $todayAppointments->take(4);
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Panel administrativo</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Bienvenido, {{ $userName }}</h2>
            </div>
            <x-user-profile-menu :user="$user" :user-name="$userName" :role-label="$roleLabel" />
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('admin.patients') }}" class="rounded-[22px] bg-[#223FAA] p-5 text-white shadow-lg shadow-[#223FAA]/20">
                <p class="text-sm text-white/80">Pacientes activos</p>
                <p class="mt-3 text-3xl font-bold">{{ number_format($patientCount) }}</p>
            </a>
            <a href="{{ route('admin.appointments') }}" class="rounded-[22px] bg-[#AC9FE2] p-5 text-[#191346] shadow-lg shadow-[#AC9FE2]/20">
                <p class="text-sm opacity-80">Citas hoy</p>
                <p class="mt-3 text-3xl font-bold">{{ number_format($todayAppointmentCount) }}</p>
            </a>
            <a href="{{ route('admin.doctors') }}" class="rounded-[22px] bg-[#AAE2E2] p-5 text-[#191346] shadow-lg shadow-[#AAE2E2]/20">
                <p class="text-sm opacity-80">Médicos activos</p>
                <p class="mt-3 text-3xl font-bold">{{ number_format($doctorCount) }}</p>
            </a>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <article class="rounded-[26px] border border-slate-200 bg-slate-50 p-5">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h3 class="text-2xl font-bold text-[#191346]">Agenda del día</h3>
                    <a href="{{ route('admin.agenda') }}" class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-medium text-white">Ver agenda</a>
                </div>
                <div class="space-y-3">
                    @forelse ($todayAppointments as $appointment)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-[#191346]">{{ $appointment->patient->name }}</p>
                                <p class="truncate text-sm text-slate-500">{{ $appointment->doctor->name }} · {{ $appointment->reason ?: 'Consulta' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $appointment->scheduled_at->format('H:i') }}</span>
                        </div>
                    @empty
                        <p class="rounded-xl bg-white p-5 text-sm text-slate-500">No hay citas activas para hoy.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[26px] bg-[#191346] p-5 text-white shadow-xl shadow-slate-300/20">
                <h3 class="text-2xl font-bold">Resumen rápido</h3>
                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Citas programadas hoy</p>
                        <p class="mt-2 text-3xl font-bold">{{ number_format($scheduledAppointmentCount) }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Citas confirmadas hoy</p>
                        <p class="mt-2 text-3xl font-bold">{{ number_format($confirmedAppointmentCount) }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-3">
                        <p class="text-sm text-slate-300">Citas canceladas hoy</p>
                        <p class="mt-2 text-3xl font-bold">{{ number_format($cancelledAppointmentCount) }}</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <article class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h3 class="text-2xl font-bold text-[#191346]">Próximas citas</h3>
                    <a href="{{ route('admin.appointments') }}" class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-medium text-white">Ver más</a>
                </div>
                <div class="space-y-3">
                    @forelse ($upcomingAppointments as $appointment)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-[#191346]">{{ $appointment->patient->name }}</p>
                                <p class="truncate text-sm text-slate-500">{{ $appointment->doctor->name }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $appointment->scheduled_at->format('H:i') }}</span>
                        </div>
                    @empty
                        <p class="rounded-xl bg-slate-50 p-5 text-sm text-slate-500">No hay próximas citas para hoy.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-[#191346]">Accesos rápidos</h3>
                    <i class="fa-solid fa-bolt text-[#223FAA]"></i>
                </div>
                <div class="space-y-3">
                    @forelse ($availableActions as $action)
                        <a href="{{ $action['route'] }}" class="flex items-center justify-between rounded-2xl bg-slate-50 p-3 text-sm font-medium text-slate-700 transition hover:bg-[#eef3ff]">
                            <span><i class="{{ $action['icon'] }} mr-2 text-[#223FAA]"></i>{{ $action['label'] }}</span>
                            <i class="fa-solid fa-arrow-right text-slate-400"></i>
                        </a>
                    @empty
                        <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No tienes accesos rápidos asignados.</p>
                    @endforelse
                </div>
            </article>
        </section>
        </div>
@endsection
