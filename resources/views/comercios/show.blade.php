
<x-app-layout>
<x-slot name="header">
<style>
    .perfil-barra { display: flex; flex-direction: column; gap: 12px; }
    .perfil-barra__izq { display: flex; align-items: center; gap: 12px; min-width: 0; flex-wrap: wrap; }
    .perfil-volver { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #d1d5db; border-radius: 9999px; background: #fff; color: #374151; font-size: .875rem; font-weight: 600; white-space: nowrap; text-decoration: none; }
    .perfil-volver:hover { background: #f3f4f6; }
    .perfil-volver:focus-visible, .perfil-buscador input:focus-visible { outline: 3px solid var(--light-blue, #3498db); outline-offset: 2px; }
    .perfil-buscador { position: relative; width: 100%; }
    .perfil-buscador input[type="search"] { width: 100%; padding: .55rem .75rem .55rem 2.5rem; border: 1px solid #d1d5db; border-radius: .5rem; background: #fff; font-size: .95rem; }
    .perfil-buscador input[type="search"]:focus { border-color: var(--primary-green, #2ecc71); box-shadow: 0 0 0 1px var(--primary-green, #2ecc71); }
    .perfil-buscador svg { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); width: 1.25rem; height: 1.25rem; color: #9ca3af; pointer-events: none; }
    .perfil-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    @media (min-width: 768px) {
        .perfil-barra { flex-direction: row; align-items: center; justify-content: space-between; }
        .perfil-buscador { max-width: 26rem; }
    }
</style>
<div class="perfil-barra">
    <div class="perfil-barra__izq">
        @if ($mostrarBuscador)
            <!-- Vuelve a los resultados de la búsqueda que hizo el cliente -->
            <a href="{{ $volverUrl }}" class="perfil-volver">
                <span aria-hidden="true">←</span> Volver a los resultados
            </a>
        @endif
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <!-- Título de la página: el nombre del comercio -->
            {{ $comercio->nombre }}
        </h2>
    </div>

    @if ($mostrarBuscador)
        <!-- Buscador: arranca vacío, con el mismo texto de ayuda que el del panel del cliente -->
        <form action="{{ route('comercios.index') }}" method="GET" role="search" class="perfil-buscador" x-data="autocompletadoComercios({ url: @js(route('comercios.sugerencias')) })" @click.outside="cerrar()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="search" name="search" aria-label="Buscar comercios" placeholder="Buscar Restaurantes, Ferreterías, Servicios..." autocomplete="off" x-bind="entrada" role="combobox" aria-autocomplete="list" aria-haspopup="listbox">
            @include('comercios.partials.sugerencias')
            <button type="submit" class="perfil-sr">Buscar</button>
        </form>
    @endif
</div>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status_resena'))
            <!-- Aviso al volver de calificar o borrar una reseña -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-green-100 px-4 py-3 text-green-800 shadow" role="status">
                <span class="font-medium">{{ session('status_resena') }}</span>
                <a href="#resenas" class="text-sm font-semibold underline">Ver / editar mi reseña</a>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <!-- Contenedor del perfil: dividido en 2 columnas -->
            <div class="grid grid-cols-1 md:grid-cols-3">

                <!-- ================================== -->
                <!-- Columna 1: Info (Logo, Mapa, Contacto) -->
                <!-- ================================== -->
                <div class="md:col-span-1 p-6 bg-gray-50 border-r border-gray-200">

                    <!-- Logo / Imagen del comercio -->
                    @if ($comercio->logo_url)
                        <img src="{{ $comercio->logo_url }}"
                             alt="Logo de {{ $comercio->nombre }}"
                             class="w-full h-48 object-contain bg-white rounded-lg border border-gray-200">
                    @else
                        <div class="w-full h-48 bg-gray-200 rounded-lg flex flex-col items-center justify-center">
                            <span class="text-6xl font-bold text-gray-400">{{ mb_strtoupper(mb_substr($comercio->nombre, 0, 1)) }}</span>
                            <span class="mt-2 text-sm text-gray-500">Sin logo</span>
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Ubicación y Contacto</h3>

                    <!-- Mapa con la geolocalización del comercio -->
                    @if ($comercio->tieneUbicacion())
                        <!-- "relative z-0" evita que los controles del mapa tapen el menú de navegación -->
                        <div class="relative z-0 w-full h-56 mt-2 rounded-lg overflow-hidden border border-gray-200"
                             x-data="mapaComercio({ lat: @js($comercio->latitud), lng: @js($comercio->longitud), nombre: @js($comercio->nombre) })"></div>

                        <a href="https://www.google.com/maps/dir/?api=1&amp;destination={{ number_format($comercio->latitud, 7, '.', '') }},{{ number_format($comercio->longitud, 7, '.', '') }}"
                           target="_blank" rel="noopener"
                           class="mt-3 flex items-center justify-center w-full px-4 py-2 bg-[var(--light-blue)] text-white text-sm font-bold rounded-lg transition hover:opacity-90">
                            Cómo llegar
                        </a>
                    @else
                        <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center mt-2 px-4 text-center">
                            <span class="text-gray-500">Este comercio todavía no cargó su ubicación en el mapa.</span>
                        </div>
                    @endif

                    <!-- Dirección -->
                    <div class="flex items-start mt-4">
                        <svg class="w-5 h-5 text-gray-500 mt-1 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        <span class="text-gray-700">{{ $comercio->direccion }}</span>
                    </div>

                    <!-- Teléfono (solo si existe) -->
                    @if ($comercio->telefono)
                        <div class="flex items-center mt-3">
                            <svg class="w-5 h-5 text-gray-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.63C11.24 16.088 9.917 14.76 8.163 12.998l1.293-.97c.362-.271.527-.734.417-1.173L8.756 6.463c-.125-.501-.575-.852-1.091-.852H6.375A2.25 2.25 0 0 0 4.125 7.875v.375Z" /></svg>
                            <div>
                                <!-- Si no hay WhatsApp cargado, el teléfono es el único contacto directo y se aclara -->
                                <p class="text-xs text-gray-500">{{ $comercio->red_whatsapp ? 'Teléfono' : 'Teléfono / sin WhatsApp' }}</p>
                                <span class="text-gray-700">{{ $comercio->telefono }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Botones de Redes Sociales (solo si existen) -->
                    <div class="mt-6 space-y-3">
                        @if ($comercio->red_whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $comercio->red_whatsapp) }}" target="_blank" class="flex items-center justify-center w-full px-4 py-3 bg-green-500 text-white font-bold rounded-lg transition hover:bg-green-600">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                Contactar por WhatsApp
                            </a>
                        @endif

                        @if ($comercio->red_instagram)
                            <a href="https://instagram.com/{{ $comercio->red_instagram }}" target="_blank" class="flex items-center justify-center w-full px-4 py-3 bg-pink-500 text-white font-bold rounded-lg transition hover:bg-pink-600">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" /></svg>
                                Seguir en Instagram
                            </a>
                        @endif

                        @if ($comercio->red_facebook)
                            <a href="https://facebook.com/{{ $comercio->red_facebook }}" target="_blank" class="flex items-center justify-center w-full px-4 py-3 bg-blue-600 text-white font-bold rounded-lg transition hover:bg-blue-700">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-2.25 9h-1.5v3.75h1.5V15h-1.5v2.25h1.5v2.25h3.75v-2.25h1.5v-2.25h-1.5v-3.75h1.5v-2.25h-1.5V6.75h-1.5v2.25h-1.5v2.25Z" /></svg>
                                Ver en Facebook
                            </a>
                        @endif

                        @if ($comercio->sitio_web)
                            <a href="{{ $comercio->sitio_web }}" target="_blank" class="flex items-center justify-center w-full px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-lg transition hover:bg-gray-200">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c-4.832 0-8.716-3.914-8.716-8.747M12 21c4.832 0 8.716-3.914 8.716-8.747m-8.716 8.747v-7.5M12 12.253v-7.5M12 12.253a2.25 2.25 0 0 0-2.25 2.25M12 12.253a2.25 2.25 0 0 1 2.25 2.25M12 12.253a2.25 2.25 0 0 1-2.25-2.25M12 12.253a2.25 2.25 0 0 0 2.25-2.25M3.284 5.253a9.004 9.004 0 0 1 17.432 0M3.284 18.747a9.004 9.004 0 0 0 17.432 0" /></svg>
                                Visitar Sitio Web
                            </a>
                        @endif
                    </div>
                </div>

                <!-- ================================== -->
                <!-- Columna 2: Detalles (Descripción, Horarios) -->
                <!-- ================================== -->
                <div class="md:col-span-2 p-6 md:p-8">

                    <!-- Nombre y Rubro -->
                    <h1 class="text-4xl font-bold text-gray-900">{{ $comercio->nombre }}</h1>
                    <p class="mt-1 text-xl font-medium text-[var(--primary-green)]">{{ $comercio->rubro }}</p>

                    <!-- Descripción -->
                    @if ($comercio->descripcion)
                        <div class="mt-6 border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Sobre Nosotros</h3>
                            <p class="text-gray-600 whitespace-pre-line">{{ $comercio->descripcion }}</p>
                        </div>
                    @endif

                    <!-- Horarios -->
                    @php
                        // Fechas de cierre que todavía no pasaron (las vencidas no se muestran al cliente)
                        $cierresVigentes = collect($comercio->dias_cierre ?? [])
                            ->filter(fn ($c) => ($c['b'] ?? $c['a']) >= now()->toDateString())
                            ->values();

                        // Si el comercio usa el selector nuevo, dias_cierre es un array (aunque esté vacío).
                        // Si es null, es un comercio viejo que solo tiene el texto libre en dias_no_laborales.
                        $usaSelectorCierres = $comercio->dias_cierre !== null;
                    @endphp
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Horarios</h3>
                        <div class="text-gray-700">
                            <p class="font-medium">Atención:</p>
                            <p class="whitespace-pre-line">{{ $comercio->horarios_atencion ?: 'No especificado' }}</p>

                            <p class="mt-3 font-medium">Días cerrados:</p>
                            @if ($usaSelectorCierres)
                                @if ($comercio->cierra_feriados)
                                    <p>Cerrado los feriados nacionales.</p>
                                @endif
                                @foreach ($cierresVigentes as $cierre)
                                    <p>
                                        {{ \Carbon\Carbon::parse($cierre['a'])->format('d/m/Y') }}
                                        @if (!empty($cierre['b']))
                                            al {{ \Carbon\Carbon::parse($cierre['b'])->format('d/m/Y') }}
                                        @endif
                                        @if (!empty($cierre['w']))
                                            <span class="text-gray-500">({{ $cierre['w'] }})</span>
                                        @endif
                                    </p>
                                @endforeach
                                @if (!$comercio->cierra_feriados && $cierresVigentes->isEmpty())
                                    <p>Sin cierres especiales.</p>
                                @endif
                            @else
                                <p class="whitespace-pre-line">{{ $comercio->dias_no_laborales ?: 'No especificado' }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Servicios y Pagos -->
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Servicios y Facilidades</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Formas de Pago -->
                            <div>
                                <p class="font-medium text-gray-700">Formas de Pago:</p>
                                @php
                                    $formasPago = array_values(array_filter(array_map('trim', explode(',', $comercio->formas_pago ?? ''))));
                                @endphp
                                @if (count($formasPago))
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        @foreach ($formasPago as $pago)
                                            <span class="inline-flex rounded-full bg-sky-100 px-3 py-0.5 text-sm text-sky-800">{{ $pago }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-600">No especificado</p>
                                @endif
                            </div>
                            <!-- Otros Servicios -->
                            <div>
                                <p class="font-medium text-gray-700">Servicios Adicionales:</p>
                                <p class="text-gray-600">{{ $comercio->servicios_adicionales ?? 'No especificado' }}</p>
                            </div>
                            <!-- Accesibilidad y servicios: cada ítem muestra su etiqueta afirmativa o negativa -->
                            <div>
                                <p class="font-medium text-gray-700 mb-2">Accesibilidad y servicios:</p>
                                <ul class="flex flex-col items-start gap-2" data-accesibilidad>
                                    @foreach (\App\Models\Comercio::ACCESIBILIDAD as $campo => $item)
                                        @if ($comercio->$campo)
                                            <li class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <span aria-hidden="true">✓</span> {{ $item['si'] }}
                                            </li>
                                        @else
                                            <li class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                <span aria-hidden="true">✕</span> {{ $item['no'] }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> <!-- Fin Columna 2 -->

            </div> <!-- Fin Grid -->
        </div>

        <!-- Calificaciones y comentarios -->
        @include('comercios.partials.resenas')
    </div>
</div>


</x-app-layout>
