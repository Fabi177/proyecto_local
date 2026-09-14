<x-app-layout>
<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
{{ __('Buscar Comercios') }}
</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- TARJETA DE BÚSQUEDA (COMO LA DEL DASHBOARD) -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 md:p-8">
                <!-- Formulario de Búsqueda -->
                <form action="{{ route('comercios.index') }}" method="GET">
                    <div class="relative flex items-center">
                        <!-- El valor (value) mantiene lo que el usuario escribió -->
                        <input type="text"
                               name="search"
                               id="search"
                               class="block w-full rounded-lg border-gray-300 py-4 pl-12 pr-4 text-lg shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]"
                               placeholder="Buscar Restaurantes, Ferreterías, Servicios..."
                               value="{{ $filters['search'] ?? '' }}">

                        <div class="absolute left-0 pl-4">
                            <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        </div>
                    </div>

                    <!-- Si se está filtrando por rubro, lo mantenemos oculto -->
                    @if (isset($filters['rubro']))
                        <input type="hidden" name="rubro" value="{{ $filters['rubro'] }}">
                    @endif

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gray-800 text-white font-bold rounded-lg shadow transition hover:bg-gray-700">
                            Re-Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ENCABEZADO DE RESULTADOS -->
        <div class="mt-8 mb-4">
            @if (isset($filters['search']))
                <h3 class="text-xl font-semibold text-gray-800">Resultados para: "<span class="text-[var(--primary-green)]">{{ $filters['search'] }}</span>"</h3>
            @elseif (isset($filters['rubro']))
                <h3 class="text-xl font-semibold text-gray-800">Comercios en la categoría: <span class="text-[var(--primary-green)]">{{ $filters['rubro'] }}</span></h3>
            @else
                <h3 class="text-xl font-semibold text-gray-800">Todos los Comercios</h3>
            @endif
        </div>

        <!-- RESULTADOS DE BÚSQUEDA -->
        <div class="mt-8">
            @forelse ($comercios as $comercio)
                <!-- Tarjeta de Comercio -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6 flex flex-col sm:flex-row">
                    <div class="sm:w-1/3">
                        <!-- Placeholder de la inicial del nombre -->
                        <img src="https://placehold.co/600x400/2ecc71/white?text={{ $comercio->nombre[0] }}"
                             alt="Logo de {{ $comercio->nombre }}"
                             class="w-full h-48 sm:h-full object-cover">
                    </div>

                    <!-- Info del Comercio -->
                    <div class="sm:w-2/3 p-6">
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full mb-2">
                            {{ $comercio->rubro }}
                        </span>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $comercio->nombre }}</h3>
                        <p class="mt-2 text-gray-600 truncate">{{ $comercio->descripcion }}</p>

                        <!-- Dirección e Iconos -->
                        <div class="mt-4 flex items-center text-gray-500">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <span>{{ $comercio->direccion }}</span>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('comercio.show', $comercio) }}" class="inline-flex items-center px-6 py-2 bg-[var(--primary-green)] text-white font-bold rounded-lg shadow transition hover:bg-green-600">
                                Ver Perfil
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Mensaje si no hay resultados -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 md:p-8 text-center">
                        <h3 class="text-2xl font-bold text-gray-800">Sin resultados</h3>
                        <p class="mt-2 text-gray-600">
                            No se encontraron comercios que coincidan con tu búsqueda.
                        </p>
                    </div>
                </div>
            @endforelse

            <!-- Links de Paginación (con los filtros) -->
            <div class="mt-8">
                {{ $comercios->appends($filters)->links() }}
            </div>
        </div>

    </div>
</div>


</x-app-layout>
