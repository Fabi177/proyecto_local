{{--
    Selector de ubicación (geolocalización) del comercio.

    Se incluye en comercios/create y comercios/edit, DENTRO del <form>.
    Envía dos campos ocultos ("latitud" y "longitud") junto con el resto del formulario.
    La lógica está en resources/js/mapa-comercio.js (componente Alpine "selectorUbicacion").

    IMPORTANTE: todos los <button> de este archivo llevan type="button" para que
    no envíen el formulario al presionarlos.
--}}
@php
    $latInicial = old('latitud', isset($comercio) ? $comercio->latitud : null);
    $lngInicial = old('longitud', isset($comercio) ? $comercio->longitud : null);
@endphp

<div class="input-wrapper"
     x-data="selectorUbicacion({ lat: @js($latInicial), lng: @js($lngInicial) })">

    <span class="input-label">{{ __('Ubicación en el mapa') }}</span>

    <input type="hidden" name="latitud" :value="lat ?? ''">
    <input type="hidden" name="longitud" :value="lng ?? ''">

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <button type="button"
                x-on:click="abrir()"
                class="inline-flex items-center px-4 py-2 bg-[var(--light-blue)] text-white text-sm font-semibold rounded-lg shadow transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--light-blue)]">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
            <span x-text="lat === null ? 'Marcar ubicación en el mapa' : 'Cambiar ubicación'">Marcar ubicación en el mapa</span>
        </button>

        <button type="button"
                x-show="lat !== null"
                x-on:click="quitar()"
                style="display: none;"
                class="text-sm font-medium text-red-600 hover:text-red-800 hover:underline">
            Quitar ubicación
        </button>
    </div>

    <p class="mt-2 text-sm"
       :class="lat === null ? 'text-gray-500' : 'text-green-700'"
       x-text="resumen">
        Todavía no marcaste la ubicación de tu comercio.
    </p>

    {{-- ============ VENTANA EMERGENTE CON EL MAPA ============ --}}
    <x-modal name="selector-ubicacion" maxWidth="2xl">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-800">Ubicación de tu comercio</h3>
            <p class="mt-1 text-sm text-gray-600">
                Hacé clic en el mapa para colocar el marcador. Podés arrastrarlo para ajustar la posición exacta.
            </p>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <input type="text"
                       x-model="busqueda"
                       x-on:keydown.enter.prevent="buscar()"
                       placeholder="Buscar dirección (ej: Av. San Martín 123, Ciudad)"
                       class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
                <button type="button"
                        x-on:click="buscar()"
                        :disabled="buscando"
                        class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-md transition hover:bg-gray-700 disabled:opacity-50">
                    <span x-text="buscando ? 'Buscando...' : 'Buscar'">Buscar</span>
                </button>
            </div>

            <button type="button"
                    x-on:click="usarMiUbicacion()"
                    class="mt-2 text-sm font-medium text-[var(--light-blue)] hover:underline">
                Usar mi ubicación actual
            </button>

            {{-- "relative z-0" crea su propio contexto de apilamiento para que los controles de Leaflet no queden por encima de otros elementos --}}
            <div id="mapa-selector-ubicacion" class="relative z-0 mt-3 h-80 w-full rounded-lg border border-gray-300"></div>

            <p class="mt-2 text-sm text-red-600" x-show="mensaje" x-text="mensaje" style="display: none;"></p>

            <p class="mt-2 text-xs text-gray-500">
                Coordenadas seleccionadas: <span class="font-mono" x-text="seleccion">ninguna</span>
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        x-on:click="cancelar()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg transition hover:bg-gray-200">
                    Cancelar
                </button>
                <button type="button"
                        x-on:click="confirmar()"
                        :disabled="tmpLat === null"
                        class="px-4 py-2 bg-[var(--primary-green)] text-white text-sm font-semibold rounded-lg transition hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Confirmar ubicación
                </button>
            </div>
        </div>
    </x-modal>
</div>
