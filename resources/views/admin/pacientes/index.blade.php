@extends('layout.app')

@section('content')
    @php
        $isAdmin = $user->isAdmin();
        $canRegisterPatients = $user->hasPermission('registrar_pacientes') || $user->hasPermission('gestionar_pacientes');
        $listRoute = $isAdmin ? 'admin.patients' : ($user->isMedico() ? 'medico.patients' : 'recepcion.patients');
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
                <form id="patient-filters" method="GET" action="{{ route($listRoute) }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar paciente..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#223FAA] md:w-64">
                    <button type="submit" aria-label="Buscar pacientes" class="rounded-2xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <a href="{{ route($listRoute) }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600">Limpiar</a>
                </form>
            </div>

            <div class="overflow-x-auto rounded-b-3xl">
                <table class="w-full min-w-[900px] divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Paciente</th>
                            <th class="px-4 py-3 font-semibold">Edad / nacimiento</th>
                            <th class="px-4 py-3 font-semibold">Sexo / género</th>
                            <th class="px-4 py-3 font-semibold">Contacto</th>
                            <th class="px-4 py-3 font-semibold">Tipo de sangre</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                        </tr>
                        <tr class="border-t border-slate-200 bg-white">
                            <th class="px-2 py-2">
                                <div class="flex min-w-32 flex-col gap-1">
                                    <input form="patient-filters" type="search" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Filtrar nombre" aria-label="Filtrar por nombre" class="w-full rounded-lg border-slate-200 text-xs">
                                    <input form="patient-filters" type="search" name="address" value="{{ $filters['address'] ?? '' }}" placeholder="Filtrar domicilio" aria-label="Filtrar por domicilio" class="w-full rounded-lg border-slate-200 text-xs">
                                </div>
                            </th>
                            <th class="px-2 py-2"><input form="patient-filters" type="date" name="birth_date" value="{{ $filters['birth_date'] ?? '' }}" aria-label="Filtrar por fecha de nacimiento" class="w-full min-w-36 rounded-lg border-slate-200 text-xs"></th>
                            <th class="px-2 py-2">
                                <select form="patient-filters" name="gender" aria-label="Filtrar por género" class="w-full min-w-36 rounded-lg border-slate-200 text-xs">
                                    <option value="">Todos</option>
                                    @foreach (['Femenino', 'Masculino', 'Otro', 'Prefiero no decirlo'] as $gender)
                                        <option value="{{ $gender }}" @selected(($filters['gender'] ?? '') === $gender)>{{ $gender }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-2 py-2">
                                <div class="flex min-w-48 flex-col gap-1">
                                    <input form="patient-filters" type="search" name="phone" value="{{ $filters['phone'] ?? '' }}" placeholder="Teléfono" aria-label="Filtrar por teléfono" class="w-full rounded-lg border-slate-200 text-xs">
                                    <input form="patient-filters" type="search" name="email" value="{{ $filters['email'] ?? '' }}" placeholder="Correo" aria-label="Filtrar por correo" class="w-full rounded-lg border-slate-200 text-xs">
                                </div>
                            </th>
                            <th class="px-2 py-2">
                                <select form="patient-filters" name="blood_type" aria-label="Filtrar por tipo de sangre" class="w-full min-w-28 rounded-lg border-slate-200 text-xs">
                                    <option value="">Todos</option>
                                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                                        <option value="{{ $bloodType }}" @selected(($filters['blood_type'] ?? '') === $bloodType)>{{ $bloodType }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="px-2 py-2">
                                <select form="patient-filters" name="status" aria-label="Filtrar por estado" class="w-full min-w-24 rounded-lg border-slate-200 text-xs">
                                    <option value="">Todos</option>
                                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activo</option>
                                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactivo</option>
                                </select>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($patients as $patient)
                            <tr>
                                <td class="px-4 py-3 font-medium text-[#191346]">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#EAF1FF] text-xs font-bold text-[#223FAA]">{{ strtoupper(substr($patient->name, 0, 1)) }}</span>
                                        <div>
                                            <p>{{ $patient->name }}</p>
                                            <p class="mt-0.5 max-w-56 truncate text-xs font-normal text-slate-500" title="{{ $patient->address }}">{{ $patient->address ?: 'Domicilio sin registrar' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ $patient->age !== null ? $patient->age.' años' : '—' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $patient->birth_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $patient->gender ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <p>{{ $patient->phone ?: 'Sin teléfono' }}</p>
                                    <p class="mt-0.5 max-w-48 truncate text-xs text-slate-500" title="{{ $patient->email }}">{{ $patient->email ?: 'Sin correo' }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $patient->blood_type ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full {{ $patient->is_active ? 'bg-[#EAFBF7] text-[#0f766e]' : 'bg-[#FDECEC] text-[#b42318]' }} px-2.5 py-1 text-xs font-semibold">{{ $patient->is_active ? 'Activo' : 'Inactivo' }}</span>
                                </td>
                                @include('admin.pacientes.actions', [
                                    'patient' => $patient,
                                    'user' => $user,
                                    'toggleRoute' => $toggleRoute,
                                    'canTogglePatients' => $canTogglePatients,
                                    'canDeletePatients' => $canDeletePatients,
                                ])
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">No se encontraron pacientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection