@php
    $isEditing = $appointment !== null;
    $slotsRoute = $user->isAdmin() ? 'admin.appointments.slots' : 'recepcion.appointments.slots';
    $scheduledAt = old('scheduled_at', $appointment?->scheduled_at?->format('Y-m-d\TH:i'));
    $appointmentDate = $scheduledAt ? substr($scheduledAt, 0, 10) : '';
    $appointmentTime = $scheduledAt ? substr($scheduledAt, 11, 5) : '';
@endphp

<h3 class="mb-4 font-bold text-[#191346]">Datos de la cita</h3>
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="patient_id" class="mb-1 block text-sm font-medium text-slate-700">Paciente</label>
        <select id="patient_id" name="patient_id" required class="w-full rounded-xl border-slate-200 bg-white text-sm">
            <option value="">Seleccione un paciente</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected((string) old('patient_id', $appointment?->patient_id) === (string) $patient->id)>{{ $patient->name }}</option>
            @endforeach
        </select>
        @error('patient_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="doctor_id" class="mb-1 block text-sm font-medium text-slate-700">Médico</label>
        <select id="doctor_id" name="doctor_id" required class="w-full rounded-xl border-slate-200 bg-white text-sm">
            <option value="">Seleccione un médico activo</option>
            @foreach ($doctors as $doctor)
                <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $appointment?->doctor_id) === (string) $doctor->id)>{{ $doctor->name }}</option>
            @endforeach
        </select>
        @error('doctor_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2" id="appointment-slot-picker" data-slots-url="{{ route($slotsRoute) }}" data-appointment-id="{{ $appointment?->id }}">
        <input id="scheduled_at" type="hidden" name="scheduled_at" value="{{ $scheduledAt }}">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="appointment_date" class="mb-1 block text-sm font-medium text-slate-700">Día de atención</label>
                <input id="appointment_date" type="date" min="{{ today()->toDateString() }}" value="{{ $appointmentDate }}" required class="w-full rounded-xl border-slate-200 bg-white text-sm">
            </div>
            <div>
                <label for="appointment_time" class="mb-1 block text-sm font-medium text-slate-700">Horarios libres</label>
                <select id="appointment_time" required disabled class="w-full rounded-xl border-slate-200 bg-white text-sm">
                    <option value="">Seleccione médico y fecha</option>
                </select>
            </div>
        </div>
        <p id="appointment_slots_status" class="mt-2 text-xs text-slate-500" aria-live="polite">Selecciona un médico y un día para consultar sus horarios.</p>
        @error('scheduled_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="reason" class="mb-1 block text-sm font-medium text-slate-700">Motivo de consulta</label>
        <input id="reason" name="reason" value="{{ old('reason', $appointment?->reason) }}" maxlength="255" class="w-full rounded-xl border-slate-200 bg-white text-sm">
        @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    @if ($isEditing)
        <div class="md:col-span-2">
            <label for="reschedule_reason" class="mb-1 block text-sm font-medium text-slate-700">Motivo de reprogramación</label>
            <textarea id="reschedule_reason" name="reschedule_reason" rows="3" maxlength="500" required class="w-full rounded-xl border-slate-200 bg-white text-sm">{{ old('reschedule_reason') }}</textarea>
            @error('reschedule_reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif
</div>

<script>
    (() => {
        const picker = document.getElementById('appointment-slot-picker');
        const form = picker.closest('form');
        const doctor = document.getElementById('doctor_id');
        const date = document.getElementById('appointment_date');
        const time = document.getElementById('appointment_time');
        const scheduledAt = document.getElementById('scheduled_at');
        const status = document.getElementById('appointment_slots_status');
        const initialTime = @json($appointmentTime);
        const appointmentId = picker.dataset.appointmentId;
        let firstLoad = true;
        let requestNumber = 0;

        function updateScheduledAt() {
            scheduledAt.value = date.value && time.value ? `${date.value}T${time.value}` : '';
        }

        async function loadAvailableSlots() {
            const currentRequest = ++requestNumber;
            time.replaceChildren(new Option('Seleccione un horario', ''));
            time.disabled = true;
            scheduledAt.value = '';

            if (!doctor.value || !date.value) {
                status.textContent = 'Selecciona un médico y un día para consultar sus horarios.';
                return;
            }

            status.textContent = 'Consultando horarios disponibles...';
            const query = new URLSearchParams({ doctor_id: doctor.value, date: date.value });
            if (appointmentId) query.set('appointment_id', appointmentId);

            try {
                const response = await fetch(`${picker.dataset.slotsUrl}?${query}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) throw new Error('No se pudieron cargar los horarios.');

                const data = await response.json();
                if (currentRequest !== requestNumber) return;

                for (const slot of data.slots) {
                    time.add(new Option(slot.label, slot.time));
                }

                const preferredTime = firstLoad ? initialTime : '';
                firstLoad = false;
                if (data.slots.some((slot) => slot.time === preferredTime)) {
                    time.value = preferredTime;
                }
                time.disabled = data.slots.length === 0;
                status.textContent = data.slots.length
                    ? `${data.slots.length} horarios disponibles.`
                    : 'No hay horarios libres para este médico en el día seleccionado.';
                updateScheduledAt();
            } catch (error) {
                if (currentRequest !== requestNumber) return;
                status.textContent = error.message;
            }
        }

        doctor.addEventListener('change', loadAvailableSlots);
        date.addEventListener('change', loadAvailableSlots);
        time.addEventListener('change', updateScheduledAt);
        form.addEventListener('submit', (event) => {
            updateScheduledAt();
            if (!scheduledAt.value) {
                event.preventDefault();
                status.textContent = 'Selecciona uno de los horarios disponibles para continuar.';
                time.focus();
            }
        });

        if (doctor.value && date.value) loadAvailableSlots();
    })();
</script>

<div class="mt-7 flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
    <a href="{{ route($user->isAdmin() ? 'admin.appointments' : 'recepcion.appointments') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Cancelar</a>
    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white">
        <i class="fa-solid {{ $isEditing ? 'fa-calendar-check' : 'fa-calendar-plus' }}" aria-hidden="true"></i>
        {{ $isEditing ? 'Guardar reprogramación' : 'Programar cita' }}
    </button>
</div>