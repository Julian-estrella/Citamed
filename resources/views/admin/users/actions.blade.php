@extends('layout.app')

@section('content')
    <div class="flex min-h-full w-full flex-col gap-6">
        <header class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#223FAA]">Usuarios</p>
                <h2 class="mt-2 text-3xl font-bold text-[#191346]">Funciones del módulo</h2>
            </div>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
        </header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EAF1FF] text-[#223FAA]">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191346]">Registrar usuarios</h3>
                <p class="mt-2 text-sm text-slate-500">Crear nuevas cuentas con nombre, correo, rol y credenciales.</p>
                <a href="{{ route('admin.users.create') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#223FAA]">
                    Ir a registrar
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EAF1FF] text-[#223FAA]">
                    <i class="fa-solid fa-list"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191346]">Consultar usuarios</h3>
                <p class="mt-2 text-sm text-slate-500">Acceder a la lista completa, buscar información específica y filtrar resultados.</p>
                <a href="{{ route('admin.users') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#223FAA]">
                    Ver listado
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EAF1FF] text-[#223FAA]">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191346]">Modificar usuarios</h3>
                <p class="mt-2 text-sm text-slate-500">Actualizar datos personales, tipo de rol y estado de la cuenta.</p>
                <a href="{{ route('admin.users') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#223FAA]">
                    Editar usuario
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EAF1FF] text-[#223FAA]">
                    <i class="fa-solid fa-toggle-on"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191346]">Activar / Desactivar</h3>
                <p class="mt-2 text-sm text-slate-500">Habilitar o deshabilitar cuentas según el criterio del administrador.</p>
                <a href="{{ route('admin.users') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#223FAA]">
                    Gestionar estados
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EAF1FF] text-[#223FAA]">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191346]">Asignar tipo de usuario</h3>
                <p class="mt-2 text-sm text-slate-500">Definir rol o permisos específicos para cada cuenta.</p>
                <a href="{{ route('admin.users') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#223FAA]">
                    Asignar rol
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EAF1FF] text-[#223FAA]">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 class="text-lg font-bold text-[#191346]">Actualizar credenciales</h3>
                <p class="mt-2 text-sm text-slate-500">Cambiar o restablecer contraseñas de manera segura para cada usuario.</p>
                <a href="{{ route('admin.users') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#223FAA]">
                    Actualizar acceso
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </section>
    </div>
@endsection
