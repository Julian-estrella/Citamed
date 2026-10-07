@php
    $user = Auth::user();
    $role = $user?->role ?? 'recepcion';
    $navigation = [
        'administrador' => [
            ['label' => 'Inicio', 'route' => route('admin.panel'), 'icon' => '⌂'],
            ['label' => 'Panel principal', 'route' => route('admin.panel'), 'icon' => '▣'],
            ['label' => 'Gestión de usuarios', 'route' => route('admin.users'), 'icon' => '👥'],
            ['label' => 'Pacientes', 'route' => '#', 'icon' => '🩺'],
            ['label' => 'Médicos', 'route' => '#', 'icon' => '👨‍⚕️'],
            ['label' => 'Citas', 'route' => '#', 'icon' => '📅'],
            ['label' => 'Agenda', 'route' => '#', 'icon' => '🕒'],
        ],
        'medico' => [
            ['label' => 'Inicio', 'route' => route('medico.dashboard'), 'icon' => '⌂'],
            ['label' => 'Mis citas', 'route' => '#', 'icon' => '📅'],
            ['label' => 'Pacientes', 'route' => '#', 'icon' => '🩺'],
            ['label' => 'Horario', 'route' => '#', 'icon' => '🕘'],
            ['label' => 'Disponibilidad', 'route' => '#', 'icon' => '✅'],
        ],
        'recepcion' => [
            ['label' => 'Inicio', 'route' => route('recepcion.dashboard'), 'icon' => '⌂'],
            ['label' => 'Pacientes', 'route' => '#', 'icon' => '🩺'],
            ['label' => 'Citas', 'route' => '#', 'icon' => '📅'],
            ['label' => 'Agenda', 'route' => '#', 'icon' => '🕒'],
            ['label' => 'Horarios médicos', 'route' => '#', 'icon' => '🗓️'],
        ],
    ];
    $menu = $navigation[$role] ?? $navigation['recepcion'];
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citamed</title>
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
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/5 text-base">{{ $item['icon'] }}</span>
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
