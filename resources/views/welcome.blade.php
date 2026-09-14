<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LocalCommers - Inicio</title>

        <!-- Scripts de Tailwind --><script src="https://cdn.tailwindcss.com"></script>

        <!-- Configuración de Colores de Marca --><script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'brand-green': '#2ecc71',
                            'brand-blue': '#3498db',
                        },
                    },
                },
            }
        </script>

        <!-- Fuentes de Google Fonts --><link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="bg-white dark:bg-gray-900 antialiased">

        <div class="relative min-h-screen w-full">

            <!-- ================================= --><!-- ===== NAVEGACIÓN (HEADER) ===== --><!-- ================================= --><header class="absolute inset-x-0 top-0 z-50">
                <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
                    <!-- Logo/Título --><div class="flex lg:flex-1">
                        <a href="{{ url('/') }}" class="-m-1.5 p-1.5">
                            <h1 class="text-2xl font-bold text-brand-green">LocalCommers</h1>
                        </a>
                    </div>

                    <!-- Botones de Autenticación (Derecha) --><div class="flex lg:flex-1 justify-end gap-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="rounded-md px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-white/10">Mi Panel</a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-md px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-white/10">Ingresar</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-brand-green shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Registrarse</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </nav>
            </header>

            <main>
                <!-- ================================= --><!-- ===== SECCIÓN HERO (PRINCIPAL) ===== --><!-- ================================= --><div class="relative isolate overflow-hidden pt-14">

                    <img src="../../imagenes/fondo.jpg" alt="Fondo de comercios" class="absolute inset-0 -z-20 h-full w-full object-cover">

                    <div class="absolute inset-0 -z-10 bg-black/60"></div>

                    <!--
                        CAMBIO:
                        Se aumentó el padding (py-56 a py-64)
                        para bajar el texto BLANCO AÚN MÁS y no tapar el texto de la imagen.
                    --><div class="mx-auto max-w-2xl py-64 sm:py-72 lg:py-80 text-center"> <!-- CAMBIO DE PADDING --><!-- Título y Tagline --><h1 class="text-4xl font-black tracking-tight text-white sm:text-6xl" style="text-shadow: 0 2px 10px rgba(0,0,0,0.3)">
                            Conecta, Descubre, Crece.
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-gray-100" style="text-shadow: 0 1px 5px rgba(0,0,0,0.3)">
                            La plataforma donde comerciantes y clientes se encuentran.
                        </p>

                        <!-- Botones de CTA (Solo para visitantes) -->@guest
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            <a href="{{ route('register') }}" class="rounded-md bg-white px-5 py-3 text-base font-semibold text-brand-green shadow-lg hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition-transform hover:scale-105">
                                Registrarme Ahora
                            </a>
                            <a href="{{ route('login') }}" class="text-base font-semibold leading-6 text-white hover:text-gray-200">
                                Ingresar <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                        @endguest
                    </div>
                </div>

                <!-- ================================= --><!-- ===== SECCIÓN DE CARACTERÍSTICAS ===== --><!-- ================================= --><div class="py-24 sm:py-32 bg-white dark:bg-gray-900">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            Todo en un solo lugar
                        </h2>

                        <!-- Grid de 3 Tarjetas --><div class="mt-16 grid grid-cols-1 lg:grid-cols-3 gap-8">

                            <!-- Card 1: Comerciantes --><div class="flex flex-col rounded-2xl bg-gray-50 dark:bg-gray-800 p-8 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 transition-transform duration-300 hover:scale-[1.03] hover:shadow-2xl">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900 text-brand-green">
                                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5m-3 0V21m0-11.422L2.25 10.5 12 5.25l9.75 5.25-9.75 5.25Zm0 0V21m0-4.5H9.75M12 9V3M12 9l3-1.5M12 9l-3-1.5M12 9l3 1.5M12 9l-3 1.5M12 9V3M12 9l3 1.5" /></svg>
                                    </span>
                                </div>
                                <div class="mt-6 flex flex-col flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Para Comerciantes</h3>
                                    <p class="mt-2 text-base text-gray-600 dark:text-gray-300 flex-1">
                                        Registra tu negocio, detalla tus productos o servicios y llega a miles de nuevos clientes en tu zona.
                                    </p>
                                    @guest
                                    <a href="{{ route('register') }}" class="mt-6 font-semibold text-brand-green dark:text-green-400">Crear cuenta de comerciante &rarr;</a>
                                    @endguest
                                </div>
                            </div>

                            <!-- Card 2: Clientes --><div class="flex flex-col rounded-2xl bg-gray-50 dark:bg-gray-800 p-8 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 transition-transform duration-300 hover:scale-[1.03] hover:shadow-2xl">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900 text-brand-blue">
                                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                    </span>
                                </div>
                                <div class="mt-6 flex flex-col flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Para Clientes</h3>
                                    <p class="mt-2 text-base text-gray-600 dark:text-gray-300 flex-1">
                                        Busca comercios por categoría, ubicación o servicios y encuentra exactamente lo que buscas, ¡cerca tuyo!
                                    </p>
                                    @guest
                                    <a href="{{ route('register') }}" class="mt-6 font-semibold text-brand-blue dark:text-blue-400">Crear cuenta de cliente &rarr;</a>
                                    @endguest
                                </div>
                            </div>

                            <!-- Card 3: Conexión --><div class="flex flex-col rounded-2xl bg-gray-50 dark:bg-gray-800 p-8 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700 transition-transform duration-300 hover:scale-[1.03] hover:shadow-2xl">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400">
                                        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.28.086 2.54.245 3.764L3.5 19.5a2 2 0 002 2h13a2 2 0 002-2l.255-1.226c.16-.924.245-1.874.245-2.836C20.5 8.243 16.342 4 11.25 4S2 8.243 2 13.5c0 1.05.111 2.072.32 3.056l.178.852c.24.974.623 1.87 1.144 2.645A2 2 0 005.5 22h13a2 2 0 002-2l.255-1.226c.16-.924.245-1.874.245-2.836C20.5 8.243 16.342 4 11.25 4S2 8.243 2 13.5zm2.75 0c0-3.313 3.357-6 7.5-6s7.5 2.687 7.5 6m-7.5 0h.008v.008H12V13.5Zm-4.5 0h.008v.008H7.5V13.5Zm9 0h.008v.008H16.5V13.5Z" /></svg>
                                    </span>
                                </div>
                                <div class="mt-6 flex flex-col flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Conexión Directa</h3>
                                    <p class="mt-2 text-base text-gray-600 dark:text-gray-300 flex-1">
                                        Simplificamos el proceso. Facilitamos la conexión directa entre el negocio y el cliente sin intermediarios.
                                    </p>
                                    @auth
                                    <a href="{{ route('dashboard') }}" class="mt-6 font-semibold text-purple-600 dark:text-purple-400">Ir a mi panel &rarr;</a>
                                    @endauth
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </main>

            <!-- ================================= --><!-- ===== FOOTER (PIÉ DE PÁGINA) ===== --><!-- ================================= --><footer class="bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                    <p class="text-center text-xs leading-5 text-gray-500 dark:text-gray-400">
                        &copy; {{ date('Y') }} LocalCommers. Todos los derechos reservados.
                    </p>
                </div>
            </footer>
        </div>

    </body>
</html>

