@php
    $shiftValues = old('shifts', $profile?->shifts
        ->groupBy(fn ($shift) => match (substr((string) $shift->start_time, 0, 5)) {
            $workShifts['matutino']['start'] => 'matutino',
            $workShifts['vespertino']['start'] => 'vespertino',
            default => '',
        })
        ->map(fn ($shifts, $session) => ['days' => $shifts->pluck('day_of_week')->all(), 'session' => $session])
        ->values()
        ->all() ?? []);
    if (empty($shiftValues)) {
        $shiftValues = [['day_of_week' => '', 'session' => '']];
    }
@endphp

@if (! $doctor)
    <h3 class="mb-4 font-bold text-[#191346]">Cuenta del médico</h3>
    <div class="mb-7 grid gap-5 border-b border-slate-200 pb-7 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre(s) y apellidos</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" required class="w-full rounded-xl border-slate-200 text-sm">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required class="w-full rounded-xl border-slate-200 text-sm">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
            <input id="password" type="password" name="password" minlength="8" maxlength="12" autocomplete="new-password" required class="w-full rounded-xl border-slate-200 text-sm">
            <p class="mt-1 text-xs text-slate-500">De 8 a 12 caracteres, con mayúscula, minúscula, número y símbolo.</p>
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" maxlength="12" autocomplete="new-password" required class="w-full rounded-xl border-slate-200 text-sm">
        </div>
    </div>
@endif

<h3 class="mb-4 font-bold text-[#191346]">Datos profesionales</h3>
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="professional_title" class="mb-1 block text-sm font-medium text-slate-700">Tratamiento profesional</label>
        <select id="professional_title" name="professional_title" required class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Seleccione</option>
            @foreach (['Dr.' => 'Dr.', 'Dra.' => 'Dra.'] as $value => $label)
                <option value="{{ $value }}" @selected(old('professional_title', $profile?->professional_title) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('professional_title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="license_number" class="mb-1 block text-sm font-medium text-slate-700">Cédula profesional</label>
        <p id="license_number" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $profile?->license_number ?? 'Se asignará automáticamente al guardar' }}</p>
    </div>
    <div>
        <label for="specialty" class="mb-1 block text-sm font-medium text-slate-700">Especialidad médica</label>
        <select id="specialty" name="specialty" required class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Seleccione una especialidad</option>
            @foreach ($specialties as $specialty)
                <option value="{{ $specialty }}" @selected(old('specialty', $profile?->specialty) === $specialty)>{{ $specialty }}</option>
            @endforeach
        </select>
        @error('specialty')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Teléfono (máximo 8 dígitos)</label>
        <input id="phone" type="tel" name="phone" value="{{ old('phone', $profile?->phone) }}" maxlength="8" inputmode="numeric" pattern="[0-9]{1,8}" autocomplete="tel" required class="w-full rounded-xl border-slate-200 text-sm">
        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="appointment_duration" class="mb-1 block text-sm font-medium text-slate-700">Duración estimada de la cita</label>
        <select id="appointment_duration" name="appointment_duration" required class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Seleccione duración</option>
            @foreach ([20, 30, 45] as $duration)
                <option value="{{ $duration }}" @selected((string) old('appointment_duration', $profile?->appointment_duration) === (string) $duration)>{{ $duration }} minutos</option>
            @endforeach
        </select>
        @error('appointment_duration')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="office" class="mb-1 block text-sm font-medium text-slate-700">Consultorio asignado</label>
        <select id="office" name="office" required class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Seleccione consultorio</option>
            @foreach ($consultationRooms as $room)
                <option value="{{ $room->label }}" data-specialty="{{ $room->specialty }}" data-active="{{ $room->is_active ? '1' : '0' }}" @selected(old('office', $profile?->office) === $room->label)>{{ $room->label }} · {{ $room->specialty }}{{ $room->is_active ? '' : ' (inactivo)' }}</option>
            @endforeach
        </select>
        @error('office')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="is_active" class="mb-1 block text-sm font-medium text-slate-700">Estado del médico</label>
        <select id="is_active" name="is_active" required class="w-full rounded-xl border-slate-200 text-sm">
            <option value="1" @selected((string) old('is_active', (int) ($doctor?->is_active ?? true)) === '1')>Activo</option>
            <option value="0" @selected((string) old('is_active', (int) ($doctor?->is_active ?? true)) === '0')>Inactivo</option>
        </select>
        @error('is_active')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<section class="mt-7 border-t border-slate-200 pt-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="font-bold text-[#191346]">Días y turnos de atención</h3>
            <p class="mt-1 text-sm text-slate-500">Selecciona uno o varios días por turno. Matutino: 07:00 a 14:00 · Vespertino: 15:00 a 22:00</p>
        </div>
        <button id="add-shift" type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
            <i class="fa-solid fa-plus"></i> Agregar turno
        </button>
    </div>
    @error('shifts')<p class="mb-3 text-xs text-red-600">{{ $message }}</p>@enderror
    <div id="shift-list" class="space-y-3">
        @foreach ($shiftValues as $index => $shift)
            <div class="shift-row grid gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-[1.5fr_1fr_auto] sm:items-end">
                <div>
                    <p class="mb-2 text-xs font-medium text-slate-600">Días</p>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach ($daysOfWeek as $day => $label)
                            <label class="flex items-center gap-2 text-xs text-slate-700">
                                <input type="checkbox" name="shifts[{{ $index }}][days][]" value="{{ $day }}" @checked(in_array((string) $day, array_map('strval', (array) ($shift['days'] ?? [])), true)) class="rounded border-slate-300 text-[#223FAA] focus:ring-[#223FAA]">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('shifts.'.$index.'.days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="shift-session-{{ $index }}" class="mb-1 block text-xs font-medium text-slate-600">Turno</label>
                    <select id="shift-session-{{ $index }}" name="shifts[{{ $index }}][session]" required class="w-full rounded-lg border-slate-200 text-sm">
                        <option value="">Seleccione turno</option>
                        @foreach ($workShifts as $key => $session)
                            <option value="{{ $key }}" @selected(($shift['session'] ?? '') === $key)>{{ $session['label'] }} · {{ $session['start'] }}–{{ $session['end'] }}</option>
                        @endforeach
                    </select>
                    @error('shifts.'.$index.'.session')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="button" class="remove-shift inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 text-red-600" aria-label="Quitar turno" title="Quitar turno"><i class="fa-solid fa-trash"></i></button>
            </div>
        @endforeach
    </div>
</section>

<template id="shift-template">
    <div class="shift-row grid gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-[1.5fr_1fr_auto] sm:items-end">
        <div>
            <p class="mb-2 text-xs font-medium text-slate-600">Días</p>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach ($daysOfWeek as $day => $label)
                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" name="shifts[__INDEX__][days][]" value="{{ $day }}" class="rounded border-slate-300 text-[#223FAA] focus:ring-[#223FAA]">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
        <div>
            <label for="shift-session-__INDEX__" class="mb-1 block text-xs font-medium text-slate-600">Turno</label>
            <select id="shift-session-__INDEX__" name="shifts[__INDEX__][session]" required class="w-full rounded-lg border-slate-200 text-sm">
                <option value="">Seleccione turno</option>
                @foreach ($workShifts as $key => $session)<option value="{{ $key }}">{{ $session['label'] }} · {{ $session['start'] }}–{{ $session['end'] }}</option>@endforeach
            </select>
        </div>
        <button type="button" class="remove-shift inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 text-red-600" aria-label="Quitar turno" title="Quitar turno"><i class="fa-solid fa-trash"></i></button>
    </div>
</template>

<script>
    const specialtySelect = document.getElementById('specialty');
    const officeSelect = document.getElementById('office');
    const currentDoctorOffice = @json($profile?->office);

    function filterConsultationRooms() {
        const specialty = specialtySelect.value;
        let selectedOfficeIsAvailable = false;

        Array.from(officeSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matchesSpecialty = option.dataset.specialty === specialty;
            const isCurrentRoom = option.value === currentDoctorOffice;
            const isAvailable = option.dataset.active === '1' || isCurrentRoom;
            option.hidden = !matchesSpecialty || !isAvailable;
            option.disabled = !matchesSpecialty || !isAvailable;
            if (option.selected && matchesSpecialty && isAvailable) {
                selectedOfficeIsAvailable = true;
            }
        });

        if (!selectedOfficeIsAvailable) {
            officeSelect.value = '';
        }
    }

    specialtySelect.addEventListener('change', filterConsultationRooms);
    filterConsultationRooms();

    const shiftList = document.getElementById('shift-list');
    const shiftTemplate = document.getElementById('shift-template');
    let nextShiftIndex = {{ empty($shiftValues) ? 0 : max(array_keys($shiftValues)) + 1 }};

    document.getElementById('add-shift').addEventListener('click', () => {
        const fragment = shiftTemplate.content.cloneNode(true);
        fragment.querySelectorAll('[name], [id], label[for]').forEach((element) => {
            ['name', 'id', 'for'].forEach((attribute) => {
                if (element.hasAttribute(attribute)) {
                    element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', nextShiftIndex));
                }
            });
        });
        shiftList.append(fragment);
        nextShiftIndex++;
    });

    shiftList.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-shift');
        if (removeButton && shiftList.querySelectorAll('.shift-row').length > 1) {
            removeButton.closest('.shift-row').remove();
        }
    });
</script>