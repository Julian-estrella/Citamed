@extends('layout.app')

@section('content')
    @php
        $user = Auth::user();
    @endphp

    <div class="flex min-h-full w-full flex-col">
        <header class="mb-8 border-b border-slate-200 pb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Recepción</p>
            <h2 class="mt-2 text-3xl font-bold text-[#191346]">Bienvenido, {{ $user?->name ?? 'Recepción' }}</h2>
        </header>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-3xl bg-[#223FAA] p-5 text-white">
                <p class="text-sm text-white/80">Pacientes registrados</p>
                <p class="mt-3 text-3xl font-bold">120</p>
            </article>
            <article class="rounded-3xl bg-[#AC9FE2] p-5 text-[#191346]">
                <p class="text-sm opacity-80">Citas programadas</p>
                <p class="mt-3 text-3xl font-bold">36</p>
            </article>
            <article class="rounded-3xl bg-[#AAE2E2] p-5 text-[#191346]">
                <p class="text-sm opacity-80">Horarios disponibles</p>
                <p class="mt-3 text-3xl font-bold">09</p>
            </article>
        </section>

        <section class="mt-8 rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-xl font-bold text-[#191346]">Citas del día</h3>
            <div class="mt-4 space-y-3">
                @php
                    $appointments = [
                        ['time' => '08:30', 'patient' => 'Andrea López', 'doctor' => 'Dr. Ramírez'],
                        ['time' => '10:15', 'patient' => 'José García', 'doctor' => 'Dra. Silva'],
                        ['time' => '12:00', 'patient' => 'María Pérez', 'doctor' => 'Dr. Ortega'],
                    ];
                @endphp

                @foreach ($appointments as $appointment)
                    <div class="flex items-center justify-between rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                        <div>
                            <p class="font-semibold text-[#191346]">{{ $appointment['patient'] }}</p>
                            <p class="text-sm text-slate-500">{{ $appointment['doctor'] }}</p>
                        </div>
                        <span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $appointment['time'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
