@extends('layout.app')

@section('content')
    <header class="mb-8 border-b border-slate-200 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#223FAA]">Directorio</p>
        <h2 class="mt-2 text-3xl font-bold text-[#191346]">Médicos</h2>
        <p class="mt-1 text-sm text-slate-500">Médicos activos disponibles en el sistema.</p>
    </header>

    @if ($doctors->isNotEmpty())
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Médico</th>
                        <th class="px-5 py-3 font-semibold">Correo electrónico</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($doctors as $doctor)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $doctor->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $doctor->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
            <i class="fa-solid fa-user-doctor text-2xl text-slate-400"></i>
            <h3 class="mt-3 font-semibold text-slate-800">No hay médicos activos</h3>
            <p class="mt-1 text-sm text-slate-500">Los médicos activos aparecerán en este directorio.</p>
        </div>
    @endif
@endsection