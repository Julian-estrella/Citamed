@extends('layout.app')

@section('content')
    @php
        $roleLabels = [
            'administrador' => 'Administrador',
            'medico' => 'Médico',
            'recepcion' => 'Recepción',
        ];

        $totalUsers = $users->count();
        $activeUsers = $users->where('is_active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;
        $admins = $users->where('role', 'administrador')->count();
        $doctors = $users->where('role', 'medico')->count();
        $reception = $users->where('role', 'recepcion')->count();
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Panel administrativo</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Dashboard de usuarios</h2>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">
                <i class="fa-solid fa-user-plus"></i>
                Nuevo usuario
            </a>
        </header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl bg-[#223FAA] p-5 text-white shadow-lg shadow-[#223FAA]/20">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-white/80">Usuarios</span>
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
                <p class="mt-4 text-3xl font-bold">{{ $totalUsers }}</p>
            </div>

            <div class="rounded-3xl bg-[#EAF1FF] p-5 text-[#191346] shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Activos</span>
                    <i class="fa-solid fa-user-check text-lg text-[#223FAA]"></i>
                </div>
                <p class="mt-4 text-3xl font-bold">{{ $activeUsers }}</p>
            </div>

            <div class="rounded-3xl bg-[#FDECEC] p-5 text-[#191346] shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Inactivos</span>
                    <i class="fa-solid fa-user-slash text-lg text-red-500"></i>
                </div>
                <p class="mt-4 text-3xl font-bold">{{ $inactiveUsers }}</p>
            </div>

            <div class="rounded-3xl bg-[#EAFBF7] p-5 text-[#191346] shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Roles</span>
                    <i class="fa-solid fa-shield-halved text-lg text-emerald-600"></i>
                </div>
                <p class="mt-4 text-3xl font-bold">{{ $admins + $doctors + $reception }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-[#191346]">Resumen por roles</h3>
                    <i class="fa-solid fa-chart-pie text-[#223FAA]"></i>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Administradores</span>
                            <span class="text-slate-500">{{ $admins }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-[#223FAA]" style="width: {{ $totalUsers ? ($admins / $totalUsers) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Médicos</span>
                            <span class="text-slate-500">{{ $doctors }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-[#AC9FE2]" style="width: {{ $totalUsers ? ($doctors / $totalUsers) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Recepción</span>
                            <span class="text-slate-500">{{ $reception }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-[#AAE2E2]" style="width: {{ $totalUsers ? ($reception / $totalUsers) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="text-xl font-bold text-[#191346]">Acciones rápidas</h3>
                <div class="mt-5 space-y-3">
                    <a href="{{ route('admin.users.create') }}" class="flex items-center justify-between rounded-2xl bg-white p-3 text-sm font-medium text-slate-700 shadow-sm">
                        <span><i class="fa-solid fa-user-plus mr-2 text-[#223FAA]"></i>Registrar usuario</span>
                        <i class="fa-solid fa-arrow-right text-slate-400"></i>
                    </a>
                    <a href="{{ route('admin.users') }}" class="flex items-center justify-between rounded-2xl bg-white p-3 text-sm font-medium text-slate-700 shadow-sm">
                        <span><i class="fa-solid fa-table-list mr-2 text-[#223FAA]"></i>Consultar usuarios</span>
                        <i class="fa-solid fa-arrow-right text-slate-400"></i>
                    </a>
                    <a href="{{ route('admin.users.actions') }}" class="flex items-center justify-between rounded-2xl bg-white p-3 text-sm font-medium text-slate-700 shadow-sm">
                        <span><i class="fa-solid fa-wrench mr-2 text-[#223FAA]"></i>Administrar funciones</span>
                        <i class="fa-solid fa-arrow-right text-slate-400"></i>
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
