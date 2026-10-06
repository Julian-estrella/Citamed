<x-app-layout>
    @php
        $user = Auth::user();
        $userName = $user ? $user->name : 'Usuario';
        $userRole = $user?->role ?? 'recepcion';
        $roleLabels = [
            'administrador' => 'Administrador',
            'medico' => 'Médico',
            'recepcion' => 'Recepción',
        ];
        $initials = collect(explode(' ', trim($userName)))
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="min-h-screen bg-[#f4f7ff] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl gap-6">
            <aside class="w-full max-w-[290px] rounded-[28px] bg-[#191346] p-6 text-white shadow-[0_20px_45px_rgba(25,19,70,0.25)]">
                <div class="mb-8 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#223FAA] to-[#AC9FE2] text-lg font-bold text-white shadow-lg shadow-[#223FAA]/30">
                        C
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-[#C0CFED]">Clinica</p>
                        <h2 class="text-xl font-bold">Citamed</h2>
                    </div>
                </div>

                <nav class="space-y-2">
                    @php
                        $menu = [
                            ['label' => 'Inicio', 'active' => true, 'icon' => 'home'],
                            ['label' => 'Pacientes', 'active' => false, 'icon' => 'users'],
                            ['label' => 'Médicos', 'active' => false, 'icon' => 'stethoscope'],
                            ['label' => 'Citas', 'active' => false, 'icon' => 'calendar'],
                            ['label' => 'Agenda', 'active' => false, 'icon' => 'clock'],
                        ];
                    @endphp

                    @foreach ($menu as $item)
                        <a href="#" class="group flex items-center justify-between rounded-2xl px-4 py-3 transition {{ $item['active'] ? 'bg-[#223FAA] text-white shadow-lg shadow-[#223FAA]/20' : 'text-[#dfe5ff] hover:bg-white/5 hover:text-white' }}">
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    @if ($item['icon'] === 'home')
                                        <path d="M3 11.5 12 4l9 7.5"></path>
                                        <path d="M5 10.5V20h14v-9.5"></path>
                                    @elseif ($item['icon'] === 'users')
                                        <path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1"></path>
                                        <circle cx="10" cy="7" r="3"></circle>
                                        <path d="M22 19v-1a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    @elseif ($item['icon'] === 'stethoscope')
                                        <path d="M6 3v7a3 3 0 1 0 6 0V3"></path>
                                        <path d="M6 6H3"></path>
                                        <path d="M12 6h3a2 2 0 0 1 2 2v4a4 4 0 0 1-8 0V8"></path>
                                        <path d="M18 10v1a4 4 0 1 1-8 0v-1"></path>
                                    @elseif ($item['icon'] === 'calendar')
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M8 2v4M16 2v4M3 10h18"></path>
                                    @else
                                        <path d="M12 6v6l4 2"></path>
                                        <circle cx="12" cy="12" r="9"></circle>
                                    @endif
                                </svg>
                                <span class="font-medium">{{ $item['label'] }}</span>
                            </span>
                            @if ($item['active'])
                                <span class="h-2.5 w-2.5 rounded-full bg-[#AAE2E2]"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="mt-10 rounded-2xl bg-white/5 p-4 backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-[0.25em] text-[#C0CFED]">Permisos</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#223FAA] px-2.5 py-1 text-xs font-medium text-white">Admin</span>
                        <span class="rounded-full bg-[#AC9FE2] px-2.5 py-1 text-xs font-medium text-[#191346]">Médico</span>
                        <span class="rounded-full bg-[#AAE2E2] px-2.5 py-1 text-xs font-medium text-[#191346]">Recepción</span>
                    </div>
                </div>
            </aside>

            <main class="flex-1 space-y-6">
                <header class="rounded-[28px] border border-[#e1e9ff] bg-white px-6 py-5 shadow-[0_10px_30px_rgba(34,63,170,0.08)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#223FAA]">Panel principal</p>
                            <h1 class="mt-2 text-3xl font-bold text-[#191346]">Hola, {{ $userName }}</h1>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <button type="button" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F0F4FF] text-[#223FAA] transition hover:bg-[#E7EEFF]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                                        <path d="M10 20a2 2 0 0 0 4 0"></path>
                                    </svg>
                                </button>
                                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#191346] px-1 text-[10px] font-bold text-white">3</span>
                            </div>

                            <div class="flex items-center gap-3 rounded-2xl border border-[#e7edff] bg-[#f8faff] px-3 py-2">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-[#223FAA] via-[#AC9FE2] to-[#AAE2E2] text-sm font-bold text-white shadow-md shadow-[#223FAA]/25">
                                    {{ $initials ?: 'US' }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#191346]">{{ $userName }}</p>
                                    <p class="text-xs text-[#5b678f]">{{ $roleLabels[$userRole] ?? 'Sin rol' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @php
                        $stats = [
                            ['label' => 'Pacientes', 'value' => '0', 'trend' => '0%', 'color' => 'bg-[#223FAA]'],
                            ['label' => 'Citas hoy', 'value' => '0', 'trend' => '0%', 'color' => 'bg-[#AC9FE2]'],
                            ['label' => 'Médicos', 'value' => '0', 'trend' => '0%', 'color' => 'bg-[#AAE2E2]'],
                            ['label' => 'Agenda', 'value' => '0%', 'trend' => 'OK', 'color' => 'bg-[#191346]'],
                        ];
                    @endphp

                    @foreach ($stats as $stat)
                        <article class="rounded-[24px] bg-white p-5 shadow-[0_10px_25px_rgba(25,19,70,0.06)] ring-1 ring-[#edf2ff]">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-[#5a678d]">{{ $stat['label'] }}</span>
                                <span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $stat['trend'] === 'OK' ? 'bg-[#EAFBF7] text-[#0f766e]' : 'bg-[#eef3ff] text-[#223FAA]' }}">{{ $stat['trend'] }}</span>
                            </div>
                            <div class="mt-4 flex items-end justify-between">
                                <div>
                                    <p class="text-3xl font-bold text-[#191346]">{{ $stat['value'] }}</p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $stat['color'] }} text-white shadow-lg shadow-[#223FAA]/10">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5v14"></path>
                                    </svg>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
                    <article class="rounded-[28px] bg-white p-6 shadow-[0_10px_25px_rgba(25,19,70,0.06)] ring-1 ring-[#edf2ff]">
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#5a678d]">Próximas citas</p>
                                <h2 class="text-xl font-bold text-[#191346]">Agenda del día</h2>
                            </div>
                            <button type="button" class="rounded-xl bg-[#223FAA] px-3 py-2 text-sm font-medium text-white shadow-md shadow-[#223FAA]/20 hover:bg-[#1a2d7e]">
                                Ver todo
                            </button>
                        </div>

                        <div class="space-y-4">
                            @php
                                $appointments = [
                                    ['time' => '08:30', 'patient' => 'Andrea López', 'doctor' => 'Dr. Ramírez', 'type' => 'Consulta general'],
                                    ['time' => '10:15', 'patient' => 'José García', 'doctor' => 'Dra. Silva', 'type' => 'Control cardiaco'],
                                    ['time' => '12:00', 'patient' => 'María Pérez', 'doctor' => 'Dr. Ortega', 'type' => 'Seguimiento'],
                                    ['time' => '15:40', 'patient' => 'Sofía Ruiz', 'doctor' => 'Dra. Cortés', 'type' => 'Dolor de espalda'],
                                ];
                            @endphp

                            @foreach ($appointments as $appointment)
                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-[#edf2ff] bg-[#f8faff] p-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#EAF0FF] text-sm font-bold text-[#223FAA]">
                                            {{ substr($appointment['patient'], 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-[#191346]">{{ $appointment['patient'] }}</p>
                                            <p class="text-sm text-[#5a678d]">{{ $appointment['doctor'] }} · {{ $appointment['type'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-[#223FAA]">{{ $appointment['time'] }}</p>
                                        <span class="mt-1 inline-flex rounded-full bg-[#EAFBF7] px-2 py-1 text-[10px] font-semibold text-[#0f766e]">Confirmada</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <aside class="space-y-6">
                        <article class="rounded-[28px] bg-[#191346] p-5 text-white shadow-[0_15px_30px_rgba(25,19,70,0.2)]">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold">Notificaciones</h3>
                                <span class="rounded-full bg-white/10 px-2 py-1 text-xs font-semibold">3 nuevas</span>
                            </div>

                            <div class="mt-4 space-y-3">
                                @php
                                    $notifications = [
                                        ['title' => 'Resultado listo', 'text' => 'Prueba de laboratorio disponible'],
                                        ['title' => 'Cita pendiente', 'text' => 'Paciente con revisión mañana'],
                                        ['title' => 'Inventario', 'text' => 'Faltan 4 materiales en almacén'],
                                    ];
                                @endphp

                                @foreach ($notifications as $notification)
                                    <div class="rounded-2xl bg-white/5 p-3">
                                        <p class="text-sm font-semibold">{{ $notification['title'] }}</p>
                                        <p class="mt-1 text-xs text-[#dfe5ff]">{{ $notification['text'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </article>

                        <article class="rounded-[28px] bg-white p-5 shadow-[0_10px_25px_rgba(25,19,70,0.06)] ring-1 ring-[#edf2ff]">
                            <p class="text-sm font-medium text-[#5a678d]">Accesos rápidos</p>
                            <div class="mt-4 grid gap-3">
                                <button type="button" class="rounded-2xl bg-[#223FAA] px-4 py-3 text-left text-sm font-semibold text-white shadow-md shadow-[#223FAA]/10 hover:bg-[#1b2d7c]">+ Nuevo paciente</button>
                                <button type="button" class="rounded-2xl bg-[#AC9FE2] px-4 py-3 text-left text-sm font-semibold text-[#191346] shadow-md shadow-[#AC9FE2]/20 hover:bg-[#9e8fe0]">+ Nueva cita</button>
                                <button type="button" class="rounded-2xl bg-[#AAE2E2] px-4 py-3 text-left text-sm font-semibold text-[#191346] shadow-md shadow-[#AAE2E2]/20 hover:bg-[#8cd9d9]">Ver agenda</button>
                            </div>
                        </article>
                    </aside>
                </section>
            </main>
        </div>
    </div>
</x-app-layout>
