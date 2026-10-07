<!-- Panel que ven el VISITANTE y el CLIENTE: buscador, filtro por rubro y todos los comercios -->
@php
    // Rubros elegidos (vienen de la URL ya limpios), mapa clave => etiqueta para mostrarlos
    // y categoría => claves de sus rubros (para contar cuántos hay marcados en cada una)
    $rubrosElegidos = $filters['rubro'] ?? [];
    $etiquetas = \App\Support\Rubros::planos();
    $grupos = \App\Support\Rubros::clavesPorCategoria();
@endphp

<div class="bg-white overflow-visible shadow-xl sm:rounded-lg">
    <div class="p-6 md:p-8 text-gray-900">

        <h3 class="text-2xl font-bold text-gray-800">Encuentra lo que necesitas</h3>
        <p class="mt-2 text-gray-600">
            Busca comercios por nombre, rubro o servicio.
        </p>

        {{-- Un solo formulario: el texto buscado y los rubros elegidos viajan juntos por GET --}}
        <form action="{{ route('dashboard') }}" method="GET"
              x-data="{
                  ...selectorRubros({ seleccionados: @js($rubrosElegidos), etiquetas: @js($etiquetas), grupos: @js($grupos) }),
                  abierto: false,
                  inicial: @js($rubrosElegidos),
                  cambio() { return JSON.stringify([...this.sel].sort()) !== JSON.stringify([...this.inicial].sort()); },
                  cerrar() { this.abierto = false; if (this.cambio()) { this.$nextTick(() => this.$root.requestSubmit()); } },
                  quitarYBuscar(r) { const f = this.$root; this.quitar(r); this.$nextTick(() => f.requestSubmit()); },
                  limpiarYBuscar() { const f = this.$root; this.limpiar(); this.$nextTick(() => f.requestSubmit()); }
              }"
              @keydown.escape.window="abierto && cerrar()">

            {{-- Los rubros elegidos viajan como rubro[]=... --}}
            <template x-for="r in sel" :key="r">
                <input type="hidden" name="rubro[]" :value="r">
            </template>

            <div class="mt-6 flex flex-col gap-3 md:flex-row">
                {{-- Ciudad o código postal: al elegir una se ven solo los comercios de esa zona.
                     La lógica está en resources/js/autocompletado-localidades.js --}}
                <div class="relative flex items-center md:w-72"
                     x-data="autocompletadoLocalidades({ url: @js(route('localidades.sugerencias')), id: @js($localidadElegida?->id), texto: @js($localidadElegida?->etiqueta) })"
                     @click.outside="cerrar()">
                    <input type="hidden" name="localidad" :value="valorId" :disabled="!valorId">
                    <input type="text"
                           id="localidad-texto"
                           x-model="texto"
                           x-bind="entrada"
                           role="combobox"
                           aria-autocomplete="list"
                           aria-haspopup="listbox"
                           aria-label="Ciudad o código postal"
                           autocomplete="off"
                           class="block w-full rounded-lg border-gray-300 py-4 pl-12 pr-10 text-lg shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]"
                           placeholder="Ciudad o código postal">

                    @include('comercios.partials.sugerencias-localidades')

                    <div class="pointer-events-none absolute left-0 pl-4">
                        <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                    </div>
                    <button type="button"
                            x-show="texto !== ''"
                            @click="limpiar()"
                            style="display: none;"
                            aria-label="Quitar ciudad"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="relative flex flex-1 items-center" x-data="autocompletadoComercios({ url: @js(route('comercios.sugerencias')) })" @click.outside="cerrar()">
                    <input type="text"
                           name="search"
                           id="search"
                           x-bind="entrada"
                           role="combobox"
                           aria-autocomplete="list"
                           aria-haspopup="listbox"
                           autocomplete="off"
                           class="block w-full rounded-lg border-gray-300 py-4 pl-12 pr-4 text-lg shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]"
                           placeholder="Buscar Restaurantes, Ferreterías, Servicios..."
                           value="{{ $filters['search'] ?? '' }}">

                    @include('comercios.partials.sugerencias')

                    <div class="absolute left-0 pl-4">
                        <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </div>
                </div>

                <button type="button" @click="abierto = true"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-4 font-semibold text-gray-700 shadow-sm transition hover:border-[var(--primary-green)]">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                    Filtrar por rubro
                    <span x-show="sel.length" x-text="sel.length" style="display: none;" class="rounded-full bg-[var(--primary-green)] px-2 text-sm text-white"></span>
                </button>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-[var(--primary-green)] px-6 py-4 font-bold text-white shadow transition hover:bg-green-600">
                    Buscar
                </button>
            </div>

            {{-- Rubros elegidos, cada uno con su × para quitarlo --}}
            <div class="mt-4 flex flex-wrap items-center gap-2" x-show="sel.length" style="display: none;">
                <template x-for="r in ordenados()" :key="r">
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 py-1 pl-3 pr-1 text-sm font-semibold text-green-800">
                        <span x-text="etiqueta(r)"></span>
                        <button type="button" @click="quitarYBuscar(r)" class="rounded-full px-2 hover:bg-green-200" aria-label="Quitar rubro">×</button>
                    </span>
                </template>
                <button type="button" @click="limpiarYBuscar()" class="ml-1 text-sm text-green-700 underline">Limpiar rubros</button>
            </div>

            {{-- Ventana emergente con los rubros (se pueden marcar varios) --}}
            <div x-show="abierto" style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
                 @click.self="cerrar()">
                <div class="flex max-h-[85vh] w-full max-w-xl flex-col rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="titulo-rubros">

                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h4 id="titulo-rubros" class="text-lg font-bold text-gray-800">Elige uno o varios rubros</h4>
                        <button type="button" @click="cerrar()" class="text-2xl leading-none text-gray-400 hover:text-gray-600" aria-label="Cerrar">×</button>
                    </div>

                    <div class="overflow-y-auto px-6 py-4">
                        <p class="mb-3 text-sm text-gray-600">Tocá una categoría para ver sus rubros y marcá uno o varios.</p>
                        @include('comercios.partials.rubros-niveles', ['categorias' => $categorias, 'campo' => null, 'marcados' => $rubrosElegidos])
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                        <button type="button" @click="sel = []" class="text-sm text-gray-500 hover:text-gray-700">Borrar selección</button>
                        <button type="submit" class="rounded-lg bg-[var(--primary-green)] px-6 py-2 font-bold text-white shadow transition hover:bg-green-600">Aplicar</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- TODOS LOS COMERCIOS --}}
        <div class="mt-10">
            <h4 class="text-lg font-semibold text-gray-700">
                @if (! empty($filters))
                    Resultados
                @else
                    Todos los comercios
                @endif
                @if ($localidadElegida)
                    <span class="font-normal text-gray-500">en {{ $localidadElegida->nombre }}</span>
                @endif
                <span class="font-normal text-gray-500">({{ $comercios->total() }})</span>
            </h4>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($comercios as $comercio)
                    @php
                        $promedio = (float) ($comercio->resenas_avg_calificacion ?? 0);
                        $estrellas = (int) round($promedio);
                    @endphp
                    <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 transition hover:shadow-lg">
                        <div class="flex items-start gap-4">
                            @if ($comercio->logo_url)
                                <img src="{{ $comercio->logo_url }}" alt="Logo de {{ $comercio->nombre }}" class="h-16 w-16 flex-shrink-0 rounded-lg border border-gray-200 bg-white object-contain">
                            @else
                                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-lg bg-[var(--primary-green)] text-2xl font-bold text-white" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($comercio->nombre, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h5 class="break-words text-lg font-bold text-gray-900">{{ $comercio->nombre }}</h5>
                                @php $rubrosDelComercio = $comercio->rubros_etiquetas; @endphp
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach (array_slice($rubrosDelComercio, 0, 2) as $rubroEtiqueta)
                                        <span class="inline-block rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">{{ $rubroEtiqueta }}</span>
                                    @endforeach
                                    @if (count($rubrosDelComercio) > 2)
                                        <span class="inline-block rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600" title="{{ implode(', ', array_slice($rubrosDelComercio, 2)) }}">+{{ count($rubrosDelComercio) - 2 }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-sm text-gray-500">
                            @if ($comercio->resenas_count > 0)
                                <span class="text-amber-500">{{ str_repeat('★', $estrellas) }}{{ str_repeat('☆', 5 - $estrellas) }}</span>
                                {{ number_format($promedio, 1, ',', '.') }} ({{ $comercio->resenas_count }})
                            @else
                                <span class="text-gray-400">Sin calificaciones todavía</span>
                            @endif
                        </p>
                        <p class="mt-1 break-words text-sm text-gray-500">{{ $comercio->direccion }}@if ($comercio->localidad), {{ $comercio->localidad->nombre }}@endif</p>

                        <div class="mt-4 flex flex-1 items-end justify-end">
                            <a href="{{ route('comercio.show', $comercio) }}" class="inline-flex items-center rounded-lg bg-[var(--primary-green)] px-5 py-2 font-bold text-white shadow transition hover:bg-green-600">
                                Ver Perfil
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-xl bg-gray-50 p-8 text-center">
                        <h5 class="text-xl font-bold text-gray-800">Sin resultados</h5>
                        <p class="mt-2 text-gray-600">No encontramos comercios que coincidan con tu búsqueda.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $comercios->links() }}
            </div>
        </div>
    </div>
</div>
