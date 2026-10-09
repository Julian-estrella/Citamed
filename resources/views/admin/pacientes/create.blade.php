@extends('layout.app')

@section('content')
    @php
        $storeRoute = $user->isAdmin() ? 'admin.patients.store' : 'recepcion.patients.store';
        $listRoute = $user->isAdmin() ? 'admin.patients' : 'recepcion.patients';
    @endphp

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Pacientes</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Registrar paciente</h2>
            </div>
            <a href="{{ route($listRoute) }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
        </header>

        <form action="{{ route($storeRoute) }}" method="POST" class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
            @csrf

            <h3 class="mb-4 font-bold text-[#191346]">Datos personales</h3>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre(s) y apellidos completos</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="patient-birth-date" class="mb-1 block text-sm font-medium text-slate-700">Fecha de nacimiento</label>
                    <input id="patient-birth-date" type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ today()->toDateString() }}" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                    @error('birth_date')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <span class="mb-1 block text-sm font-medium text-slate-700">Edad</span>
                    <p id="patient-age" aria-live="polite" class="flex min-h-[42px] items-center rounded-2xl border border-slate-200 bg-slate-100 px-3 text-sm text-slate-600">Se calcula con la fecha de nacimiento</p>
                </div>
                <div>
                    <label for="gender" class="mb-1 block text-sm font-medium text-slate-700">Sexo / Género</label>
                    <select id="gender" name="gender" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]" required>
                        <option value="">Seleccione una opción</option>
                        @foreach (['Femenino', 'Masculino', 'Otro', 'Prefiero no decirlo'] as $gender)
                            <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                        @endforeach
                    </select>
                    @error('gender')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Teléfono de contacto</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                    @error('phone')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                    @error('email')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="address" class="mb-1 block text-sm font-medium text-slate-700">Dirección / Domicilio</label>
                    <textarea id="address" name="address" rows="2" maxlength="2000" autocomplete="street-address" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h3 class="mb-4 mt-7 border-t border-slate-200 pt-6 font-bold text-[#191346]">Contacto de emergencia</h3>
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label for="emergency_contact_name" class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
                    <input id="emergency_contact_name" type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" maxlength="255" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                    @error('emergency_contact_name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="emergency_contact_relationship" class="mb-1 block text-sm font-medium text-slate-700">Parentesco</label>
                    <input id="emergency_contact_relationship" type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" maxlength="100" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                    @error('emergency_contact_relationship')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="emergency_contact_phone" class="mb-1 block text-sm font-medium text-slate-700">Teléfono</label>
                    <input id="emergency_contact_phone" type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" maxlength="30" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                    @error('emergency_contact_phone')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <h3 class="mb-4 mt-7 border-t border-slate-200 pt-6 font-bold text-[#191346]">Información médica</h3>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="blood_type" class="mb-1 block text-sm font-medium text-slate-700">Tipo de sangre</label>
                    <select id="blood_type" name="blood_type" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">
                        <option value="">Sin especificar</option>
                        @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                            <option value="{{ $bloodType }}" @selected(old('blood_type') === $bloodType)>{{ $bloodType }}</option>
                        @endforeach
                    </select>
                    @error('blood_type')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="allergies_conditions" class="mb-1 block text-sm font-medium text-slate-700">Alergias o padecimientos relevantes</label>
                    <textarea id="allergies_conditions" name="allergies_conditions" rows="2" maxlength="5000" class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-[#223FAA]">{{ old('allergies_conditions') }}</textarea>
                    @error('allergies_conditions')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-7 flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route($listRoute) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">
                    <i class="fa-solid fa-user-plus"></i>
                    Guardar paciente
                </button>
            </div>
        </form>
    </div>

    <script>
        const birthDateInput = document.getElementById('patient-birth-date');
        const patientAge = document.getElementById('patient-age');

        function updatePatientAge() {
            if (!birthDateInput.value) {
                patientAge.textContent = 'Se calcula con la fecha de nacimiento';
                return;
            }

            const [year, month, day] = birthDateInput.value.split('-').map(Number);
            const today = new Date();
            let age = today.getFullYear() - year;

            if (today.getMonth() + 1 < month || (today.getMonth() + 1 === month && today.getDate() < day)) {
                age--;
            }

            patientAge.textContent = age >= 0 ? `${age} ${age === 1 ? 'año' : 'años'}` : 'Fecha no válida';
        }

        birthDateInput.addEventListener('input', updatePatientAge);
        updatePatientAge();
    </script>
@endsection