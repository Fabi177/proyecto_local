{{-- Selector de rubros en dos niveles. Lógica: resources/js/selector-rubros.js.

     Tiene que ir DENTRO de un x-data="selectorRubros(...)" (o de uno que lo incluya con ...selectorRubros(...)).
     Recibe:
       - $categorias: Rubros::categorias()
       - $campo:      name de los checkboxes. En el formulario del comerciante es "rubros[]"; en el filtro del
                      dashboard va null porque ese formulario arma sus propios campos ocultos.
       - $marcados:   claves ya marcadas desde el servidor (así también se ven bien si el JavaScript tarda en cargar).

     Primero se ve la lista corta de categorías; al tocar una aparecen recién sus rubros.
     Los checkboxes de todas las categorías están siempre en la página (solo se oculta el panel), así lo marcado
     viaja igual con el formulario aunque su categoría esté cerrada. --}}
@php
    $campo = $campo ?? null;
    $marcados = $marcados ?? [];
@endphp

<div class="flex flex-wrap gap-2" role="group" aria-label="Rubro principal">
    @foreach ($categorias as $nombre => $categoria)
        <button type="button"
                @click="alternar(@js($nombre))"
                :aria-expanded="abierta === @js($nombre) ? 'true' : 'false'"
                :class="abierta === @js($nombre) ? 'border-[var(--primary-green)] bg-green-50 ring-2 ring-[var(--primary-green)]' : 'border-gray-200 bg-white hover:border-[var(--primary-green)]'"
                class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition">
            <span aria-hidden="true">{{ $categoria['icono'] }}</span>
            <span>{{ $nombre }}</span>
            <span x-show="contar(@js($nombre)) > 0" x-text="contar(@js($nombre))" style="display: none;"
                  class="rounded-full bg-[var(--primary-green)] px-2 text-xs font-bold text-white"></span>
        </button>
    @endforeach
</div>

@foreach ($categorias as $nombre => $categoria)
    <div x-show="abierta === @js($nombre)" style="display: none;"
         class="mt-4 rounded-xl border border-green-200 bg-green-50/50 p-4"
         role="region" aria-label="Rubros de {{ $nombre }}">
        <p class="mb-3 text-sm font-bold text-gray-700">
            <span aria-hidden="true">{{ $categoria['icono'] }}</span> {{ $nombre }}
            <span class="font-normal text-gray-500">· marcá los que correspondan</span>
        </p>
        <div class="flex flex-wrap gap-2">
            @foreach ($categoria['items'] as $clave => $etiqueta)
                <label class="cursor-pointer">
                    <input type="checkbox" value="{{ $clave }}" x-model="sel"
                           @if ($campo) name="{{ $campo }}" @endif
                           @checked(in_array($clave, $marcados, true))
                           class="peer sr-only">
                    <span class="inline-block rounded-full border border-gray-200 bg-white px-4 py-2 text-sm transition hover:border-[var(--primary-green)] peer-checked:border-[var(--primary-green)] peer-checked:bg-[var(--primary-green)] peer-checked:font-bold peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--primary-green)]">{{ $etiqueta }}</span>
                </label>
            @endforeach
        </div>
    </div>
@endforeach
