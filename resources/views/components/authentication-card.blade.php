<div class="min-h-screen bg-[#f4f7ff] text-[#191346]">
    <header class="relative z-10 border-b border-white/10 bg-[#191346] text-white">
        <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8">
            <div class="flex items-center gap-4">
                {{ $logo }}
                <span class="hidden border-l border-white/20 pl-4 text-sm text-[#dfe5ff] sm:block">Sistema de gestión clínica</span>
            </div>
            <span class="text-right text-xs font-medium text-[#c0cfed] sm:text-sm">Atención segura y organizada</span>
        </div>
    </header>

    <div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-7xl lg:grid-cols-2">
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-[#191346] via-[#223FAA] to-[#191346] px-12 py-16 text-white lg:flex lg:flex-col xl:px-20">
            <div class="pointer-events-none absolute -right-28 top-16 h-80 w-80 rounded-full border border-white/10"></div>
            <div class="pointer-events-none absolute -right-12 top-32 h-48 w-48 rounded-full border border-white/10"></div>
            <div class="relative mt-auto max-w-lg pb-10">
                <div class="mb-7 flex h-14 w-14 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-2xl font-light text-white">+</div>
                <p class="mb-4 text-xs font-semibold uppercase tracking-[0.2em] text-[#c0cfed]">CitaMed · Gestión clínica</p>
                <h1 class="text-4xl font-bold leading-tight xl:text-5xl">Tu clínica, mejor conectada.</h1>
                <p class="mt-5 max-w-md text-base leading-7 text-white/80">Accede a la agenda, la atención y la información de pacientes desde un solo lugar.</p>
                <div class="mt-10 flex flex-wrap gap-2 text-xs font-medium text-white/90">
                    <span class="rounded-full border border-white/20 px-3 py-2">Agenda médica</span>
                    <span class="rounded-full border border-white/20 px-3 py-2">Atención coordinada</span>
                </div>
            </div>
        </aside>

        <main class="flex items-center justify-center px-4 py-10 sm:px-8 lg:px-12">
            <div class="w-full max-w-lg rounded-2xl border border-[#e5eaf5] bg-white px-6 py-8 shadow-[0_20px_55px_rgba(25,19,70,0.08)] sm:px-10 sm:py-10">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>
