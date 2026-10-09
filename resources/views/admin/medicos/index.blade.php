@extends('layout.app')

@section('content')
    <header class="mb-8 border-b border-slate-200 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Directorio</p>
        <h2 class="mt-2 text-3xl font-bold text-[#191346]">Médicos</h2>
        <p class="mt-1 text-sm text-slate-500">Médicos activos disponibles en el sistema.</p>
    </header>

    <div class="mb-3 flex justify-end">
        <form id="doctor-filters" method="GET" action="{{ route($user->isAdmin() ? 'admin.doctors' : 'recepcion.doctors') }}" class="flex items-center gap-2">
            <button type="submit" class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-semibold text-white"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <a href="{{ route($user->isAdmin() ? 'admin.doctors' : 'recepcion.doctors') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600">Limpiar</a>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Médico</th>
                        <th class="px-5 py-3 font-semibold">Correo electrónico</th>
                    </tr>
                    <tr class="border-t border-slate-200 bg-white normal-case">
                        <th class="px-3 py-2"><input form="doctor-filters" type="search" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Filtrar médico" aria-label="Filtrar por médico" class="w-full rounded-lg border-slate-200 text-xs"></th>
                        <th class="px-3 py-2"><input form="doctor-filters" type="search" name="email" value="{{ $filters['email'] ?? '' }}" placeholder="Filtrar correo" aria-label="Filtrar por correo" class="w-full rounded-lg border-slate-200 text-xs"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($doctors as $doctor)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $doctor->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $doctor->email }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-5 py-8 text-center text-slate-500">No se encontraron médicos con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
    </div>
@endsection