{{--
    Lista desplegable de sugerencias del buscador (autocompletado).
    Se incluye DENTRO de un contenedor "relative" que tenga
    x-data="autocompletadoComercios({ url: @js(route('comercios.sugerencias')) })".
    La lógica está en resources/js/autocompletado-comercios.js.
    Todo el texto se imprime con x-text (nunca como HTML).
--}}
<ul x-show="abierto"
    style="display: none;"
    role="listbox"
    :id="listaId"
    aria-label="Sugerencias de comercios"
    class="absolute left-0 right-0 top-full z-50 mt-1 max-h-96 overflow-y-auto rounded-lg border border-gray-200 bg-white text-left shadow-xl">

    <template x-for="(item, i) in items" :key="item.id">
        <li role="option" :id="idOpcion(i)" :aria-selected="i === activo ? 'true' : 'false'" @mouseenter="activo = i">
            <a :href="item.url"
               class="flex items-center gap-3 px-4 py-3 text-gray-800"
               :class="i === activo ? 'bg-gray-100' : 'hover:bg-gray-50'">

                <template x-if="item.logo">
                    <img :src="item.logo" alt="" class="h-10 w-10 flex-shrink-0 rounded border border-gray-200 bg-white object-contain">
                </template>
                <template x-if="!item.logo">
                    <span aria-hidden="true"
                          class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded bg-gray-100 text-sm font-bold text-gray-500"
                          x-text="String(item.nombre || '?').charAt(0).toUpperCase()"></span>
                </template>

                <span class="min-w-0">
                    <span class="block truncate font-semibold" x-text="item.nombre"></span>
                    <span class="block truncate text-sm text-gray-500" x-text="[item.rubro, item.direccion].filter(Boolean).join(' · ')"></span>
                </span>
            </a>
        </li>
    </template>

    <li x-show="items.length === 0" class="px-4 py-3 text-sm text-gray-500">
        No encontramos sugerencias. Presioná Enter para buscar igual.
    </li>
</ul>
