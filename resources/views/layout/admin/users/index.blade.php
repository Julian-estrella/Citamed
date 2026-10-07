@extends('layout.app')

@section('content')
    <div class="flex min-h-full w-full flex-col">
        <header class="mb-6 flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Administración</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Gestión de usuarios</h2>
            </div>
            <button class="rounded-2xl bg-[#223FAA] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-[#223FAA]/20">+ Nuevo usuario</button>
        </header>

        <div class="overflow-hidden rounded-3xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 bg-white text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nombre</th>
                        <th class="px-4 py-3 font-semibold">Correo</th>
                        <th class="px-4 py-3 font-semibold">Rol</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php
                        $users = [
                            ['name' => 'Julian Estrella', 'email' => 'julian@citamed.com', 'role' => 'Administrador', 'status' => 'Activo'],
                            ['name' => 'Dr. Médico', 'email' => 'medico@medico.com', 'role' => 'Médico', 'status' => 'Activo'],
                            ['name' => 'Recepcionista', 'email' => 'recepcion@recepcion.com', 'role' => 'Recepción', 'status' => 'Activo'],
                        ];
                    @endphp

                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium text-[#191346]">{{ $user['name'] }}</td>
                            <td class="px-4 py-3">{{ $user['email'] }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-[#EAF1FF] px-2.5 py-1 text-xs font-semibold text-[#223FAA]">{{ $user['role'] }}</span></td>
                            <td class="px-4 py-3"><span class="rounded-full bg-[#EAFBF7] px-2.5 py-1 text-xs font-semibold text-[#0f766e]">{{ $user['status'] }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <button class="mr-2 text-sm font-medium text-[#223FAA]">Editar</button>
                                <button class="text-sm font-medium text-red-500">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
