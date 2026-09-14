<x-guest-layout>
    <h1 class="absolute top-8 right-8 text-4xl font-extrabold text-gray-700">Register</h1>

    <form method="POST" action="{{ route('register') }}" class="mt-16">
        @csrf

        <div class="input-wrapper">
            <label for="name" class="input-label">{{ __('Name *') }}</label>
            <div class="input-field-container">
                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <x-text-input id="name" class="block w-full input-field" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Tu nombre completo" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-red-600 text-xs" />
        </div>


        <div class="input-wrapper">
            <label class="input-label">{{ __('Quiero registrarme como: *') }}</label>

            <div class="flex flex-col sm:flex-row gap-4 mt-2">

                <label for="role_usuario" class="flex-1 p-4 border-2 rounded-lg cursor-pointer transition-all duration-200
                                              has-[:checked]:bg-blue-50 has-[:checked]:border-[var(--light-blue)] has-[:checked]:ring-2 has-[:checked]:ring-[var(--light-blue)]/30">
                    <input type="radio" id="role_usuario" name="role" value="usuario" class="hidden"
                           {{ old('role', 'usuario') == 'usuario' ? 'checked' : '' }}>

                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-2 text-[var(--light-blue)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span class="font-semibold text-gray-700">Usuario</span>
                    </span>
                    <p class="text-sm text-gray-500 mt-1 ml-8">Busco comercios y servicios.</p>
                </label>

                <label for="role_comerciante" class="flex-1 p-4 border-2 rounded-lg cursor-pointer transition-all duration-200
                                                 has-[:checked]:bg-green-50 has-[:checked]:border-[var(--primary-green)] has-[:checked]:ring-2 has-[:checked]:ring-[var(--primary-green)]/30">
                    <input type="radio" id="role_comerciante" name="role" value="comerciante" class="hidden"
                           {{ old('role') == 'comerciante' ? 'checked' : '' }}>

                    <span class="flex items-center">
                        <svg class="w-6 h-6 mr-2 text-[var(--primary-green)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5m-3 0V21m0-11.422L2.25 10.5 12 5.25l9.75 5.25-9.75 5.25Zm0 0V21m0-4.5H9.75M12 9V3M12 9l3-1.5M12 9l-3-1.5M12 9l3 1.5M12 9l-3 1.5M12 9V3M12 9l3 1.5" />
                        </svg>
                        <span class="font-semibold text-gray-700">Comerciante</span>
                    </span>
                    <p class="text-sm text-gray-500 mt-1 ml-8">Quiero registrar mi negocio.</p>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-1 text-red-600 text-xs" />
        </div>
        <div class="input-wrapper">
            <label for="email" class="input-label">{{ __('Email *') }}</label>
            <div class="input-field-container">
                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                <x-text-input id="email" class="block w-full input-field" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="tuemail@dominio.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-600 text-xs" />
        </div>

        <div class="input-wrapper">
            <label for="password" class="input-label">{{ __('Password *') }}</label>
            <div class="input-field-container">
                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <x-text-input id="password" class="block w-full input-field" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-600 text-xs" />
        </div>

        <div class="input-wrapper">
            <label for="password_confirmation" class="input-label">{{ __('Confirm Password *') }}</label>
            <div class="input-field-container">
                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <x-text-input id="password_confirmation" class="block w-full input-field" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu contraseña" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-red-600 text-xs" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="text-sm text-gray-500 hover:text-gray-700 rounded-md" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4 px-8 py-3 bg-[var(--primary-green)] hover:bg-green-600 focus:bg-green-700 active:bg-green-800 rounded-lg">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
