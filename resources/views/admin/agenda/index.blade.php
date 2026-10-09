@extends('layout.app')

@section('content')
    <header class="mb-8 border-b border-slate-200 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Planificación</p>
        <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $title }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    </header>

    @if (session('success'))
        <p class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</p>
    @endif

    @if ($appointments->isNotEmpty())
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Hora</th>
                        <th class="px-4 py-3 font-semibold">Paciente</th>
                        <th class="px-4 py-3 font-semibold">Médico</th>
                        <th class="px-4 py-3 font-semibold">Motivo</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold">{{ $appointment->scheduled_at->format('H:i') }}</td>
                            <td class="px-4 py-3">{{ $appointment->patient->name }}</td>
                            <td class="px-4 py-3">{{ $appointment->doctor?->name ?? 'Médico no disponible' }}</td>
                            <td class="px-4 py-3">{{ $appointment->reason ?: 'Consulta' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($appointment->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
            <i class="fa-solid fa-calendar-days text-2xl text-slate-400"></i>
            <h3 class="mt-3 font-semibold text-slate-800">Agenda sin registros</h3>
            <p class="mt-1 text-sm text-slate-500">Las citas activas de hoy aparecerán aquí.</p>
        </div>
    @endif
@endsection