{{--
    Campo "Dirección" con sugerencias mientras se escribe (alta y edición del comercio).
    Variable: $valor (texto inicial). La lógica está en resources/js/autocompletado-direccion.js.
    Al elegir una sugerencia, el mapa se abre en ese punto para que el comerciante lo confirme.
--}}
<div class="input-wrapper" x-data="autocompletadoDireccion()" x-ref="caja" @click.outside="cerrar()">
    <label for="direccion" class="input-label">{{ __('Dirección *') }}</label>
    <div class="input-field-container relative" x-ref="campo">
        <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
        <x-text-input id="direccion" class="w-full input-field" type="text" name="direccion" :value="$valor" required
            autocomplete="off" role="combobox" aria-autocomplete="list" aria-haspopup="listbox"
            x-on:input="escribir($event.target.value)"
            x-on:keydown.arrow-down.prevent="mover(1)"
            x-on:keydown.arrow-up.prevent="mover(-1)"
            x-on:keydown.enter="elegirActivo($event)"
            x-on:keydown.escape="cerrar()" />

        <ul x-show="abierto" style="display: none;" role="listbox" aria-label="Sugerencias de dirección"
            class="absolute left-0 right-0 top-full z-30 mt-1 max-h-72 overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-xl">
            <template x-for="(item, i) in items" :key="i">
                <li role="option" :aria-selected="i === activo">
                    <button type="button" @mousedown.prevent="elegir(item)"
                            :class="i === activo ? 'bg-green-50' : ''"
                            class="flex w-full items-start gap-2 px-3 py-2 text-left text-sm text-gray-800 hover:bg-green-50">
                        <span aria-hidden="true">📍</span><span x-text="item.texto"></span>
                    </button>
                </li>
            </template>
        </ul>
    </div>
    <p class="mt-1 text-xs text-gray-500" x-show="aviso" x-text="aviso" style="display: none;"></p>
    <p class="mt-1 text-xs text-gray-500">Empezá a escribir y elegí tu dirección de la lista: te mostramos el punto en el mapa para que lo confirmes.</p>
</div>