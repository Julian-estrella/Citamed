@extends('layout.app')

@section('content')
    @php
        $routePrefix = $user->isAdmin() ? 'admin' : ($user->isMedico() ? 'medico' : 'recepcion');
        $updateRoute = $routePrefix.'.appointments.update';
        $listRoute = $routePrefix.'.appointments';
    @endphp

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Citas</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Reprogramar cita</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $appointment->patient->name }} · {{ $appointment->scheduled_at->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route($listRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Volver
            </a>
        </header>

        <form action="{{ route($updateRoute, $appointment) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')
            @include('admin.citas.form-fields', ['appointment' => $appointment])
        </form>
    </div>
@endsection