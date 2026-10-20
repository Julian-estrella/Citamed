@extends('layout.app')

@section('content')
    @php
        $storeRoute = $user->isAdmin() ? 'admin.appointments.store' : 'recepcion.appointments.store';
        $listRoute = $user->isAdmin() ? 'admin.appointments' : 'recepcion.appointments';
    @endphp

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Citas</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Programar cita</h2>
            </div>
            <a href="{{ route($listRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Volver
            </a>
        </header>

        @if ($patients->isEmpty() || $doctors->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Se necesita al menos un paciente activo y un médico activo con horario configurado para programar citas.</div>
        @endif

        <form action="{{ route($storeRoute) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @include('admin.citas.form-fields', ['appointment' => null])
        </form>
    </div>
@endsection