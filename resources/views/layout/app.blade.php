@php
    $user = Auth::user();
    $role = $user?->role ?? 'recepcion';
    $navigation = [
        'administrador' => [
            ['label' => 'Inicio', 'route' => route('admin.panel'), 'icon' => 'fa-solid fa-house', 'permission' => 'visualizar_panel_principal'],
            ['label' => 'Gestión de usuarios', 'route' => route('admin.users'), 'icon' => 'fa-solid fa-users', 'permission' => 'gestionar_usuarios'],
            ['label' => 'Pacientes', 'route' => route('admin.patients'), 'icon' => 'fa-solid fa-user-injured', 'permission' => 'gestionar_pacientes'],
            ['label' => 'Médicos', 'route' => route('admin.doctors'), 'icon' => 'fa-solid fa-user-doctor', 'permission' => 'gestionar_medicos'],
            ['label' => 'Consultorios', 'route' => route('admin.consultorios'), 'icon' => 'fa-solid fa-door-open', 'permission' => 'gestionar_medicos'],
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
            ['label' => 'Consultorios', 'route' => route('recepcion.consultorios'), 'icon' => 'fa-solid fa-door-open', 'permission' => 'gestionar_medicos'],
        ],
    ];
    $menu = collect($navigation[$role] ?? $navigation['recepcion'])
        ->filter(fn (array $item) => $user?->hasPermission($item['permission']) ?? false)
        ->values();
    $roleLabel = match ($role) {
        'administrador' => 'Administrador',
        'medico' => 'Médico',
        'recepcion' => 'Recepción',
        default => 'Usuario',
    };
    $workspaceTheme = ['canvas' => 'bg-white', 'surface' => 'bg-[#e8ebf4]', 'ring' => 'ring-[#D7DCEC]'];
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
<body class="bg-white text-slate-800 antialiased">
    <div class="flex min-h-screen w-full items-start bg-white">
        <aside class="sticky top-0 z-30 flex h-screen w-[4.5rem] shrink-0 flex-col overflow-y-auto bg-[#191346] p-2 text-white shadow-2xl shadow-slate-300/40 md:w-72 md:p-5">
            <div class="mb-8 flex items-center justify-center gap-3 px-1 md:justify-start md:px-2">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#223FAA] to-[#AC9FE2] text-lg font-bold text-white shadow-lg shadow-[#223FAA]/40">
                    C
                </div>
                <div class="hidden md:block">
                    <p class="text-[10px] uppercase tracking-[0.25em] text-slate-300">Clínica</p>
                    <h1 class="text-xl font-bold">Citamed</h1>
                </div>
            </div>

            <nav class="space-y-2">
                @foreach ($menu as $item)
                    @php
                        $isActive = request()->url() === $item['route'] || (str_contains(request()->path(), 'dashboard') && $item['label'] === 'Inicio');
                    @endphp
                    <a href="{{ $item['route'] }}" aria-label="{{ $item['label'] }}" class="flex items-center justify-center gap-3 rounded-2xl px-2 py-3 text-sm font-medium transition {{ $isActive ? 'bg-[#223FAA] text-white shadow-lg shadow-[#223FAA]/20' : 'text-slate-200 hover:bg-white/5 hover:text-white' }} md:justify-start md:px-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 text-base"><i class="{{ $item['icon'] }}"></i></span>
                        <span class="sr-only md:not-sr-only">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="min-w-0 flex-1 p-2 md:p-6 lg:p-8">
            <div class="min-h-full w-full rounded-2xl {{ $workspaceTheme['surface'] }} p-3 shadow-[0_18px_40px_rgba(25,19,70,0.08)] ring-1 {{ $workspaceTheme['ring'] }} md:rounded-[28px] md:p-8">
                <div class="sticky top-0 z-40 -mx-3 -mt-3 mb-6 flex flex-wrap items-center justify-end gap-3 rounded-t-2xl border-b border-slate-300/60 bg-[#e8ebf4]/95 px-3 pb-4 pt-3 backdrop-blur-sm md:-mx-8 md:-mt-8 md:rounded-t-[28px] md:px-8 md:pt-5">
                    <div class="flex items-center gap-3 rounded-xl border border-white/80 bg-white/70 px-3 py-2.5 text-slate-700">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#AAE2E2]/50 text-[#176B70]">
                            <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-semibold uppercase text-slate-500">Hoy</p>
                            <time datetime="{{ now()->toDateString() }}" class="text-sm font-semibold capitalize">
                                {{ now()->locale('es')->translatedFormat('l, j \\d\\e F Y') }}
                            </time>
                        </div>
                    </div>
                    <x-user-profile-menu :user="$user" :user-name="$user?->name ?? 'Usuario'" :role-label="$roleLabel" />
                </div>
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
