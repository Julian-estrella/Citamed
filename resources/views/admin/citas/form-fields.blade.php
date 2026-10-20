@php
    $isEditing = $appointment !== null;
    $slotsRoute = $user->isAdmin() ? 'admin.appointments.slots' : 'recepcion.appointments.slots';
    $scheduledAt = old('scheduled_at', $appointment?->scheduled_at?->format('Y-m-d\TH:i'));
    $appointmentDate = $scheduledAt ? substr($scheduledAt, 0, 10) : '';
    $appointmentTime = $scheduledAt ? substr($scheduledAt, 11, 5) : '';
    $isDoctor = $user->isMedico() && ! $user->isAdmin();
    $selectedDoctorId = old('doctor_id', $appointment?->doctor_id ?? ($isDoctor ? $user->id : ''));
    if ($isDoctor) {
        $slotsRoute = 'medico.appointments.slots';
    }
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
        <select id="doctor_id" @unless ($isDoctor) name="doctor_id" @endunless required @disabled($isDoctor) class="w-full rounded-xl border-slate-200 bg-white text-sm disabled:bg-slate-50 disabled:text-slate-600">
            @unless ($isDoctor)<option value="">Seleccione un médico activo</option>@endunless
            @foreach ($doctors as $doctor)
                <option
                    value="{{ $doctor->id }}"
                    data-specialty="{{ $doctor->doctorProfile?->specialty ?? 'Sin especialidad registrada' }}"
                    data-working-days="{{ $doctor->doctorProfile?->shifts->pluck('day_of_week')->unique()->values()->toJson() ?? '[]' }}"
                    @selected((string) $selectedDoctorId === (string) $doctor->id)
                >{{ $doctor->name }} — {{ $doctor->doctorProfile?->specialty ?? 'Sin especialidad registrada' }}</option>
            @endforeach
        </select>
        @if ($isDoctor)<input type="hidden" name="doctor_id" value="{{ $user->id }}">@endif
        <p id="doctor_specialty" class="mt-1 text-xs text-slate-500" aria-live="polite"></p>
        @error('doctor_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2" id="appointment-slot-picker" data-slots-url="{{ route($slotsRoute) }}" data-appointment-id="{{ $appointment?->id }}">
        <input id="scheduled_at" type="hidden" name="scheduled_at" value="{{ $scheduledAt }}">
        <input id="appointment_date" type="hidden" value="{{ $appointmentDate }}">
        <div class="grid gap-5 md:grid-cols-2">
    <div>
        <div class="mb-2 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Día de atención</span>
            <div class="flex items-center gap-1">
                <button id="appointment_calendar_previous" type="button" aria-label="Mes anterior" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <span id="appointment_calendar_month" class="min-w-32 text-center text-sm font-semibold capitalize text-slate-700" aria-live="polite"></span>
                <button id="appointment_calendar_next" type="button" aria-label="Mes siguiente" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="grid grid-cols-7 text-center text-xs font-semibold text-slate-500" aria-hidden="true">
            <span class="py-2">Lun</span><span class="py-2">Mar</span><span class="py-2">Mié</span><span class="py-2">Jue</span><span class="py-2">Vie</span><span class="py-2">Sáb</span><span class="py-2">Dom</span>
        </div>
        <div id="appointment_calendar_days" class="grid grid-cols-7 gap-1" role="group" aria-label="Seleccionar día de atención"></div>
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-100 ring-1 ring-emerald-300"></span>Días que trabaja</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-red-100 ring-1 ring-red-300"></span>Días que no trabaja</span>
        </div>
        <p id="appointment_selected_date" class="mt-2 min-h-4 text-xs text-slate-500" aria-live="polite"></p>
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
        const specialty = document.getElementById('doctor_specialty');
        const calendarMonth = document.getElementById('appointment_calendar_month');
        const calendarDays = document.getElementById('appointment_calendar_days');
        const selectedDate = document.getElementById('appointment_selected_date');
        const previousMonth = document.getElementById('appointment_calendar_previous');
        const nextMonth = document.getElementById('appointment_calendar_next');
        const initialTime = @json($appointmentTime);
        const appointmentId = picker.dataset.appointmentId;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        let displayedMonth = date.value ? parseDate(date.value) : new Date(today.getFullYear(), today.getMonth(), 1);
        let firstLoad = true;
        let requestNumber = 0;

        function parseDate(value) {
            const [year, month, day] = value.split('-').map(Number);
            return new Date(year, month - 1, day);
        }

        function formatDate(value) {
            const year = value.getFullYear();
            const month = String(value.getMonth() + 1).padStart(2, '0');
            const day = String(value.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function renderCalendar() {
            const workingDays = doctor.value
                ? JSON.parse(doctor.selectedOptions[0].dataset.workingDays || '[]').map(Number)
                : [];
            const selected = date.value ? parseDate(date.value) : null;
            const monthStart = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth(), 1);
            const firstDayOffset = (monthStart.getDay() + 6) % 7;
            const calendarStart = new Date(monthStart);
            calendarStart.setDate(monthStart.getDate() - firstDayOffset);
            const formatMonth = new Intl.DateTimeFormat('es', { month: 'long', year: 'numeric' });

            calendarMonth.textContent = formatMonth.format(monthStart);
            calendarDays.replaceChildren();

            for (let index = 0; index < 42; index++) {
                const day = new Date(calendarStart);
                day.setDate(calendarStart.getDate() + index);
                const isCurrentMonth = day.getMonth() === displayedMonth.getMonth();
                const isWorking = workingDays.includes(day.getDay() === 0 ? 7 : day.getDay());
                const isSelected = selected && formatDate(selected) === formatDate(day);
                const isPast = day < today;
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = String(day.getDate());
                button.disabled = isPast;
                button.setAttribute('aria-label', `${new Intl.DateTimeFormat('es', { dateStyle: 'full' }).format(day)}: ${isWorking ? 'trabaja' : 'no trabaja'}`);
                button.className = 'flex aspect-square items-center justify-center rounded-lg border text-xs font-medium transition disabled:cursor-not-allowed disabled:border-transparent disabled:bg-slate-50 disabled:text-slate-300';

                if (!isPast) {
                    button.classList.add(
                        isWorking ? 'border-emerald-200' : 'border-red-200',
                        isWorking ? 'bg-emerald-50' : 'bg-red-50',
                        isWorking ? 'text-emerald-800' : 'text-red-700',
                        isWorking ? 'hover:bg-emerald-100' : 'hover:bg-red-100'
                    );
                }
                if (!isCurrentMonth) button.classList.add('opacity-50');
                if (isSelected) {
                    button.classList.add('ring-2', 'ring-[#223FAA]', 'ring-offset-1');
                }

                button.addEventListener('click', () => {
                    date.value = formatDate(day);
                    displayedMonth = new Date(day.getFullYear(), day.getMonth(), 1);
                    renderCalendar();
                    date.dispatchEvent(new Event('change'));
                });
                calendarDays.append(button);
            }

            selectedDate.textContent = selected
                ? `Fecha seleccionada: ${new Intl.DateTimeFormat('es', { dateStyle: 'long' }).format(selected)}`
                : 'Selecciona un día para consultar los horarios disponibles.';
        }

        function updateDoctorDetails() {
            specialty.textContent = doctor.value
                ? `Especialidad: ${doctor.selectedOptions[0].dataset.specialty}`
                : 'Selecciona un médico para ver su especialidad y días de trabajo.';
            renderCalendar();
        }

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
        doctor.addEventListener('change', updateDoctorDetails);
        date.addEventListener('change', loadAvailableSlots);
        time.addEventListener('change', updateScheduledAt);
        previousMonth.addEventListener('click', () => {
            displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() - 1, 1);
            renderCalendar();
        });
        nextMonth.addEventListener('click', () => {
            displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 1);
            renderCalendar();
        });
        form.addEventListener('submit', (event) => {
            updateScheduledAt();
            if (!scheduledAt.value) {
                event.preventDefault();
                status.textContent = 'Selecciona uno de los horarios disponibles para continuar.';
                time.focus();
            }
        });

        updateDoctorDetails();
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