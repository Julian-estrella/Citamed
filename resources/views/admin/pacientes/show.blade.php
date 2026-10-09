@extends('layout.app')

@section('content')
    @php
        $isAdmin = $user->isAdmin();
        $listRoute = $isAdmin ? 'admin.patients' : ($user->isMedico() ? 'medico.patients' : 'recepcion.patients');
        $editRoute = $isAdmin ? 'admin.patients.edit' : 'recepcion.patients.edit';
    @endphp

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Expediente del paciente</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $patient->name }}</h2>
            </div>
            <div class="flex items-center gap-2">
                @if ($user->hasPermission('modificar_pacientes') || $user->hasPermission('gestionar_pacientes'))
                    <a href="{{ route($editRoute, $patient) }}" title="Editar paciente" aria-label="Editar paciente" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-[#223FAA]"><i class="fa-solid fa-pen-to-square"></i></a>
                @endif
                <a href="{{ route($listRoute) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver
                </a>
            </div>
        </header>

        <section class="grid gap-6 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 p-5">
                <h3 class="mb-4 font-bold text-[#191346]">Datos personales</h3>
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Fecha de nacimiento / edad</dt><dd class="mt-1 font-medium">{{ $patient->birth_date?->format('d/m/Y') ?? 'Sin fecha' }}{{ $patient->age !== null ? ' / '.$patient->age.' años' : '' }}</dd></div>
                    <div><dt class="text-slate-500">Sexo / género</dt><dd class="mt-1 font-medium">{{ $patient->gender ?: 'Sin especificar' }}</dd></div>
                    <div><dt class="text-slate-500">Teléfono</dt><dd class="mt-1 font-medium">{{ $patient->phone ?: 'Sin registrar' }}</dd></div>
                    <div><dt class="text-slate-500">Correo</dt><dd class="mt-1 font-medium">{{ $patient->email ?: 'Sin registrar' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">Domicilio</dt><dd class="mt-1 font-medium">{{ $patient->address ?: 'Sin registrar' }}</dd></div>
                    <div><dt class="text-slate-500">Estado</dt><dd class="mt-1 font-medium">{{ $patient->is_active ? 'Activo' : 'Inactivo' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 p-5">
                <h3 class="mb-4 font-bold text-[#191346]">Información médica</h3>
                <dl class="grid gap-4 text-sm">
                    <div><dt class="text-slate-500">Tipo de sangre</dt><dd class="mt-1 font-medium">{{ $patient->blood_type ?: 'Sin especificar' }}</dd></div>
                    <div><dt class="text-slate-500">Alergias o padecimientos</dt><dd class="mt-1 whitespace-pre-line font-medium">{{ $patient->allergies_conditions ?: 'Sin registrar' }}</dd></div>
                </dl>
                <h3 class="mb-3 mt-6 border-t border-slate-200 pt-5 font-bold text-[#191346]">Contacto de emergencia</h3>
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Nombre</dt><dd class="mt-1 font-medium">{{ $patient->emergency_contact_name ?: 'Sin registrar' }}</dd></div>
                    <div><dt class="text-slate-500">Parentesco</dt><dd class="mt-1 font-medium">{{ $patient->emergency_contact_relationship ?: 'Sin registrar' }}</dd></div>
                    <div><dt class="text-slate-500">Teléfono</dt><dd class="mt-1 font-medium">{{ $patient->emergency_contact_phone ?: 'Sin registrar' }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200">
            <div class="border-b border-slate-200 p-5">
                <h3 class="font-bold text-[#191346]">Historial de citas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Fecha y hora</th>
                            <th class="px-4 py-3 font-semibold">Médico</th>
                            <th class="px-4 py-3 font-semibold">Motivo</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td class="px-4 py-3">{{ $appointment->scheduled_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $appointment->doctor?->name ?? 'Médico no disponible' }}</td>
                                <td class="px-4 py-3">{{ $appointment->reason ?: 'Consulta' }}</td>
                                <td class="px-4 py-3">{{ ucfirst($appointment->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay citas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection