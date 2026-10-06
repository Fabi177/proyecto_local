{{--
    Lista desplegable de sugerencias del selector de ciudad / código postal.
    Se incluye DENTRO de un contenedor "relative" que tenga
    x-data="autocompletadoLocalidades({ url: @js(route('localidades.sugerencias')), id: ..., texto: ... })".
    La lógica está en resources/js/autocompletado-localidades.js.
    Todo el texto se imprime con x-text (nunca como HTML).
--}}
<ul x-show="abierto"
    style="display: none;"
    role="listbox"
    :id="listaId"
    aria-label="Sugerencias de ciudad o código postal"
    class="absolute left-0 right-0 top-full z-50 mt-1 max-h-96 overflow-y-auto rounded-lg border border-gray-200 bg-white text-left shadow-xl">

    <template x-for="(item, i) in items" :key="item.id">
        <li role="option" :id="idOpcion(i)" :aria-selected="i === activo ? 'true' : 'false'" @mouseenter="activo = i">
            <button type="button"
                    @click="elegir(item)"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-gray-800"
                    :class="i === activo ? 'bg-gray-100' : 'hover:bg-gray-50'">
                <span class="min-w-0">
                    <span class="block truncate font-semibold" x-text="item.nombre"></span>
                    <span class="block truncate text-sm text-gray-500" x-text="[item.codigo_postal, item.provincia].filter(Boolean).join(' · ')"></span>
                </span>
                <span class="flex-shrink-0 text-sm text-gray-500"
                      x-text="item.comercios + (item.comercios === 1 ? ' comercio' : ' comercios')"></span>
            </button>
        </li>
    </template>

    <li x-show="items.length === 0" class="px-4 py-3 text-sm text-gray-500">
        No encontramos esa ciudad o código postal con comercios.
    </li>
</ul>
