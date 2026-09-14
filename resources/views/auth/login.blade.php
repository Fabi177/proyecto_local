<x-guest-layout>
    <h1 class="absolute top-8 right-8 text-4xl font-extrabold text-gray-700">Login</h1>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-16">
        @csrf

        <div class="input-wrapper">
            <label for="email" class="input-label">{{ __('Email *') }}</label>

            <div class="input-field-container">
                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <x-text-input id="email" class="block w-full input-field" type="email" name="email" :value="old('email')" required autofocus placeholder="yourmail@website.c" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-600 text-xs" />
        </div>

        <div class="input-wrapper">
            <label for="password" class="input-label">{{ __('Password *') }}</label>

            <div class="input-field-container">
                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <x-text-input id="password" class="block w-full input-field" type="password" name="password" required autocomplete="current-password" placeholder="Password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-600 text-xs" />
        </div>

        <div class="block mt-6">
            {{-- ESTA ES LA LÍNEA QUE SE CORRIGIÓ --}}
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-500 hover:text-gray-700 rounded-md" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="ms-3 px-8 py-3 bg-[var(--primary-green)] hover:bg-green-600 focus:bg-green-700 active:bg-green-800 rounded-lg">
                {{ __('LOGIN') }}
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-sm text-center text-gray-600">
            ¿No tienes una cuenta?
            <a href="{{ route('register') }}" class="font-medium text-gray-600 hover:text-gray-800 underline">
                Regístrate aquí
            </a>
        </p>
    @endif
</x-guest-layout>
