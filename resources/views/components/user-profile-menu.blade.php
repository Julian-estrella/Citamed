@props(['user', 'userName', 'roleLabel'])

<details class="group relative shrink-0">
    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-2 py-1 text-left transition hover:bg-slate-200/70 [&::-webkit-details-marker]:hidden" aria-label="Menú de {{ $userName }}">
        <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#223FAA] via-[#AC9FE2] to-[#AAE2E2] text-sm font-bold text-white">
            <img src="{{ $user->profile_photo_url }}" alt="{{ $userName }}" class="h-full w-full object-cover" />
        </div>
        <div class="max-w-44 text-sm text-slate-700">
            <p class="truncate font-semibold">{{ $userName }}</p>
            <p class="text-xs text-slate-500">{{ $roleLabel }}</p>
        </div>
        <i class="fa-solid fa-chevron-down text-[10px] text-slate-500 transition group-open:rotate-180"></i>
    </summary>

    <div class="absolute right-0 z-30 mt-3 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-300/40">
        <div class="border-b border-slate-100 px-4 py-3">
            <p class="text-sm font-semibold text-slate-800">{{ $userName }}</p>
            <p class="text-xs text-slate-500">{{ $roleLabel }}</p>
        </div>

        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">
            <i class="fa-solid fa-user-pen text-[#223FAA]"></i>
            Modificar usuario
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm text-red-600 transition hover:bg-red-50">
                <i class="fa-solid fa-right-from-bracket"></i>
                Cerrar sesión
            </button>
        </form>
    </div>
</details>