<x-app-layout>
    @php
        $user = Auth::user();
        $roleLabel = match ($user?->role ?? '') {
            'administrador' => 'Administrador',
            'medico' => 'Médico',
            'recepcion' => 'Recepción',
            default => 'Sin rol',
        };
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-[28px] bg-white p-8 shadow-[0_20px_40px_rgba(25,19,70,0.08)] ring-1 ring-[#edf2ff]">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Panel administrativo</p>
            <h1 class="mt-3 text-3xl font-bold text-[#191346]">Bienvenido, {{ $user?->name ?? 'Usuario' }}</h1>
            <p class="mt-2 text-sm text-[#5a678d]">Rol asignado: <span class="font-semibold text-[#223FAA]">{{ $roleLabel }}</span></p>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-[#223FAA] p-5 text-white">
                    <p class="text-sm opacity-80">Pacientes</p>
                    <p class="mt-3 text-3xl font-bold">1,284</p>
                </div>
                <div class="rounded-2xl bg-[#AC9FE2] p-5 text-[#191346]">
                    <p class="text-sm opacity-80">Citas hoy</p>
                    <p class="mt-3 text-3xl font-bold">48</p>
                </div>
                <div class="rounded-2xl bg-[#AAE2E2] p-5 text-[#191346]">
                    <p class="text-sm opacity-80">Consultorios</p>
                    <p class="mt-3 text-3xl font-bold">12</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
