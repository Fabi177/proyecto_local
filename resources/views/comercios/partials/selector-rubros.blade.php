{{-- Campo "Rubros" del formulario del comerciante (alta y edición): puede elegir TODOS los que quiera.
     Recibe $seleccionados: las claves de los rubros que ya tiene el comercio (vacío al crear).
     Si el formulario volvió con errores, se recupera lo que había marcado (old('rubros')). --}}
@php
    $categorias = \App\Support\Rubros::categorias();
    // En el orden del catálogo y solo claves que existen.
    $elegidos = array_values(array_intersect(\App\Support\Rubros::claves(), (array) old('rubros', $seleccionados ?? [])));
@endphp

<div class="input-wrapper md:col-span-2"
     x-data="selectorRubros({
         seleccionados: @js($elegidos),
         etiquetas: @js(\App\Support\Rubros::planos()),
         grupos: @js(\App\Support\Rubros::clavesPorCategoria())
     })">
    <span class="input-label" id="etiqueta-rubros">{{ __('Rubros / Categorías *') }}</span>
    <p class="mb-3 text-sm text-gray-500">
        Tocá una categoría y marcá todos los rubros que correspondan a tu comercio: podés elegir los que quieras.
    </p>

    {{-- Lo elegido hasta ahora, cada rubro con su × para quitarlo --}}
    <div class="mb-3 flex flex-wrap items-center gap-2" aria-live="polite" aria-labelledby="etiqueta-rubros">
        <template x-for="clave in ordenados()" :key="clave">
            <span class="inline-flex items-center gap-1 rounded-full bg-green-100 py-1 pl-3 pr-1 text-sm font-semibold text-green-800">
                <span x-text="etiqueta(clave)"></span>
                <button type="button" @click="quitar(clave)" class="rounded-full px-2 hover:bg-green-200" aria-label="Quitar rubro">×</button>
            </span>
        </template>
        <span x-show="sel.length === 0" style="display: none;" class="text-sm font-semibold text-amber-700">Todavía no elegiste ningún rubro.</span>
        <button type="button" x-show="sel.length > 1" @click="limpiar()" style="display: none;" class="ml-1 text-sm text-green-700 underline">Quitar todos</button>
    </div>

    @include('comercios.partials.rubros-niveles', ['categorias' => $categorias, 'campo' => 'rubros[]', 'marcados' => $elegidos])
</div>
