
<x-app-layout>
<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
<!-- Título de la página: el nombre del comercio -->
{{ $comercio->nombre }}
</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <!-- Contenedor del perfil: dividido en 2 columnas -->
            <div class="grid grid-cols-1 md:grid-cols-3">

                <!-- ================================== -->
                <!-- Columna 1: Info (Logo, Mapa, Contacto) -->
                <!-- ================================== -->
                <div class="md:col-span-1 p-6 bg-gray-50 border-r border-gray-200">

                    <!-- (Placeholder para el Logo/Imagen) -->
                    <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                        <span class="text-gray-500">(Aquí irá el Logo/Imagen Principal)</span>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Ubicación y Contacto</h3>

                    <!-- (Placeholder para el Mapa) -->
                    <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center mt-2">
                        <span class="text-gray-500">(Aquí irá el Mapa)</span>
                    </div>

                    <!-- Dirección -->
                    <div class="flex items-start mt-4">
                        <svg class="w-5 h-5 text-gray-500 mt-1 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        <span class="text-gray-700">{{ $comercio->direccion }}</span>
                    </div>

                    <!-- Teléfono (solo si existe) -->
                    @if ($comercio->telefono)
                        <div class="flex items-center mt-3">
                            <svg class="w-5 h-5 text-gray-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.63C11.24 16.088 9.917 14.76 8.163 12.998l1.293-.97c.362-.271.527-.734.417-1.173L8.756 6.463c-.125-.501-.575-.852-1.091-.852H6.375A2.25 2.25 0 0 0 4.125 7.875v.375Z" /></svg>
                            <span class="text-gray-700">{{ $comercio->telefono }}</span>
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
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Horarios</h3>
                        <div class="text-gray-700">
                            <p><span class="font-medium">Atención:</span> {{ $comercio->horarios_atencion ?? 'No especificado' }}</p>
                            <p><span class="font-medium">Días cerrados:</span> {{ $comercio->dias_no_laborales ?? 'No especificado' }}</p>
                        </div>
                    </div>

                    <!-- Servicios y Pagos -->
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Servicios y Facilidades</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Formas de Pago -->
                            <div>
                                <p class="font-medium text-gray-700">Formas de Pago:</p>
                                <p class="text-gray-600">{{ $comercio->formas_pago ?? 'No especificado' }}</p>
                            </div>
                            <!-- Otros Servicios -->
                            <div>
                                <p class="font-medium text-gray-700">Servicios Adicionales:</p>
                                <p class="text-gray-600">{{ $comercio->servicios_adicionales ?? 'No especificado' }}</p>
                            </div>
                            <!-- Accesibilidad -->
                            <div>
                                @if ($comercio->ingreso_discapacitados)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        ✓ Acceso Discapacitados
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        ✗ Sin Acceso Discapacitados
                                    </span>
                                @endif
                            </div>
                            <!-- Estacionamiento -->
                            <div>
                                @if ($comercio->estacionamiento)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        ✓ Estacionamiento Propio
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        ✗ Sin Estacionamiento
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> <!-- Fin Columna 2 -->

            </div> <!-- Fin Grid -->
        </div>
    </div>
</div>


</x-app-layout>
