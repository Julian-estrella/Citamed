@extends('layout.app')

@section('content')
    @php
        $isAdmin = $user->isAdmin();
        $canRegisterPatients = $user->hasPermission('registrar_pacientes') || $user->hasPermission('gestionar_pacientes');
        $listRoute = $isAdmin ? 'admin.patients' : ($user->isMedico() ? 'medico.patients' : 'recepcion.patients');
        $showRoute = $isAdmin ? 'admin.patients.show' : ($user->isMedico() ? 'medico.patients.show' : 'recepcion.patients.show');
        $createRoute = $isAdmin ? 'admin.patients.create' : 'recepcion.patients.create';
        $toggleRoute = $isAdmin ? 'admin.patients.toggle-status' : 'recepcion.patients.toggle-status';
        $canTogglePatients = $user->hasPermission('modificar_pacientes') || $user->hasPermission('gestionar_pacientes');
        $canDeletePatients = $user->hasPermission('gestionar_pacientes');
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Panel de atención</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $title }}</h2>
            </div>
            @if ($canRegisterPatients)
                <a href="{{ route($createRoute) }}" class="inline-flex items-center gap-2 self-start rounded-2xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20 md:self-auto">
                    <i class="fa-solid fa-user-plus"></i>
                    Nuevo paciente
                </a>
            @endif
        </header>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-slate-200 p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-xl font-bold text-[#191346]">Pacientes registrados</h3>
                    <p class="text-sm text-slate-500">Consulta la información de contacto y médica</p>
                </div>
                <form id="patient-filters" method="GET" action="{{ route($listRoute) }}" class="flex w-full items-center gap-2 md:w-auto">
                    <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Buscar paciente por nombre..." aria-label="Buscar paciente por nombre" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#223FAA] md:w-64">
                    @foreach (['blood_type', 'status', 'sort', 'direction'] as $queryKey)
                        @if (request()->filled($queryKey))
                            <input type="hidden" name="{{ $queryKey }}" value="{{ request()->query($queryKey) }}">
                        @endif
                    @endforeach
                    <button type="submit" aria-label="Buscar" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto rounded-b-3xl">
                <table class="w-full min-w-[760px] divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">
                                @php
                                    $nextDirection = ($filters['direction'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route($listRoute, array_merge(request()->query(), ['sort' => 'name', 'direction' => $nextDirection])) }}" class="inline-flex items-center gap-2">
                                    Paciente
                                    <i class="fa-solid {{ ($filters['direction'] ?? 'asc') === 'asc' ? 'fa-arrow-up-a-z' : 'fa-arrow-down-a-z' }}" aria-hidden="true"></i>
                                    <span class="sr-only">Ordenar alfabéticamente {{ $nextDirection === 'asc' ? 'ascendente' : 'descendente' }}</span>
                                </a>
                            </th>
                            <th class="px-4 py-3 font-semibold">Contacto</th>
                            <th class="px-4 py-3 font-semibold">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Tipo de sangre</span>
                                    <details class="relative">
                                        <summary aria-label="Filtrar por tipo de sangre" class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-[#223FAA] [&::-webkit-details-marker]:hidden">
                                            <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                                        </summary>
                                        <div class="absolute right-0 z-20 mt-1 min-w-32 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                                                @php($bloodTypeSelected = ($filters['blood_type'] ?? '') === $bloodType)
                                                <a href="{{ route($listRoute, array_merge(request()->query(), ['blood_type' => $bloodTypeSelected ? '' : $bloodType])) }}" class="block px-3 py-2 text-xs {{ $bloodTypeSelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">
                                                    {{ $bloodType }}{{ $bloodTypeSelected ? ' ✓' : '' }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-semibold">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Estado</span>
                                    <details class="relative">
                                        <summary aria-label="Filtrar por estado" class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-[#223FAA] [&::-webkit-details-marker]:hidden">
                                            <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                                        </summary>
                                        <div class="absolute right-0 z-20 mt-1 min-w-32 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                            @foreach (['active' => 'Activo', 'inactive' => 'Inactivo'] as $statusValue => $statusLabel)
                                                @php($statusSelected = ($filters['status'] ?? '') === $statusValue)
                                                <a href="{{ route($listRoute, array_merge(request()->query(), ['status' => $statusSelected ? '' : $statusValue])) }}" class="block px-3 py-2 text-xs {{ $statusSelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">
                                                    {{ $statusLabel }}{{ $statusSelected ? ' ✓' : '' }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($patients as $patient)
                            <tr>
                                <td class="px-4 py-3 font-medium text-[#191346]">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#EAF1FF] text-xs font-bold text-[#223FAA]">{{ strtoupper(substr($patient->name, 0, 1)) }}</span>
                                        <a href="{{ route($showRoute, $patient) }}" class="truncate hover:text-[#223FAA] hover:underline">{{ $patient->name }}</a>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ $patient->phone ?: 'Sin teléfono' }}</p>
                                    <p class="mt-0.5 max-w-48 truncate text-xs text-slate-500" title="{{ $patient->email }}">{{ $patient->email ?: 'Sin correo' }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $patient->blood_type ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full {{ $patient->is_active ? 'bg-[#EAFBF7] text-[#0f766e]' : 'bg-[#FDECEC] text-[#b42318]' }} px-2.5 py-1 text-xs font-semibold">{{ $patient->is_active ? 'Activo' : 'Inactivo' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @include('admin.pacientes.actions', [
                                        'patient' => $patient,
                                        'user' => $user,
                                        'toggleRoute' => $toggleRoute,
                                        'canTogglePatients' => $canTogglePatients,
                                        'canDeletePatients' => $canDeletePatients,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No se encontraron pacientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection