@extends('layout.app')

@section('content')
    @php
        $shiftValues = old('shifts', $profile?->shifts->map(fn ($shift) => [
            'day_of_week' => $shift->day_of_week,
            'session' => match (substr((string) $shift->start_time, 0, 5)) {
                $workShifts['matutino']['start'] => 'matutino',
                $workShifts['vespertino']['start'] => 'vespertino',
                default => '',
            },
        ])->all() ?? []);
        if (empty($shiftValues)) {
            $shiftValues = [['day_of_week' => '', 'session' => '']];
        }
    @endphp

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Gestión de médicos</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $profile ? 'Editar médico' : 'Completar registro médico' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $doctor->name }} · {{ $doctor->email }}</p>
            </div>
            <a href="{{ route($doctorsRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
        </header>

        <form action="{{ route($updateRoute, $doctor) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')

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
                    <input id="specialty" type="text" name="specialty" value="{{ old('specialty', $profile?->specialty) }}" maxlength="150" required class="w-full rounded-xl border-slate-200 text-sm">
                    @error('specialty')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Teléfono de contacto (máximo 8 dígitos)</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $profile?->phone) }}" maxlength="8" inputmode="numeric" pattern="[0-9]{1,8}" autocomplete="tel" required class="w-full rounded-xl border-slate-200 text-sm">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="appointment_duration" class="mb-1 block text-sm font-medium text-slate-700">Duración estimada de cada cita</label>
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
                        @foreach ($offices as $office)
                            <option value="{{ $office }}" @selected(old('office', $profile?->office) === $office)>{{ $office }}</option>
                        @endforeach
                    </select>
                    @error('office')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="is_active" class="mb-1 block text-sm font-medium text-slate-700">Estado del médico</label>
                    <select id="is_active" name="is_active" required class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="1" @selected((string) old('is_active', (int) $doctor->is_active) === '1')>Activo</option>
                        <option value="0" @selected((string) old('is_active', (int) $doctor->is_active) === '0')>Inactivo</option>
                    </select>
                    @error('is_active')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <section class="mt-7 border-t border-slate-200 pt-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-[#191346]">Días y turnos de atención</h3>
                        <p class="mt-1 text-sm text-slate-500">Matutino: 07:00 a 14:00 · Vespertino: 15:00 a 22:00</p>
                    </div>
                    <button id="add-shift" type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-plus"></i> Agregar turno
                    </button>
                </div>
                @error('shifts')<p class="mb-3 text-xs text-red-600">{{ $message }}</p>@enderror
                <div id="shift-list" class="space-y-3">
                    @foreach ($shiftValues as $index => $shift)
                        <div class="shift-row grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                            <div>
                                <label for="shift-day-{{ $index }}" class="mb-1 block text-xs font-medium text-slate-600">Día</label>
                                <select id="shift-day-{{ $index }}" name="shifts[{{ $index }}][day_of_week]" required class="w-full rounded-lg border-slate-200 text-sm">
                                    <option value="">Seleccione un día</option>
                                    @foreach ($daysOfWeek as $day => $label)
                                        <option value="{{ $day }}" @selected((string) ($shift['day_of_week'] ?? '') === (string) $day)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('shifts.'.$index.'.day_of_week')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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

            <div class="mt-7 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route($doctorsRoute) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white"><i class="fa-solid fa-floppy-disk"></i> Guardar médico</button>
            </div>
        </form>
    </div>

    <template id="shift-template">
        <div class="shift-row grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
            <div>
                <label for="shift-day-__INDEX__" class="mb-1 block text-xs font-medium text-slate-600">Día</label>
                <select id="shift-day-__INDEX__" name="shifts[__INDEX__][day_of_week]" required class="w-full rounded-lg border-slate-200 text-sm">
                    <option value="">Seleccione un día</option>
                    @foreach ($daysOfWeek as $day => $label)<option value="{{ $day }}">{{ $label }}</option>@endforeach
                </select>
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
@endsection