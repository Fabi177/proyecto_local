<!-- Este es el panel que verá un USUARIO (Cliente) -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 md:p-8 text-gray-900">

        <h3 class="text-2xl font-bold text-gray-800">Encuentra lo que necesitas</h3>
        <p class="mt-2 text-gray-600">
            Busca comercios por nombre, rubro o servicio.
        </p>

        <!--
            ============================================
            ==== CAMBIO 1: Se añadió el <form> ====
            ============================================
            Envolvemos la barra de búsqueda en un formulario
            para que apunte a la ruta 'comercios.index'
        -->
        <form action="{{ route('comercios.index') }}" method="GET">
            <div class="mt-6">
                <div class="relative flex items-center">
                    <input type="text"
                           name="search"
                           id="search"
                           class="block w-full rounded-lg border-gray-300 py-4 pl-12 pr-4 text-lg shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]"
                           placeholder="Buscar Restaurantes, Ferreterías, Servicios...">

                    <div class="absolute left-0 pl-4">
                        <svg class="w-6 h-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </div>
                </div>
                <!--
                    No es necesario un botón de "submit" visible,
                    presionar Enter enviará el formulario.
                -->
            </div>
        </form>

        <!--
            ============================================
            ==== CAMBIO 2: Se actualizaron los enlaces ====
            ============================================
        -->
        <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-700">Categorías Populares</h4>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">

                <!-- Enlace a ?rubro=Restaurante -->
                <a href="{{ route('comercios.index', ['rubro' => 'Restaurante']) }}" class="block p-4 bg-gray-50 rounded-lg text-center transition hover:bg-gray-100 hover:shadow-md">
                    <span class="text-4xl">🍽️</span>
                    <span class="mt-2 block font-semibold text-gray-700">Restaurantes</span>
                </a>

                <!-- Enlace a ?rubro=Indumentaria -->
                <a href="{{ route('comercios.index', ['rubro' => 'Indumentaria']) }}" class="block p-4 bg-gray-50 rounded-lg text-center transition hover:bg-gray-100 hover:shadow-md">
                    <span class="text-4xl">👕</span>
                    <span class="mt-2 block font-semibold text-gray-700">Indumentaria</span>
                </a>

                <!-- Enlace a ?rubro=Farmacia -->
                <a href="{{ route('comercios.index', ['rubro' => 'Farmacia']) }}" class="block p-4 bg-gray-50 rounded-lg text-center transition hover:bg-gray-100 hover:shadow-md">
                    <span class="text-4xl">⚕️</span>
                    <span class="mt-2 block font-semibold text-gray-700">Farmacias</span>
                </a>

                <!-- (Puedes añadir más categorías aquí) -->
                <a href="{{ route('comercios.index', ['rubro' => 'Mecanico']) }}" class="block p-4 bg-gray-50 rounded-lg text-center transition hover:bg-gray-100 hover:shadow-md">
                    <span class="text-4xl">🛠️</span>
                    <span class="mt-2 block font-semibold text-gray-700">Servicios</span>
                </a>

            </div>
        </div>
    </div>
</div>
