@extends('layout.app')

@section('content')
    @php
        $doctorsRoute = $user->isAdmin() ? 'admin.doctors' : 'recepcion.doctors';
        $toggleRoute = $user->isAdmin() ? 'admin.doctors.toggle-status' : 'recepcion.doctors.toggle-status';
        $deleteRoute = $user->isAdmin() ? 'admin.doctors.destroy' : 'recepcion.doctors.destroy';
        $sortColumn = $filters['sort'] ?? 'name';
        $sortDirection = $filters['direction'] ?? 'asc';
    @endphp

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <header class="mb-8 flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Directorio</p>
            <h2 class="mt-2 text-3xl font-bold text-[#191346]">Médicos</h2>
            <p class="mt-1 text-sm text-slate-500">Directorio y datos profesionales registrados.</p>
        </div>
        @if ($user->hasPermission('gestionar_medicos'))
            <a href="{{ route($createRoute) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white">
                <i class="fa-solid fa-user-plus"></i> Nuevo médico
            </a>
        @endif
    </header>

    <section class="rounded-3xl border border-slate-200 bg-white">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-[#191346]">Médicos registrados</h3>
                        <p class="text-sm text-slate-500">Consulta médicos por nombre, correo, especialidad, consultorio o estado.</p>
                    </div>
                    <form id="doctor-filters" method="GET" action="{{ route($doctorsRoute) }}" class="flex w-full items-center gap-2 md:w-auto">
                        <input type="search" name="search" value="{{ $search }}" placeholder="Buscar médico por nombre o correo..." aria-label="Buscar médico por nombre o correo" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#223FAA] md:w-72">
                        @foreach (['sort', 'direction', 'specialty', 'office', 'status'] as $queryKey)
                            @if (request()->filled($queryKey))
                                <input type="hidden" name="{{ $queryKey }}" value="{{ request()->query($queryKey) }}">
                            @endif
                        @endforeach
                        <button type="submit" aria-label="Buscar médicos" title="Buscar" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                        @if ($search !== '')
                            <a href="{{ route($doctorsRoute, request()->except('search')) }}" aria-label="Quitar filtro de búsqueda" title="Quitar filtro" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto rounded-b-3xl">
                    <table class="w-full table-fixed divide-y divide-slate-200 text-left text-xs text-slate-700">
                        <colgroup>
                            <col class="w-[15%]">
                            <col class="w-[19%]">
                            <col class="w-[14%]">
                            <col class="w-[11%]">
                            <col class="w-[20%]">
                            <col class="w-[9%]">
                            @if ($user->hasPermission('gestionar_medicos'))<col class="w-[12%]">@endif
                        </colgroup>
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-2 py-3 font-semibold sm:px-3">
                                    @php($nextNameDirection = $sortColumn === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc')
                                    <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['sort' => 'name', 'direction' => $nextNameDirection])) }}" class="inline-flex items-center gap-2">
                                        Médico <i class="fa-solid {{ $sortColumn === 'name' ? ($sortDirection === 'asc' ? 'fa-arrow-up-a-z' : 'fa-arrow-down-a-z') : 'fa-arrow-up-a-z' }}" aria-hidden="true"></i>
                                        <span class="sr-only">Ordenar médico {{ $nextNameDirection === 'asc' ? 'ascendente' : 'descendente' }}</span>
                                    </a>
                                </th>
                                <th class="px-2 py-3 font-semibold sm:px-3">
                                    @php($nextEmailDirection = $sortColumn === 'email' && $sortDirection === 'asc' ? 'desc' : 'asc')
                                    <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['sort' => 'email', 'direction' => $nextEmailDirection])) }}" class="inline-flex items-center gap-2">
                                        Contacto <i class="fa-solid {{ $sortColumn === 'email' ? ($sortDirection === 'asc' ? 'fa-arrow-up-a-z' : 'fa-arrow-down-a-z') : 'fa-arrow-up-a-z' }}" aria-hidden="true"></i>
                                        <span class="sr-only">Ordenar correo {{ $nextEmailDirection === 'asc' ? 'ascendente' : 'descendente' }}</span>
                                    </a>
                                </th>
                                <th class="px-2 py-3 font-semibold sm:px-3">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="truncate">Especialidad</span>
                                        <details class="relative">
                                            <summary aria-label="Filtrar por especialidad" class="flex h-6 w-6 cursor-pointer list-none items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-[#223FAA] [&::-webkit-details-marker]:hidden"><i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i></summary>
                                            <div class="absolute right-0 z-20 mt-1 max-h-64 min-w-48 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                                @php($specialtySelected = ($filters['specialty'] ?? '') === '')
                                                <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['specialty' => ''])) }}" class="block px-3 py-2 text-xs {{ $specialtySelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">Todas{{ $specialtySelected ? ' ✓' : '' }}</a>
                                                <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['specialty' => '__pending'])) }}" class="block px-3 py-2 text-xs {{ ($filters['specialty'] ?? '') === '__pending' ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">Pendiente</a>
                                                @foreach ($specialties as $specialty)
                                                    @php($specialtySelected = ($filters['specialty'] ?? '') === $specialty)
                                                    <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['specialty' => $specialtySelected ? '' : $specialty])) }}" class="block px-3 py-2 text-xs {{ $specialtySelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">{{ $specialty }}{{ $specialtySelected ? ' ✓' : '' }}</a>
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                </th>
                                <th class="px-2 py-3 font-semibold sm:px-3">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="truncate">Consultorio</span>
                                        <details class="relative">
                                            <summary aria-label="Filtrar por consultorio" class="flex h-6 w-6 cursor-pointer list-none items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-[#223FAA] [&::-webkit-details-marker]:hidden"><i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i></summary>
                                            <div class="absolute right-0 z-20 mt-1 min-w-40 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                                @php($officeSelected = ($filters['office'] ?? '') === '')
                                                <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['office' => ''])) }}" class="block px-3 py-2 text-xs {{ $officeSelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">Todos{{ $officeSelected ? ' ✓' : '' }}</a>
                                                <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['office' => '__pending'])) }}" class="block px-3 py-2 text-xs {{ ($filters['office'] ?? '') === '__pending' ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">Pendiente</a>
                                                @foreach ($offices as $office)
                                                    @php($officeSelected = ($filters['office'] ?? '') === $office)
                                                    <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['office' => $officeSelected ? '' : $office])) }}" class="block px-3 py-2 text-xs {{ $officeSelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">{{ $office }}{{ $officeSelected ? ' ✓' : '' }}</a>
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                </th>
                                <th class="px-2 py-3 font-semibold sm:px-3">Turnos</th>
                                <th class="px-2 py-3 font-semibold sm:px-3">
                                    <div class="flex items-center justify-between gap-1">
                                        <span>Estado</span>
                                        <details class="relative">
                                            <summary aria-label="Filtrar por estado" class="flex h-6 w-6 cursor-pointer list-none items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-[#223FAA] [&::-webkit-details-marker]:hidden"><i class="fa-solid fa-chevron-down text-[10px]" aria-hidden="true"></i></summary>
                                            <div class="absolute right-0 z-20 mt-1 min-w-32 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                                @foreach (['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Inactivo'] as $statusValue => $statusLabel)
                                                    @php($statusSelected = ($filters['status'] ?? '') === $statusValue)
                                                    <a href="{{ route($doctorsRoute, array_merge(request()->query(), ['status' => $statusValue])) }}" class="block px-3 py-2 text-xs {{ $statusSelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">{{ $statusLabel }}{{ $statusSelected ? ' ✓' : '' }}</a>
                                                @endforeach
                                            </div>
                                        </details>
                                    </div>
                                </th>
                                @if ($user->hasPermission('gestionar_medicos'))<th class="px-2 py-3 text-right font-semibold sm:px-3">Acciones</th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($doctors as $doctor)
                                <tr>
                                    <td class="break-words px-2 py-3 font-medium text-slate-800 sm:px-3" title="{{ $doctor->doctorProfile?->professional_title }} {{ $doctor->name }}">{{ $doctor->doctorProfile?->professional_title }} {{ $doctor->name }}</td>
                                    <td class="break-all px-2 py-3 text-slate-600 sm:px-3">
                                        <p title="{{ $doctor->email }}">{{ $doctor->email }}</p>
                                        <p class="mt-1 text-[10px] text-slate-500">{{ $doctor->doctorProfile?->phone ?? 'Teléfono pendiente' }}</p>
                                    </td>
                                    <td class="break-words px-2 py-3 text-slate-600 sm:px-3">{{ $doctor->doctorProfile?->specialty ?? 'Pendiente' }}</td>
                                    <td class="break-words px-2 py-3 text-slate-600 sm:px-3">{{ $doctor->doctorProfile?->office ?? 'Pendiente' }}</td>
                                    <td class="px-2 py-3 text-[10px] text-slate-600 sm:px-3">
                                        @foreach ($doctor->doctorProfile?->shifts ?? [] as $shift)
                                            @php($sessionKey = $workShifts['matutino']['start'] === substr((string) $shift->start_time, 0, 5) ? 'matutino' : 'vespertino')
                                            <span title="{{ $daysOfWeek[$shift->day_of_week] ?? '' }} · {{ $workShifts[$sessionKey]['label'] }} {{ $workShifts[$sessionKey]['start'] }}–{{ $workShifts[$sessionKey]['end'] }}" class="mb-1 mr-1 inline-block rounded bg-slate-100 px-1.5 py-1">{{ substr($daysOfWeek[$shift->day_of_week] ?? '', 0, 3) }} · {{ $sessionKey === 'matutino' ? 'Mat' : 'Vesp' }} {{ substr($workShifts[$sessionKey]['start'], 0, 5) }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-2 py-3 sm:px-3"><span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $doctor->isActive() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $doctor->isActive() ? 'Activo' : 'Inactivo' }}</span></td>
                                    @if ($user->hasPermission('gestionar_medicos'))
                                        <td class="px-2 py-3 sm:px-3">
                                            <div class="flex flex-wrap items-center justify-end gap-1">
                                                <a href="{{ route($user->isAdmin() ? 'admin.doctors.edit' : 'recepcion.doctors.edit', $doctor) }}" title="Editar médico" aria-label="Editar médico {{ $doctor->name }}" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-[#223FAA]"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                                                @if (! $doctor->isSuperAdmin() && (auth()->id() !== $doctor->id || ! $doctor->isActive()))
                                                    <form method="POST" action="{{ route($toggleRoute, $doctor) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" title="{{ $doctor->isActive() ? 'Desactivar' : 'Activar' }} cuenta" aria-label="{{ $doctor->isActive() ? 'Desactivar' : 'Activar' }} cuenta de {{ $doctor->name }}" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700"><i class="fa-solid {{ $doctor->isActive() ? 'fa-toggle-on' : 'fa-toggle-off' }}" aria-hidden="true"></i></button>
                                                    </form>
                                                @endif
                                                @if (! $doctor->isSuperAdmin() && auth()->id() !== $doctor->id)
                                                    <form method="POST" action="{{ route($deleteRoute, $doctor) }}" onsubmit="return confirm('¿Eliminar la cuenta de {{ $doctor->name }}? Las citas existentes se conservarán sin médico asignado.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="Eliminar médico" aria-label="Eliminar médico {{ $doctor->name }}" class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $user->hasPermission('gestionar_medicos') ? 7 : 6 }}" class="px-5 py-8 text-center text-slate-500">No se encontraron médicos con esos filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
@endsection