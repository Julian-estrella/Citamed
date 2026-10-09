@extends('layout.app')

@section('content')
    <header class="mb-8 border-b border-slate-200 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Panel de atención</p>
        <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $title }}</h2>
        <p class="mt-1 text-sm text-slate-500">Pacientes registrados y su estado actual.</p>
    </header>

    @if (session('success'))
        <p class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</p>
    @endif

    @if ($user->hasPermission('registrar_pacientes') || $user->hasPermission('gestionar_pacientes'))
        @php
            $isAdmin = $user->isAdmin();
            $storeRoute = $isAdmin ? 'admin.patients.store' : 'recepcion.patients.store';
        @endphp
        <form method="POST" action="{{ route($storeRoute) }}" class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            @csrf
            <h3 class="mb-4 font-bold text-[#191346]">Registrar paciente</h3>
            <div class="grid gap-3 md:grid-cols-3">
                <label class="text-sm font-medium text-slate-700">Nombre
                    <input name="name" value="{{ old('name') }}" required maxlength="255" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                    @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium text-slate-700">Correo
                    <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                    @error('email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="text-sm font-medium text-slate-700">Teléfono
                    <input name="phone" value="{{ old('phone') }}" maxlength="30" class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm">
                    @error('phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            </div>
            <button class="mt-4 rounded-xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white">Guardar paciente</button>
        </form>
    @endif

    @if ($patients->isNotEmpty())
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nombre</th>
                        <th class="px-4 py-3 font-semibold">Correo</th>
                        <th class="px-4 py-3 font-semibold">Teléfono</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        @if ($user->hasPermission('modificar_pacientes') || $user->hasPermission('gestionar_pacientes'))
                            <th class="px-4 py-3 text-right font-semibold">Acción</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($patients as $patient)
                        <tr>
                            <td class="px-4 py-3 font-medium text-[#191346]">{{ $patient->name }}</td>
                            <td class="px-4 py-3">{{ $patient->email ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $patient->phone ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full {{ $patient->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-2.5 py-1 text-xs font-semibold">{{ $patient->is_active ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            @if ($user->hasPermission('modificar_pacientes') || $user->hasPermission('gestionar_pacientes'))
                                <td class="px-4 py-3 text-right">
                                    @php $toggleRoute = $isAdmin ? 'admin.patients.toggle-status' : 'recepcion.patients.toggle-status'; @endphp
                                    <form method="POST" action="{{ route($toggleRoute, $patient) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $patient->is_active ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
            <i class="fa-solid fa-user-injured text-2xl text-slate-400"></i>
            <h3 class="mt-3 font-semibold text-slate-800">No hay pacientes para mostrar</h3>
            <p class="mt-1 text-sm text-slate-500">Los pacientes registrados aparecerán aquí.</p>
        </div>
    @endif
@endsection