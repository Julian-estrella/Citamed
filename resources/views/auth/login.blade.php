<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#223FAA]">Acceso a tu cuenta</p>
            <h1 class="mt-2 text-3xl font-bold text-[#191346]">Bienvenido de nuevo</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Ingresa tus datos para continuar con tu jornada.</p>
        </div>

        <x-validation-errors class="mb-5" />

        @session('status')
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="Correo electrónico" class="text-sm font-semibold text-[#191346]" />
                <x-input id="email" class="mt-2 block w-full rounded-xl border-slate-200 bg-[#f7f9fe] px-4 py-3 text-sm shadow-none focus:!border-[#223FAA] focus:!ring-[#223FAA]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nombre@clinica.com" />
            </div>

            <div class="mt-5">
                <x-label for="password" value="Contraseña" class="text-sm font-semibold text-[#191346]" />
                <x-input id="password" class="mt-2 block w-full rounded-xl border-slate-200 bg-[#f7f9fe] px-4 py-3 text-sm shadow-none focus:!border-[#223FAA] focus:!ring-[#223FAA]" type="password" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" />
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <label for="remember_me" class="flex items-center gap-2">
                    <x-checkbox id="remember_me" name="remember" class="rounded border-slate-300 text-[#223FAA] shadow-sm focus:ring-[#223FAA]" />
                    <span class="text-sm text-slate-600">Recordarme</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-[#223FAA] underline decoration-[#223FAA]/40 underline-offset-4 hover:text-[#191346]" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <div class="mt-7">
                <x-button class="!w-full !justify-center !rounded-xl !bg-[#223FAA] px-5 py-3 text-sm font-semibold normal-case !tracking-normal shadow-lg shadow-[#223FAA]/20 transition hover:!bg-[#191346] focus:!ring-[#223FAA]">
                    Iniciar sesión
                </x-button>
            </div>
        </form>

        @if (Route::has('register'))
            <p class="mt-7 border-t border-slate-200 pt-6 text-center text-sm text-slate-600">
                ¿Aún no tienes una cuenta?
                <a class="font-semibold text-[#223FAA] underline decoration-[#223FAA]/40 underline-offset-4 hover:text-[#191346]" href="{{ route('register') }}">Crear cuenta</a>
            </p>
        @endif
    </x-authentication-card>
</x-guest-layout>
