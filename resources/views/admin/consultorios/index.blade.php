@extends('layout.app')

@section('content')
    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Espacios clínicos</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Consultorios</h2>
                <p class="mt-1 text-sm text-slate-600">Consultorios registrados y su especialidad asignada.</p>
            </div>
            <a href="{{ route($createRoute) }}" class="inline-flex items-center gap-2 self-start rounded-xl bg-[#223FAA] px-4 py-2.5 text-sm font-semibold text-white">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                Nuevo consultorio
            </a>
        </header>

        @if (session('success'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</p>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-[#191346]">Consultorios registrados</h3>
                    <p class="text-sm text-slate-500">{{ $rooms->count() }} espacios</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Consultorio</th>
                            <th class="px-5 py-3 font-semibold">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Especialidad</span>
                                    <details class="relative">
                                        <summary aria-label="Filtrar por especialidad" class="flex h-7 w-7 cursor-pointer list-none items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-[#223FAA] [&::-webkit-details-marker]:hidden">
                                            <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                                        </summary>
                                        <div class="absolute right-0 z-20 mt-1 max-h-64 min-w-52 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                            @php($specialtySelected = empty($filters['specialty']))
                                            <a href="{{ route($listRoute) }}" class="block px-3 py-2 text-xs {{ $specialtySelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">Todas{{ $specialtySelected ? ' ✓' : '' }}</a>
                                            @foreach ($specialties as $specialty)
                                                @php($specialtySelected = ($filters['specialty'] ?? '') === $specialty)
                                                <a href="{{ route($listRoute, ['specialty' => $specialtySelected ? '' : $specialty]) }}" class="block px-3 py-2 text-xs {{ $specialtySelected ? 'bg-slate-50 font-semibold text-[#223FAA]' : 'text-slate-700 hover:bg-slate-50' }}">{{ $specialty }}{{ $specialtySelected ? ' ✓' : '' }}</a>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                            </th>
                            <th class="px-5 py-3 font-semibold">Médicos asignados</th>
                            <th class="px-5 py-3 font-semibold">Estado</th>
                            <th class="px-5 py-3 text-right font-semibold">Disponibilidad</th>
                            <th class="px-5 py-3 text-right font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rooms as $room)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-[#191346]">{{ $room->label }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $room->specialty }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $room->doctorProfiles->count() }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $room->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $room->is_active ? 'Activo' : 'Inactivo' }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route($listRoute.'.availability', $room) }}" class="inline-flex items-center gap-2 rounded-lg border border-[#223FAA]/20 bg-[#eef3ff] px-3 py-2 text-xs font-semibold text-[#223FAA] hover:bg-[#e2eaff]">
                                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                        Ver agenda
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route($editRoute, $room) }}" title="Editar especialidad" aria-label="Editar especialidad de {{ $room->label }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-[#223FAA]">
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="{{ route($toggleRoute, $room) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="{{ $room->is_active ? 'Desactivar' : 'Activar' }} consultorio" aria-label="{{ $room->is_active ? 'Desactivar' : 'Activar' }} {{ $room->label }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700">
                                                <i class="fa-solid {{ $room->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route($deleteRoute, $room) }}" onsubmit="return confirm('¿Eliminar {{ $room->label }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar consultorio" aria-label="Eliminar {{ $room->label }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600">
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay consultorios para esta especialidad.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection