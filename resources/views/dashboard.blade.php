@if (Auth::user()->role === 'comerciante')

<!-- ======================================================= -->
<!-- SI ES COMERCIANTE, CARGA EL LAYOUT DEL PANEL DE CONTROL -->
<!-- ======================================================= -->
<x-comerciante-layout>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Panel de Comerciante') }}
            </h2>

            {{-- Buscador en vivo de comercios (lógica en resources/js/buscador-comercios.js). Solo si tiene comercios. --}}
            @if (Auth::user()->comercios()->exists())
                <div class="relative w-full md:max-w-sm">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </div>
                    <input type="text"
                           x-model="$store.buscadorComercios.q"
                           @keydown.enter.prevent
                           autocomplete="off"
                           aria-label="Buscar entre mis comercios"
                           placeholder="Buscar mis comercios por nombre, rubro o dirección..."
                           class="block w-full rounded-lg border-gray-300 py-2 pl-10 pr-10 shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
                    <button type="button"
                            x-show="$store.buscadorComercios.q !== ''"
                            @click="$store.buscadorComercios.limpiar()"
                            style="display: none;"
                            aria-label="Borrar búsqueda"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
        </div>
    </x-slot>

    @include('dashboard.comerciante')

</x-comerciante-layout>


@else

<!-- ======================================================= -->
<!-- SI ES USUARIO, CARGA EL LAYOUT PÚBLICO (CON GRADIENTE) -->
<!-- ======================================================= -->
<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('¡Hola, ') . Auth::user()->name . '!' }}
        </h2>
    </x-slot>

    @include('dashboard.usuario')

</x-app-layout>


@endif
