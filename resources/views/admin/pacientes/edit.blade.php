@extends('layout.app')

@section('content')
    @php
        $isAdmin = $user->isAdmin();
        $updateRoute = $isAdmin ? 'admin.patients.update' : 'recepcion.patients.update';
        $listRoute = $isAdmin ? 'admin.patients' : 'recepcion.patients';
    @endphp

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Pacientes</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Editar paciente</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $patient->name }}</p>
            </div>
            <a href="{{ route($listRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
        </header>

        <form action="{{ route($updateRoute, $patient) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')

            <h3 class="mb-4 font-bold text-[#191346]">Datos personales</h3>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre(s) y apellidos completos</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $patient->name) }}" maxlength="255" class="w-full rounded-xl border-slate-200 text-sm" required>
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="birth_date" class="mb-1 block text-sm font-medium text-slate-700">Fecha de nacimiento</label>
                    <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', $patient->birth_date?->toDateString()) }}" max="{{ today()->toDateString() }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                    @error('birth_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="gender" class="mb-1 block text-sm font-medium text-slate-700">Sexo / género</label>
                    <select id="gender" name="gender" class="w-full rounded-xl border-slate-200 text-sm" required>
                        <option value="">Seleccione una opción</option>
                        @foreach (['Femenino', 'Masculino', 'Otro', 'Prefiero no decirlo'] as $gender)
                            <option value="{{ $gender }}" @selected(old('gender', $patient->gender) === $gender)>{{ $gender }}</option>
                        @endforeach
                    </select>
                    @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Teléfono</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $patient->phone) }}" maxlength="30" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $patient->email) }}" maxlength="255" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="address" class="mb-1 block text-sm font-medium text-slate-700">Dirección / domicilio</label>
                    <textarea id="address" name="address" rows="2" maxlength="2000" class="w-full rounded-xl border-slate-200 text-sm">{{ old('address', $patient->address) }}</textarea>
                    @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h3 class="mb-4 mt-7 border-t border-slate-200 pt-6 font-bold text-[#191346]">Contacto de emergencia</h3>
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label for="emergency_contact_name" class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
                    <input id="emergency_contact_name" type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}" maxlength="255" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('emergency_contact_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="emergency_contact_relationship" class="mb-1 block text-sm font-medium text-slate-700">Parentesco</label>
                    <input id="emergency_contact_relationship" type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $patient->emergency_contact_relationship) }}" maxlength="100" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('emergency_contact_relationship')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="emergency_contact_phone" class="mb-1 block text-sm font-medium text-slate-700">Teléfono</label>
                    <input id="emergency_contact_phone" type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" maxlength="30" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('emergency_contact_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h3 class="mb-4 mt-7 border-t border-slate-200 pt-6 font-bold text-[#191346]">Información médica</h3>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="blood_type" class="mb-1 block text-sm font-medium text-slate-700">Tipo de sangre</label>
                    <select id="blood_type" name="blood_type" class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">Sin especificar</option>
                        @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                            <option value="{{ $bloodType }}" @selected(old('blood_type', $patient->blood_type) === $bloodType)>{{ $bloodType }}</option>
                        @endforeach
                    </select>
                    @error('blood_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="allergies_conditions" class="mb-1 block text-sm font-medium text-slate-700">Alergias o padecimientos relevantes</label>
                    <textarea id="allergies_conditions" name="allergies_conditions" rows="2" maxlength="5000" class="w-full rounded-xl border-slate-200 text-sm">{{ old('allergies_conditions', $patient->allergies_conditions) }}</textarea>
                    @error('allergies_conditions')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-7 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route($listRoute) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection