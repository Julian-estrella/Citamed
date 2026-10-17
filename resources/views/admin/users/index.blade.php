@extends('layout.app')

@section('content')
    @php
        $roleLabels = [
            'administrador' => 'Administrador',
            'medico' => 'Médico',
            'recepcion' => 'Recepción',
        ];
    @endphp

    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Administración</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Gestión de usuarios</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">
                    <i class="fa-solid fa-user-plus"></i>
                    Nuevo usuario
                </a>
            </div>
        </header>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-slate-200 p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-xl font-bold text-[#191346]">Usuarios registrados</h3>
                    <p class="text-sm text-slate-500">Consultar, buscar, editar y gestionar acceso</p>
                </div>

                <form id="user-filters" method="GET" action="{{ route('admin.users') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar usuario..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#223FAA] md:w-64">
                    <button type="submit" class="rounded-2xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if (($search ?? '') !== '')
                        <a href="{{ route('admin.users', request()->except('search')) }}" aria-label="Quitar búsqueda de usuarios" title="Quitar búsqueda" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">
                                @php
                                    $nextDirection = ($filters['direction'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('admin.users', array_merge(request()->query(), ['sort' => 'name', 'direction' => $nextDirection])) }}" class="inline-flex items-center gap-2">
                                    Nombre
                                    <i class="fa-solid {{ ($filters['direction'] ?? 'asc') === 'asc' ? 'fa-arrow-up-a-z' : 'fa-arrow-down-a-z' }}" aria-hidden="true"></i>
                                    <span class="sr-only">Ordenar {{ $nextDirection === 'asc' ? 'ascendente' : 'descendente' }}</span>
                                </a>
                            </th>
                            <th class="px-4 py-3 font-semibold">Correo</th>
                            <th class="px-4 py-3 font-semibold">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Rol</span>
                                    <details class="relative">
                                        <summary aria-label="Filtrar por rol" class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-[#223FAA] [&::-webkit-details-marker]:hidden">
                                            <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                                        </summary>
                                        <div class="absolute right-0 z-20 mt-1 min-w-40 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                            @foreach (['' => 'Todos los roles', 'administrador' => 'Administrador', 'medico' => 'Médico', 'recepcion' => 'Recepción'] as $roleValue => $roleLabel)
                                                @php($roleSelected = ($filters['role'] ?? '') === $roleValue && $roleValue !== '')
                                                <a href="{{ route('admin.users', array_merge(request()->query(), ['role' => $roleSelected ? '' : $roleValue])) }}" class="block px-3 py-2 text-xs {{ $roleSelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">
                                                    {{ $roleLabel }}{{ $roleSelected ? ' ✓' : '' }}
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
                                                <a href="{{ route('admin.users', array_merge(request()->query(), ['status' => $statusSelected ? '' : $statusValue])) }}" class="block px-3 py-2 text-xs {{ $statusSelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">
                                                    {{ $statusLabel }}{{ $statusSelected ? ' ✓' : '' }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($users as $userItem)
                            <tr>
                                <td class="px-4 py-3 font-medium text-[#191346]">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#EAF1FF] text-xs font-bold text-[#223FAA]">
                                            {{ strtoupper(substr($userItem->name, 0, 1)) }}
                                        </span>
                                        {{ $userItem->name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $userItem->email }}</td>
                                <td class="px-4 py-3">
                                    {{ collect($userItem->assignedRoles())->map(fn ($role) => $roleLabels[$role] ?? ucfirst($role))->join(', ') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full {{ $userItem->isActive() ? 'bg-[#EAFBF7] text-[#0f766e]' : 'bg-[#FDECEC] text-[#b42318]' }} px-2.5 py-1 text-xs font-semibold">
                                        {{ $userItem->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $userItem) }}" title="Editar usuario" aria-label="Editar usuario {{ $userItem->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-[#223FAA]">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        @if ($userItem->isSuperAdmin() && $userItem->isActive())
                                            <button type="button" disabled title="No puedes desactivar al super administrador" aria-label="No puedes desactivar al super administrador" class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-400">
                                                <i class="fa-solid fa-toggle-on"></i>
                                            </button>
                                        @else
                                            <form action="{{ route('admin.users.toggle-status', $userItem) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="{{ $userItem->isActive() ? 'Desactivar usuario' : 'Activar usuario' }}" aria-label="{{ $userItem->isActive() ? 'Desactivar' : 'Activar' }} usuario {{ $userItem->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700">
                                                    <i class="fa-solid {{ $userItem->isActive() ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if (!$userItem->isSuperAdmin() && auth()->id() !== $userItem->id)
                                            <form action="{{ route('admin.users.destroy', $userItem) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este usuario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar usuario" aria-label="Eliminar usuario {{ $userItem->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No se encontraron usuarios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
