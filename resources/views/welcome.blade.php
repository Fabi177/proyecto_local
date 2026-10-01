<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LocalCommers - Inicio</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2">

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

            /* ===== Portada: foto de L. N. Alem + buscador ===== */
            .hero { position: relative; min-height: 100vh; display: flex; flex-direction: column; color: #fff; background: #0c0a09;
                --barra: 7rem;
                /* tamaño que ocupa la foto (cover) y posición de la línea bajo "L. N. ALEM" (43,14 % de la foto, alineada al 30 %) */
                --foto: max(100vh, calc(100vw / 2));
                --linea: calc((100vh - var(--foto)) * .3 + var(--foto) * .4314); }
            .hero-fondo { position: absolute; top: 0; left: 0; right: 0; height: 100vh; overflow: hidden; z-index: 0;
                background: #222 url("{{ asset('imagenes/fondo.jpg') }}") center 30% / cover no-repeat; }
            /* degradé: deja ver las letras de la foto arriba y oscurece abajo, donde va el buscador */
            .hero-fondo::after { content: ""; position: absolute; inset: 0;
                background: linear-gradient(180deg, rgba(0,0,0,.25) 0%, rgba(0,0,0,.45) 30%, rgba(0,0,0,.72) 60%, rgba(0,0,0,.88) 100%); }
            .hero-barra { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 2rem; }
            .hero-barra img { height: 4.5rem; width: auto; display: block; }
            .hero-barra nav { display: flex; gap: .75rem; align-items: center; }
            .hero-barra nav a { text-decoration: none; font-weight: 600; font-size: .875rem; padding: .625rem .875rem; border-radius: .375rem; color: #fff; }
            .hero-barra nav a:hover { background: rgba(255,255,255,.12); }
            .hero-barra nav a.registro { background: #fff; color: #2ecc71; }
            .hero-barra nav a.registro:hover { background: #f3f4f6; }

            .hero-centro { position: relative; z-index: 2; flex: 1; display: flex; flex-direction: column; justify-content: flex-start; align-items: center;
                text-align: center; padding: max(1rem, calc(var(--linea) + 1.75rem - var(--barra))) 1.25rem 4vh; }
            .hero-centro h1 { margin: 0; font-weight: 900; letter-spacing: -.02em; line-height: 1.05; font-size: clamp(2rem, 4.6vw, 3.6rem); text-shadow: 0 2px 12px rgba(0,0,0,.5); }
            .hero-centro p.sub { margin: .8rem 0 1.4rem; font-size: clamp(1rem, 1.6vw, 1.2rem); color: #f3f4f6; text-shadow: 0 1px 6px rgba(0,0,0,.5); }

            .hero-buscador { position: relative; width: min(44rem, 100%); text-align: left; }
            .hero-campo { display: flex; align-items: center; background: #fff; border-radius: 999px; box-shadow: 0 10px 30px rgba(0,0,0,.45); border: 3px solid transparent; }
            .hero-campo:focus-within { border-color: #2ecc71; }
            .hero-campo button { flex: none; display: grid; place-items: center; width: 3.4rem; height: 3.4rem; margin: .2rem; border: 0; cursor: pointer; border-radius: 50%; background: #2ecc71; color: #fff; }
            .hero-campo input { flex: 1; min-width: 0; border: 0; background: transparent; font: inherit; font-size: 1.05rem; color: #111827; padding: 1rem 1.25rem 1rem .75rem; box-shadow: none; }
            .hero-campo input:focus { outline: 0; box-shadow: none; }
            .hero-campo input::placeholder { color: #6b7280; }
            .hero-sug { position: absolute; left: 0; right: 0; top: calc(100% + .5rem); z-index: 10; margin: 0; padding: .35rem; list-style: none; background: #fff; color: #1f2937;
                border-radius: 1rem; box-shadow: 0 18px 40px rgba(0,0,0,.4); max-height: 22rem; overflow-y: auto; }
            .hero-sug[hidden] { display: none; }
            .hero-sug li a { display: flex; align-items: center; gap: .75rem; padding: .6rem .75rem; border-radius: .65rem; text-decoration: none; color: inherit; }
            .hero-sug li[aria-selected="true"] a, .hero-sug li a:hover { background: #f3f4f6; }
            .hero-sug .ini { flex: none; width: 2.5rem; height: 2.5rem; border-radius: .5rem; background: #e5e7eb; display: grid; place-items: center; font-weight: 700; color: #6b7280; overflow: hidden; }
            .hero-sug .ini img { width: 100%; height: 100%; object-fit: contain; background: #fff; }
            .hero-sug b { display: block; font-weight: 600; }
            .hero-sug small { display: block; color: #6b7280; }
            .hero-sug .vacio { padding: .8rem .9rem; color: #6b7280; font-size: .9rem; }

            .hero-rubros { display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem; margin: 1.2rem 0 0; padding: 0; list-style: none; }
            .hero-rubros a { display: inline-flex; gap: .45rem; align-items: center; text-decoration: none; color: #fff; font-weight: 600; font-size: .9rem; padding: .55rem 1rem; border-radius: 999px;
                background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.38); backdrop-filter: blur(4px); }
            .hero-rubros a:hover { background: rgba(255,255,255,.28); }
            .hero-todos { margin-top: 1rem; font-weight: 600; font-size: .9rem; color: #fff; }
            .hero-comerciante { margin-top: 1.5rem; font-size: .95rem; color: #e5e7eb; }
            .hero-comerciante a { font-weight: 700; color: #fff; text-underline-offset: 3px; }

            @media (max-height: 760px) and (min-width: 641px) {
                .hero-centro h1 { font-size: clamp(1.8rem, 3.6vw, 2.6rem); }
                .hero-centro p.sub { margin: .5rem 0 1rem; }
                .hero-rubros { margin-top: .8rem; }
                .hero-comerciante { margin-top: 1rem; }
                .hero-campo button { width: 3rem; height: 3rem; }
            }
            @media (max-width: 640px) {
                .hero { --barra: 5.4rem; }
                .hero-barra { padding: 1rem; }
                .hero-barra img { height: 3.4rem; }
                .hero-campo input { font-size: 1rem; }
            }
        </style>
    </head>
    <body class="bg-white dark:bg-gray-900 antialiased">

        <div class="relative min-h-screen w-full">

            <!-- ================================= --><!-- ===== PORTADA: FOTO + BUSCADOR ===== --><!-- ================================= -->
            <header class="hero">
                <div class="hero-fondo" role="img" aria-label="Vista aérea de Leandro N. Alem al atardecer"></div>

                <div class="hero-barra">
                    <a href="{{ url('/') }}" aria-label="LocalCommers, inicio"><img src="{{ asset('imagenes/logo.png') }}" alt="LocalCommers"></a>
                    <nav aria-label="Cuenta">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}">Mi Panel</a>
                            @else
                                <a href="{{ route('login') }}">Ingresar</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="registro">Registrarse</a>
                                @endif
                            @endauth
                        @endif
                    </nav>
                </div>

                <div class="hero-centro">
                    <h1>Conecta, Descubre, Crece.</h1>
                    <p class="sub">Busca comercios, servicios y rubros de L. N. Alem.</p>

                    <!-- Buscador público: no pide registrarse -->
                    <div class="hero-buscador" id="hero-buscador">
                        <form action="{{ route('comercios.index') }}" method="GET" role="search">
                            <div class="hero-campo">
                                <input id="hero-q" type="search" name="search" autocomplete="off" role="combobox"
                                       aria-autocomplete="list" aria-expanded="false" aria-controls="hero-sug"
                                       aria-label="Buscar comercios" placeholder="Buscar Restaurantes, Ferreterías, Servicios...">
                                <button type="submit" aria-label="Buscar">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                </button>
                            </div>
                        </form>
                        <ul class="hero-sug" id="hero-sug" role="listbox" aria-label="Sugerencias de comercios" hidden></ul>
                    </div>

                    <ul class="hero-rubros" aria-label="Rubros populares">
                        <li><a href="{{ route('comercios.index', ['rubro' => 'Restaurante']) }}">🍽️ Restaurantes</a></li>
                        <li><a href="{{ route('comercios.index', ['rubro' => 'Indumentaria']) }}">👕 Indumentaria</a></li>
                        <li><a href="{{ route('comercios.index', ['rubro' => 'Farmacia']) }}">⚕️ Farmacias</a></li>
                        <li><a href="{{ route('comercios.index', ['rubro' => 'Ferreteria']) }}">🛠️ Ferreterías</a></li>
                    </ul>
                    <a class="hero-todos" href="{{ route('comercios.index') }}">Ver todos los comercios</a>

                    @guest
                        <p class="hero-comerciante">¿Tienes un comercio? <a href="{{ route('register') }}">Súmalo a LocalCommers</a></p>
                    @endguest
                </div>
            </header>

            <main>
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


        <script>
            // Sugerencias en vivo del buscador de la portada (usa la ruta pública comercios.sugerencias).
            (function () {
                var URL_SUG = @json(route('comercios.sugerencias'));
                var q = document.getElementById('hero-q');
                var lista = document.getElementById('hero-sug');
                var caja = document.getElementById('hero-buscador');
                var items = [], activo = -1, timer = null, ctl = null;

                function cerrar() { lista.hidden = true; q.setAttribute('aria-expanded', 'false'); activo = -1; }
                function marcar() {
                    Array.prototype.forEach.call(lista.querySelectorAll('li[role=option]'), function (li, i) {
                        li.setAttribute('aria-selected', i === activo ? 'true' : 'false');
                    });
                }
                function pintar(datos) {
                    items = datos; lista.textContent = ''; activo = -1;
                    datos.forEach(function (x) {
                        var li = document.createElement('li'), a = document.createElement('a'), ini = document.createElement('span'),
                            w = document.createElement('span'), b = document.createElement('b'), s = document.createElement('small');
                        li.setAttribute('role', 'option'); a.href = x.url; ini.className = 'ini';
                        if (x.logo) { var im = document.createElement('img'); im.src = x.logo; im.alt = ''; ini.appendChild(im); }
                        else { ini.textContent = String(x.nombre || '?').charAt(0).toUpperCase(); }
                        b.textContent = x.nombre; s.textContent = [x.rubro, x.direccion].filter(Boolean).join(' · ');
                        w.appendChild(b); w.appendChild(s); a.appendChild(ini); a.appendChild(w); li.appendChild(a); lista.appendChild(li);
                    });
                    if (!datos.length) {
                        var v = document.createElement('li'); v.className = 'vacio'; v.textContent = 'No encontramos sugerencias. Presiona Enter para buscar igual.'; lista.appendChild(v);
                    }
                    lista.hidden = false; q.setAttribute('aria-expanded', 'true');
                }
                function buscar() {
                    var t = q.value.trim();
                    if (t.length < 2) { cerrar(); return; }
                    if (ctl) ctl.abort();
                    ctl = new AbortController();
                    fetch(URL_SUG + '?q=' + encodeURIComponent(t), { signal: ctl.signal, headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.ok ? r.json() : { sugerencias: [] }; })
                        .then(function (j) { pintar(j.sugerencias || []); })
                        .catch(function (e) { if (e.name !== 'AbortError') cerrar(); });
                }
                q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(buscar, 200); });
                q.addEventListener('focus', function () { if (lista.children.length) lista.hidden = false; });
                q.addEventListener('keydown', function (e) {
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        if (!items.length || lista.hidden) return;
                        e.preventDefault();
                        var d = e.key === 'ArrowDown' ? 1 : -1;
                        activo = activo === -1 ? (d > 0 ? 0 : items.length - 1) : (activo + d + items.length) % items.length;
                        marcar();
                    } else if (e.key === 'Enter' && activo > -1 && items[activo]) {
                        e.preventDefault(); window.location.href = items[activo].url;
                    } else if (e.key === 'Escape' || e.key === 'Tab') { cerrar(); }
                });
                document.addEventListener('click', function (e) { if (!caja.contains(e.target)) cerrar(); });
            })();
        </script>
    </body>
</html>

