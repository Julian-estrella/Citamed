@extends('layout.app')

@section('content')
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Espacios clínicos</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Nuevo consultorio</h2>
            </div>
            <a href="{{ route($listRoute) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Volver
            </a>
        </header>

        <form action="{{ route($storeRoute) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            <div>
                <label for="specialty" class="mb-2 block text-sm font-semibold text-[#191346]">Especialidad asignada</label>
                <select id="specialty" name="specialty" required class="w-full rounded-xl border-slate-200 text-sm focus:border-[#223FAA] focus:ring-[#223FAA]">
                    <option value="">Seleccione una especialidad</option>
                    @foreach ($specialties as $specialty)
                        <option value="{{ $specialty }}" @selected(old('specialty') === $specialty)>{{ $specialty }}</option>
                    @endforeach
                </select>
                @error('specialty')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-2 text-xs text-slate-500">El número consecutivo se asignará automáticamente.</p>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route($listRoute) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Crear consultorio
                </button>
            </div>
        </form>
    </div>
@endsection