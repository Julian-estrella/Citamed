@extends('layout.app')

@section('content')
    @php
        $roleLabels = [
            'administrador' => 'Administrador',
            'medico' => 'Médico',
            'recepcion' => 'Recepción',
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Usuarios</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Editar usuario</h2>
            </div>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
        </header>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nombre completo</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Tipo de usuario</label>
                    <select name="role" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                        @foreach ($roleLabels as $value => $label)
                            <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Estado</label>
                    <select name="is_active" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                        <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nueva contraseña</label>
                    <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                    <p class="mt-1 text-[11px] text-slate-500">Déjalo vacío para conservar la contraseña actual.</p>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
