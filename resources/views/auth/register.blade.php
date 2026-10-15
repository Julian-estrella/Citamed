<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-7">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#223FAA]">Registro de acceso</p>
            <h1 class="mt-2 text-3xl font-bold text-[#191346]">Crea tu cuenta</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Completa tus datos para acceder a CitaMed.</p>
        </div>

        <x-validation-errors class="mb-5" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="Nombre completo" class="text-sm font-semibold text-[#191346]" />
                <x-input id="name" class="mt-2 block w-full rounded-xl border-slate-200 bg-[#f7f9fe] px-4 py-3 text-sm shadow-none focus:!border-[#223FAA] focus:!ring-[#223FAA]" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Tu nombre y apellidos" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="Correo electrónico" class="text-sm font-semibold text-[#191346]" />
                <x-input id="email" class="mt-2 block w-full rounded-xl border-slate-200 bg-[#f7f9fe] px-4 py-3 text-sm shadow-none focus:!border-[#223FAA] focus:!ring-[#223FAA]" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nombre@clinica.com" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="Contraseña" class="text-sm font-semibold text-[#191346]" />
                <x-input id="password" class="mt-2 block w-full rounded-xl border-slate-200 bg-[#f7f9fe] px-4 py-3 text-sm shadow-none focus:!border-[#223FAA] focus:!ring-[#223FAA]" type="password" name="password" minlength="8" maxlength="12" required autocomplete="new-password" />
                <p class="mt-2 text-xs leading-5 text-slate-500">Entre 8 y 12 caracteres, con mayúscula, minúscula, número y símbolo.</p>
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="Confirmar contraseña" class="text-sm font-semibold text-[#191346]" />
                <x-input id="password_confirmation" class="mt-2 block w-full rounded-xl border-slate-200 bg-[#f7f9fe] px-4 py-3 text-sm shadow-none focus:!border-[#223FAA] focus:!ring-[#223FAA]" type="password" name="password_confirmation" minlength="8" maxlength="12" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <label for="terms" class="flex items-start gap-3 text-sm leading-6 text-slate-600">
                        <x-checkbox name="terms" id="terms" required class="mt-1 rounded border-slate-300 text-[#223FAA] shadow-sm focus:ring-[#223FAA]" />
                        <span>
                            Acepto los
                            <a target="_blank" href="{{ route('terms.show') }}" class="font-semibold text-[#223FAA] underline underline-offset-4">Términos de servicio</a>
                            y el
                            <a target="_blank" href="{{ route('policy.show') }}" class="font-semibold text-[#223FAA] underline underline-offset-4">Aviso de privacidad</a>.
                        </span>
                    </label>
                </div>
            @endif

            <div class="mt-6">
                <x-button class="!w-full !justify-center !rounded-xl !bg-[#223FAA] px-5 py-3 text-sm font-semibold normal-case !tracking-normal shadow-lg shadow-[#223FAA]/20 transition hover:!bg-[#191346] focus:!ring-[#223FAA]">
                    Crear cuenta
                </x-button>
            </div>
        </form>

        <p class="mt-6 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">
            ¿Ya tienes una cuenta?
            <a class="font-semibold text-[#223FAA] underline decoration-[#223FAA]/40 underline-offset-4 hover:text-[#191346]" href="{{ route('login') }}">Inicia sesión</a>
        </p>
    </x-authentication-card>
</x-guest-layout>
