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
            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 rounded-2xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">
                <i class="fa-solid fa-user-plus"></i>
                Nuevo usuario
            </a>
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

        <div class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
            <section class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#223FAA]/10 text-[#223FAA]">
                        <i class="fa-solid {{ $editingUser ? 'fa-user-pen' : 'fa-user-plus' }}"></i>
                    </div>
                    <h3 class="text-xl font-bold text-[#191346]">
                        {{ $editingUser ? 'Editar usuario' : 'Registrar usuario' }}
                    </h3>
                </div>

                <form action="{{ $editingUser ? route('admin.users.update', $editingUser) : route('admin.users.store') }}" method="POST" class="space-y-4">
                    @csrf
                    @if ($editingUser)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nombre completo</label>
                        <input type="text" name="name" value="{{ old('name', $editingUser?->name ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none ring-0 transition focus:border-[#223FAA]" required>
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $editingUser?->email ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#223FAA]" required>
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tipo de usuario</label>
                        <select name="role" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#223FAA]" required>
                            @foreach ($roleLabels as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $editingUser?->role ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Estado</label>
                        <select name="is_active" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#223FAA]">
                            <option value="1" {{ old('is_active', $editingUser?->is_active ?? true) == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('is_active', $editingUser?->is_active ?? true) == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Contraseña {{ $editingUser ? '(opcional para mantener la actual)' : '' }}</label>
                        <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#223FAA]" {{ $editingUser ? '' : 'required' }}>
                        @if ($editingUser)
                            <p class="mt-1 text-[11px] text-slate-500">Deja este campo vacío si no deseas cambiarla.</p>
                        @endif
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if (!$editingUser)
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#223FAA]" required>
                        </div>
                    @else
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#223FAA]">
                        </div>
                    @endif

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">
                            <i class="fa-solid {{ $editingUser ? 'fa-floppy-disk' : 'fa-user-plus' }}"></i>
                            {{ $editingUser ? 'Guardar cambios' : 'Crear usuario' }}
                        </button>

                        @if ($editingUser)
                            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-xmark"></i>
                                Cancelar
                            </a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-[#191346]">Usuarios registrados</h3>
                        <p class="text-sm text-slate-500">Consulta, edita y administra las cuentas</p>
                    </div>

                    <form method="GET" action="{{ route('admin.users') }}" class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar usuario..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-[#223FAA] md:w-56">
                        <button type="submit" class="rounded-2xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Nombre</th>
                                <th class="px-4 py-3 font-semibold">Correo</th>
                                <th class="px-4 py-3 font-semibold">Rol</th>
                                <th class="px-4 py-3 font-semibold">Estado</th>
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
                                        <span class="rounded-full bg-[#EAF1FF] px-2.5 py-1 text-xs font-semibold text-[#223FAA]">
                                            {{ $roleLabels[$userItem->role] ?? ucfirst($userItem->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full {{ $userItem->isActive() ? 'bg-[#EAFBF7] text-[#0f766e]' : 'bg-[#FDECEC] text-[#b42318]' }} px-2.5 py-1 text-xs font-semibold">
                                            {{ $userItem->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $userItem) }}" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-[#223FAA]">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                Editar
                                            </a>

                                            <form action="{{ route('admin.users.toggle-status', $userItem) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700">
                                                    <i class="fa-solid {{ $userItem->isActive() ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                    {{ $userItem->isActive() ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>

                                            @if (auth()->id() !== $userItem->id)
                                                <form action="{{ route('admin.users.destroy', $userItem) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este usuario?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-xl border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-600">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Eliminar
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
    </div>
@endsection
