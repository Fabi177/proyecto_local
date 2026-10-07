{{--
    Ubicación del comercio: UN solo selector para dirección, localidad (con código postal) y mapa.

    Se incluye en comercios/create y comercios/edit, DENTRO del <form>. Variables: $localidades
    (id, nombre, codigo_postal) y, solo en la edición, $comercio.
    Envía cuatro campos ocultos: "direccion", "localidad_id", "latitud" y "longitud".
    La dirección es lo que se escribe (o se elige de la lista) en el buscador de la ventana; la localidad
    se completa sola al elegir una dirección y también se puede elegir a mano.
    La lógica está en resources/js/mapa-comercio.js (componente Alpine "selectorUbicacion").

    IMPORTANTE: todos los <button> de este archivo llevan type="button" para que
    no envíen el formulario al presionarlos.
--}}
@php
    $dirInicial = old('direccion', isset($comercio) ? $comercio->direccion : '');
    $locInicial = old('localidad_id', isset($comercio) ? $comercio->localidad_id : null);
    $latInicial = old('latitud', isset($comercio) ? $comercio->latitud : null);
    $lngInicial = old('longitud', isset($comercio) ? $comercio->longitud : null);
@endphp

<div class="input-wrapper"
     x-data="selectorUbicacion({
         lat: @js($latInicial), lng: @js($lngInicial),
         direccion: @js($dirInicial), localidadId: @js($locInicial),
         localidades: @js($localidades)
     })">

    <span class="input-label">{{ __('Ubicación del comercio *') }}</span>

    <input type="hidden" name="direccion" value="{{ $dirInicial }}" :value="direccion">
    <input type="hidden" name="localidad_id" value="{{ $locInicial }}" :value="localidadId">
    <input type="hidden" name="latitud" value="{{ $latInicial }}" :value="lat ?? ''">
    <input type="hidden" name="longitud" value="{{ $lngInicial }}" :value="lng ?? ''">

    {{-- Resumen de lo elegido --}}
    <div class="mt-2 rounded-lg border px-4 py-3" :class="completo ? 'border-green-300 bg-green-50' : 'border-gray-300 bg-gray-50'">
        <p class="text-sm text-gray-500" x-show="!direccion" @if ($dirInicial) style="display: none;" @endif>
            Todavía no elegiste la ubicación de tu comercio.
        </p>
        <div x-show="direccion" @if (! $dirInicial) style="display: none;" @endif>
            <p class="font-semibold text-gray-800" x-text="direccion">{{ $dirInicial }}</p>
            <p class="text-sm text-gray-600" x-text="etiquetaLocalidad">
                @if ($locInicial){{ optional($localidades->firstWhere('id', (int) $locInicial))->etiqueta }}@endif
            </p>
            <p class="mt-1 text-xs text-green-700" x-show="lat !== null" @if (! $latInicial) style="display: none;" @endif>
                📍 Ubicación marcada en el mapa. Se guarda al enviar el formulario.
            </p>
            <p class="mt-1 text-xs text-amber-700" x-show="lat === null" @if ($latInicial) style="display: none;" @endif>
                Falta marcar el punto en el mapa: tocá "Cambiar ubicación".
            </p>
        </div>
    </div>

    @error('direccion') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    @error('localidad_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

    <div class="mt-3">
        <button type="button"
                x-on:click="abrir()"
                class="inline-flex items-center px-4 py-2 bg-[var(--light-blue)] text-white text-sm font-semibold rounded-lg shadow transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--light-blue)]">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
            <span x-text="direccion ? 'Cambiar ubicación' : 'Elegir ubicación'">{{ $dirInicial ? 'Cambiar ubicación' : 'Elegir ubicación' }}</span>
        </button>
    </div>

    {{-- ============ VENTANA EMERGENTE: buscador + mapa + dirección + localidad ============ --}}
    <x-modal name="selector-ubicacion" maxWidth="2xl">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-800">Elegí la ubicación de tu comercio</h3>
            <p class="mt-1 text-sm text-gray-600">
                Buscá tu dirección y elegila de la lista, o hacé clic en el mapa. Podés arrastrar el marcador para ajustar el punto exacto.
            </p>

            {{-- Buscador con sugerencias (por encima del mapa) + botón para ubicar lo escrito en el mapa --}}
            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-stretch">
            <div class="relative z-20 min-w-0 flex-1" x-on:click.outside="abierto = false">
                <div class="flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 shadow-sm focus-within:border-[var(--primary-green)] focus-within:ring-1 focus-within:ring-[var(--primary-green)]">
                    <svg class="h-5 w-5 flex-none text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="text" x-model="q"
                           x-on:input="escribir()"
                           x-on:keydown.arrow-down.prevent="mover(1)"
                           x-on:keydown.arrow-up.prevent="mover(-1)"
                           x-on:keydown.enter="elegirActivo($event)"
                           x-on:keydown.escape="abierto = false"
                           maxlength="255"
                           autocomplete="off" role="combobox" aria-autocomplete="list" aria-label="Buscar dirección"
                           placeholder="Buscar dirección (ej: Av. Uruguay 1200, Posadas)"
                           class="w-full border-0 bg-transparent py-3 text-sm focus:ring-0">
                    <button type="button" x-show="q" x-on:click="limpiarBusqueda()" style="display: none;" class="flex-none text-xl leading-none text-gray-400 hover:text-gray-600" aria-label="Borrar búsqueda">&times;</button>
                </div>

                <ul x-show="abierto" style="display: none;" role="listbox" aria-label="Sugerencias de dirección"
                    class="absolute left-0 right-0 top-full mt-1 max-h-64 overflow-auto rounded-xl border border-gray-200 bg-white py-1 shadow-xl">
                    <template x-for="(item, i) in items" :key="i">
                        <li role="option" :aria-selected="i === activo">
                            <button type="button" x-on:mousedown.prevent="elegir(item)"
                                    :class="i === activo ? 'bg-green-50' : ''"
                                    class="flex w-full items-start gap-2 px-4 py-2 text-left text-sm text-gray-800 hover:bg-green-50">
                                <span aria-hidden="true">📍</span><span x-text="item.texto"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <button type="button" x-on:click="ubicar()" :disabled="ubicando"
                    class="flex-none rounded-full bg-[var(--primary-green)] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-[var(--primary-green)] focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                <span x-text="ubicando ? 'Buscando...' : 'Ubicar en el mapa'">Ubicar en el mapa</span>
            </button>
            </div>
            <p class="mt-1 text-xs text-gray-500" x-show="aviso" x-text="aviso" style="display: none;"></p>
            <p class="mt-1 text-xs text-amber-700" x-show="faltaNumero" style="display: none;">
                Si tu dirección tiene número, agregalo al final (por ejemplo: Av. Uruguay 1200).
            </p>

            <button type="button" x-on:click="usarMiUbicacion()" class="mt-2 text-sm font-medium text-[var(--light-blue)] hover:underline">
                Usar mi ubicación actual
            </button>

            {{-- "relative z-0" crea su propio contexto de apilamiento para que los controles de Leaflet no queden por encima de otros elementos --}}
            <div id="mapa-selector-ubicacion" class="relative z-0 mt-3 h-72 w-full rounded-lg border border-gray-300"></div>

            <p class="mt-2 rounded-md border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-900" x-show="tmpLat !== null" style="display: none;" data-confirmar-ubicacion>
                📍 <strong>¿Es acá tu comercio?</strong> Si no es exacto, arrastrá el marcador o hacé clic en el punto correcto.
            </p>

            {{-- Localidad: se completa sola al elegir una dirección de la lista, pero se puede elegir a mano --}}
            <div class="mt-4">
                <label for="ubicacion-localidad" class="block text-sm font-medium text-gray-700">Localidad (código postal) *</label>
                <select id="ubicacion-localidad" x-model="tmpLoc" x-on:change="localidadManual()"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
                    <option value="" disabled {{ $locInicial ? '' : 'selected' }}>Selecciona la localidad...</option>
                    @foreach ($localidades as $localidad)
                        <option value="{{ $localidad->id }}" {{ (string) $locInicial === (string) $localidad->id ? 'selected' : '' }}>{{ $localidad->nombre }} ({{ $localidad->codigo_postal }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs"
                   :class="locEstado === 'auto' ? 'text-green-700' : (locEstado === 'no-encontrada' ? 'text-amber-700' : 'text-gray-500')"
                   x-text="textoLocalidad">Se completa sola al elegir una dirección de la lista; también la podés elegir a mano.</p>
            </div>

            <p class="mt-2 text-sm text-red-600" x-show="mensaje" x-text="mensaje" style="display: none;"></p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="cancelar()" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg transition hover:bg-gray-200">
                    Volver
                </button>
                <button type="button" x-on:click="confirmar()" :disabled="!puedeConfirmar"
                        class="px-4 py-2 bg-[var(--primary-green)] text-white text-sm font-semibold rounded-lg transition hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Confirmar ubicación
                </button>
            </div>
        </div>
    </x-modal>
</div>
