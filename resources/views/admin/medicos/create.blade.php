@extends('layout.app')

@section('content')
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Gestión de médicos</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">{{ $doctor ? ($profile ? 'Editar médico' : 'Completar registro médico') : 'Registrar médico' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $doctor ? $doctor->name.' · '.$doctor->email : 'Cuenta de acceso y datos profesionales' }}</p>
            </div>
            <a href="{{ route($doctorsRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </header>

        <form action="{{ $doctor ? route($updateRoute, $doctor) : route($storeRoute) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @if ($doctor)
                @method('PUT')
            @endif
            @include('admin.medicos.form-fields')

            <div class="mt-7 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route($doctorsRoute) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white"><i class="fa-solid {{ $doctor ? 'fa-floppy-disk' : 'fa-user-plus' }}"></i> {{ $doctor ? 'Guardar médico' : 'Registrar médico' }}</button>
            </div>
        </form>
    </div>
@endsection