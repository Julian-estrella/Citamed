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
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Crear usuario</h2>
            </div>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
        </header>

        <form action="{{ route('admin.users.store') }}" method="POST" class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nombre completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('name')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input type="email" name="email" maxlength="50" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('email')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Tipo de usuario</label>
                    <select name="role" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                        <option value="">Seleccione un rol</option>
                        @foreach ($roleLabels as $value => $label)
                            <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Estado</label>
                    <select name="is_active" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('is_active')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
                    <input type="password" name="password" maxlength="8" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('password')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" maxlength="8" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('password_confirmation')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">
                    <i class="fa-solid fa-user-plus"></i>
                    Guardar usuario
                </button>
            </div>
        </form>
    </div>
@endsection
