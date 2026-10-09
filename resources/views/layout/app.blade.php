@php
    $user = Auth::user();
    $role = $user?->role ?? 'recepcion';
    $navigation = [
        'administrador' => [
            ['label' => 'Inicio', 'route' => route('admin.panel'), 'icon' => 'fa-solid fa-house', 'permission' => 'visualizar_panel_principal'],
            ['label' => 'Gestión de usuarios', 'route' => route('admin.users'), 'icon' => 'fa-solid fa-users', 'permission' => 'gestionar_usuarios'],
            ['label' => 'Pacientes', 'route' => route('admin.patients'), 'icon' => 'fa-solid fa-user-injured', 'permission' => 'gestionar_pacientes'],
            ['label' => 'Médicos', 'route' => route('admin.doctors'), 'icon' => 'fa-solid fa-user-doctor', 'permission' => 'gestionar_medicos'],
            ['label' => 'Citas', 'route' => route('admin.appointments'), 'icon' => 'fa-solid fa-calendar-check', 'permission' => 'gestionar_citas'],
            ['label' => 'Agenda', 'route' => route('admin.agenda'), 'icon' => 'fa-solid fa-clock', 'permission' => 'consultar_agenda'],
        ],
        'medico' => [
            ['label' => 'Inicio', 'route' => route('medico.dashboard'), 'icon' => 'fa-solid fa-house', 'permission' => 'consultar_panel_principal_medico'],
            ['label' => 'Mis citas', 'route' => route('medico.appointments'), 'icon' => 'fa-solid fa-calendar-check', 'permission' => 'consultar_citas'],
            ['label' => 'Pacientes', 'route' => route('medico.patients'), 'icon' => 'fa-solid fa-user-injured', 'permission' => 'consultar_pacientes_que_atiende'],
            ['label' => 'Horario', 'route' => route('medico.agenda'), 'icon' => 'fa-solid fa-clock', 'permission' => 'gestionar_horario_atencion'],
            ['label' => 'Disponibilidad', 'route' => route('medico.availability'), 'icon' => 'fa-solid fa-check-circle', 'permission' => 'gestionar_disponibilidad'],
        ],
        'recepcion' => [
            ['label' => 'Inicio', 'route' => route('recepcion.dashboard'), 'icon' => 'fa-solid fa-house', 'permission' => 'consultar_agenda'],
            ['label' => 'Pacientes', 'route' => route('recepcion.patients'), 'icon' => 'fa-solid fa-user-injured', 'permission' => 'consultar_pacientes'],
            ['label' => 'Citas', 'route' => route('recepcion.appointments'), 'icon' => 'fa-solid fa-calendar-check', 'permission' => 'gestionar_citas'],
            ['label' => 'Agenda', 'route' => route('recepcion.agenda'), 'icon' => 'fa-solid fa-calendar-days', 'permission' => 'consultar_agenda'],
            ['label' => 'Horarios médicos', 'route' => route('recepcion.doctors'), 'icon' => 'fa-solid fa-clipboard-list', 'permission' => 'consultar_horarios_disponibles_medicos'],
        ],
    ];
    $menu = collect($navigation[$role] ?? $navigation['recepcion'])
        ->filter(fn (array $item) => $user?->hasPermission($item['permission']) ?? false)
        ->values();
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citamed</title>
    <script src="https://kit.fontawesome.com/bb9452ddb0.js" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    <div class="flex min-h-screen w-full bg-[#f4f7ff]">
        <aside class="w-72 shrink-0 bg-[#191346] p-5 text-white shadow-2xl shadow-slate-300/40">
            <div class="mb-8 flex items-center gap-3 px-2">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#223FAA] to-[#AC9FE2] text-lg font-bold text-white shadow-lg shadow-[#223FAA]/40">
                    C
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-[0.25em] text-slate-300">Clínica</p>
                    <h1 class="text-xl font-bold">Citamed</h1>
                </div>
            </div>

            <nav class="space-y-2">
                @foreach ($menu as $item)
                    @php
                        $isActive = request()->url() === $item['route'] || (str_contains(request()->path(), 'dashboard') && $item['label'] === 'Inicio');
                    @endphp
                    <a href="{{ $item['route'] }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition {{ $isActive ? 'bg-[#223FAA] text-white shadow-lg shadow-[#223FAA]/20' : 'text-slate-200 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 text-base"><i class="{{ $item['icon'] }}"></i></span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="flex-1 p-4 md:p-6 lg:p-8">
            <div class="min-h-full w-full rounded-[28px] bg-white p-5 shadow-[0_18px_40px_rgba(25,19,70,0.08)] ring-1 ring-[#edf2ff] md:p-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
