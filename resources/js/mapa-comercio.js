import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import iconoMarcador from 'leaflet/dist/images/marker-icon.png';
import iconoMarcador2x from 'leaflet/dist/images/marker-icon-2x.png';
import sombraMarcador from 'leaflet/dist/images/marker-shadow.png';

// Vite renombra los assets al compilar, así que Leaflet no encuentra solo las
// imágenes del marcador. Se las indicamos explícitamente.
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: iconoMarcador,
    iconRetinaUrl: iconoMarcador2x,
    shadowUrl: sombraMarcador,
});

const TILES_URL = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const TILES_ATTR =
    '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors';

// Centro y zoom del mapa cuando el comercio todavía no tiene ubicación.
// Por defecto muestra toda Argentina; cambialo por las coordenadas de tu ciudad
// (ej: [-34.6037, -58.3816] y zoom 12) si querés que abra más cerca.
const CENTRO_POR_DEFECTO = [-38.4161, -63.6167];
const ZOOM_POR_DEFECTO = 4;

const ID_MAPA_SELECTOR = 'mapa-selector-ubicacion';
const NOMBRE_MODAL = 'selector-ubicacion';

const aNumero = (valor) =>
    valor === null || valor === undefined || valor === '' ? null : Number(valor);
const formato = (n) => Number(n).toFixed(6);

const PHOTON = 'https://photon.komoot.io';
// Por ahora el catálogo de localidades es solo de Misiones, así que el mapa arranca mostrando la
// provincia y las sugerencias de dirección se limitan a ella. Para abrirlo a otras provincias hay
// que ampliar el catálogo (LocalidadSeeder) y cambiar estas constantes.
const CENTRO_MISIONES = [-26.95, -54.65];
const ZOOM_MISIONES = 8;
const MISIONES = '-56.3,-28.4,-53.4,-25.3'; // minLon,minLat,maxLon,maxLat (con un poco de margen)
const MIN_LETRAS = 4;

// "Leandro N. Alem" y "leandro n alem" se tratan igual: sin tildes, mayúsculas ni puntuación.
const norm = (t) =>
    String(t ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();

// Arma el texto de una sugerencia de Photon: "calle número, barrio, ciudad, provincia".
const armarItem = (f) => {
    const p = f.properties || {};
    const linea1 = [p.street || p.name || '', p.housenumber].filter(Boolean).join(' ');
    const ciudad = p.city || p.town || p.village || p.county || '';
    // Sin partes repetidas: Photon a veces informa el mismo nombre como barrio y como ciudad/departamento
    const partes = [];
    for (const parte of [linea1, p.district, ciudad, p.state].filter(Boolean)) {
        if (!partes.some((x) => norm(x) === norm(parte))) partes.push(parte);
    }
    const texto = partes.join(', ');
    return { texto, linea1, props: p, lat: f.geometry.coordinates[1], lng: f.geometry.coordinates[0] };
};

// Las sugerencias se limitan a Misiones, Argentina (el recuadro de búsqueda deja pasar algo de los
// países y provincias vecinas en los bordes, así que se filtra también por lo que informa Photon).
const esDeMisiones = (item) => {
    const p = item.props;
    if (p.countrycode && String(p.countrycode).toUpperCase() !== 'AR') return false;
    return !p.state || norm(p.state) === 'misiones';
};

// Nombre de la ciudad de una dirección encontrada, para avisar cuando no está en el catálogo.
const ciudadDe = (p) => p.city || p.town || p.village || p.locality || '';

// Error de respuesta del buscador (guarda el código HTTP para mostrarlo en el aviso).
const errorDeBuscador = (estado) => Object.assign(new Error(`El buscador respondió ${estado}`), { estado });

export function registrarComponentesMapa(Alpine) {
    /**
     * Ubicación del comercio (alta y edición): UN solo selector para dirección, localidad y mapa.
     * Una ventana con buscador de sugerencias, mapa y marcador arrastrable. Al confirmar se
     * completan juntos los campos ocultos "direccion", "localidad_id", "latitud" y "longitud".
     * Las variables de Leaflet viven en el closure (no en el estado de Alpine, que usa Proxies).
     */
    Alpine.data('selectorUbicacion', (inicial = {}) => {
        let mapa = null;
        let marcador = null;
        let ctl = null;
        let timer = null;

        return {
            // Lo ya confirmado (es lo que viaja en el formulario)
            lat: aNumero(inicial.lat),
            lng: aNumero(inicial.lng),
            direccion: inicial.direccion ?? '',
            localidadId: inicial.localidadId ? String(inicial.localidadId) : '',
            localidades: inicial.localidades ?? [],

            // Lo que se está eligiendo dentro de la ventana
            tmpLat: null,
            tmpLng: null,
            tmpLoc: '',
            dirAuto: true,
            locAuto: true,
            locEstado: '', // '' | 'auto' | 'manual' | 'no-encontrada'
            locNombre: '', // ciudad que informó el buscador cuando no está en el catálogo
            q: '', // la dirección completa tal como se ve en el buscador
            elegido: null, // sugerencia elegida de la lista (con su punto exacto en el mapa)
            items: [],
            abierto: false,
            activo: -1,
            buscando: false,
            ubicando: false,
            aviso: '',
            mensaje: '',

            get completo() {
                return this.lat !== null && this.direccion.trim() !== '' && this.localidadId !== '';
            },

            get etiquetaLocalidad() {
                const l = this.localidades.find((x) => String(x.id) === String(this.localidadId));
                return l ? `${l.nombre} (${l.codigo_postal})` : '';
            },

            get puedeConfirmar() {
                return this.tmpLat !== null && this.q.trim() !== '' && this.tmpLoc !== '';
            },

            // Lo que se guarda como dirección: calle y número. En el buscador se ve la dirección completa
            // ("Tambor de Tacuarí 880, Leandro N. Alem, Misiones"), pero la ciudad y la provincia ya
            // están en la localidad, así que se quitan las partes (separadas por coma) que son una
            // localidad del catálogo, la provincia o lo que informó el buscador. Si no quedara nada, se
            // guarda lo escrito tal cual.
            get direccionLimpia() {
                const texto = this.q.trim();
                const quitar = new Set(['misiones', ...this.localidades.map((l) => norm(l.nombre))]);
                const e = this.elegido?.props;
                if (e) {
                    [e.city, e.town, e.village, e.locality, e.district, e.county, e.state].filter(Boolean).forEach((n) => {
                        quitar.add(norm(n));
                        quitar.add(norm(n).replace(/^departamento /, ''));
                    });
                }
                const partes = texto.split(',').map((x) => x.trim()).filter(Boolean);
                const quedan = partes.filter((x) => !quitar.has(norm(x)));
                return (quedan.length ? quedan : partes).join(', ');
            },

            // Si la dirección no tiene ningún número, se sugiere agregarlo (no bloquea: hay calles "s/n").
            get faltaNumero() {
                return this.q.trim() !== '' && !/\d/.test(this.q);
            },

            // Mensaje bajo el desplegable de localidad.
            get textoLocalidad() {
                const l = this.localidades.find((x) => String(x.id) === String(this.tmpLoc));
                const etiqueta = l ? `${l.nombre} (${l.codigo_postal})` : '';
                if (this.locEstado === 'auto' && etiqueta) {
                    return `✓ Localidad detectada automáticamente: ${etiqueta}. Si no es correcta, cambiala.`;
                }
                if (this.locEstado === 'manual') return 'Localidad elegida a mano.';
                if (this.locEstado === 'no-encontrada') {
                    return this.locNombre
                        ? `No encontramos «${this.locNombre}» en el listado de localidades (por ahora solo Misiones). Elegila a mano.`
                        : 'No pudimos detectar la localidad. Elegila a mano.';
                }
                return 'Se completa sola al elegir una dirección de la lista; también la podés elegir a mano.';
            },

            abrir() {
                this.tmpLat = this.lat;
                this.tmpLng = this.lng;
                this.tmpLoc = this.localidadId;
                this.dirAuto = this.direccion === '';
                this.locAuto = this.localidadId === '';
                this.locEstado = '';
                this.locNombre = '';
                // La dirección ya guardada aparece en el buscador (con su localidad) para poder corregirla
                const guardada = this.localidades.find((l) => String(l.id) === String(this.localidadId));
                this.q = [this.direccion, guardada?.nombre].filter(Boolean).join(', ');
                this.elegido = null;
                this.items = [];
                this.abierto = false;
                this.aviso = '';
                this.mensaje = '';
                this.$dispatch('open-modal', NOMBRE_MODAL);
                this.$nextTick(() => {
                    this.montarMapa();
                    setTimeout(() => this.refrescarMapa(), 350);
                });
            },

            montarMapa() {
                if (!mapa) {
                    mapa = L.map(ID_MAPA_SELECTOR);
                    L.tileLayer(TILES_URL, { maxZoom: 19, attribution: TILES_ATTR }).addTo(mapa);
                    mapa.on('click', (e) => {
                        const p = e.latlng.wrap();
                        this.colocar(p.lat, p.lng);
                        this.completarDesdePunto(p.lat, p.lng);
                    });
                }

                if (this.tmpLat !== null) {
                    this.colocar(this.tmpLat, this.tmpLng, 17);
                } else {
                    if (marcador) {
                        marcador.remove();
                        marcador = null;
                    }
                    mapa.setView(CENTRO_MISIONES, ZOOM_MISIONES);
                }
                mapa.invalidateSize();
            },

            refrescarMapa() {
                if (!mapa) return;
                mapa.invalidateSize();
                if (this.tmpLat !== null) {
                    mapa.setView([this.tmpLat, this.tmpLng]);
                }
            },

            colocar(lat, lng, zoom = null) {
                this.tmpLat = lat;
                this.tmpLng = lng;

                if (!marcador) {
                    marcador = L.marker([lat, lng], { draggable: true }).addTo(mapa);
                    marcador.on('dragend', () => {
                        const p = marcador.getLatLng().wrap();
                        this.tmpLat = p.lat;
                        this.tmpLng = p.lng;
                        this.completarDesdePunto(p.lat, p.lng);
                    });
                } else {
                    marcador.setLatLng([lat, lng]);
                }

                if (zoom !== null) {
                    mapa.setView([lat, lng], zoom);
                }
            },

            // Busca la localidad del catálogo que corresponde a una dirección encontrada:
            // primero por nombre de ciudad y, si no, por código postal (solo si es único).
            localidadDe(props) {
                // Se prueba en orden de importancia: primero la ciudad y por último el departamento.
                const nombres = [props.city, props.town, props.village, props.locality, props.district, props.county]
                    .filter(Boolean)
                    .map(norm);
                for (const nombre of nombres) {
                    const porNombre = this.localidades.find((l) => norm(l.nombre) === nombre);
                    if (porNombre) return String(porNombre.id);
                }

                const cp = String(props.postcode ?? '').match(/\d{4}/)?.[0];
                if (cp) {
                    const mismas = this.localidades.filter((l) => String(l.codigo_postal) === cp);
                    if (mismas.length === 1) return String(mismas[0].id);
                }
                return '';
            },

            // Respaldo que NO depende del buscador de direcciones: si lo escrito trae el nombre de una
            // localidad del catálogo (por ejemplo "Av. Uruguay 1200, Posadas"), esa localidad se elige sola.
            // Se prueba de atrás para adelante ("calle número, ciudad, provincia"). Nunca pisa una
            // localidad elegida a mano.
            detectarLocalidadEscrita() {
                if (!(this.locAuto || this.tmpLoc === '')) return;
                const partes = this.q.split(',').map(norm).filter(Boolean);
                for (let i = partes.length - 1; i >= 0; i--) {
                    const l = this.localidades.find((x) => norm(x.nombre) === partes[i]);
                    if (l) {
                        this.tmpLoc = String(l.id);
                        this.locAuto = true;
                        this.locEstado = 'auto';
                        return;
                    }
                }
            },

            aplicarEncontrada(item) {
                if (this.dirAuto || this.q.trim() === '') {
                    this.q = item.texto || item.linea1;
                    this.elegido = item;
                    this.dirAuto = true;
                }
                // La localidad solo se completa sola si el comerciante no la eligió a mano
                if (this.locAuto || this.tmpLoc === '') {
                    const loc = this.localidadDe(item.props);
                    this.locAuto = true;
                    if (loc) {
                        this.tmpLoc = loc;
                        this.locEstado = 'auto';
                    } else {
                        this.tmpLoc = '';
                        this.locEstado = 'no-encontrada';
                        this.locNombre = ciudadDe(item.props);
                    }
                }
            },

            // Al marcar un punto a mano o arrastrar el marcador, se busca la dirección más cercana.
            async completarDesdePunto(lat, lng) {
                try {
                    // Sin parámetro "lang": Photon usa el idioma del navegador y, si no lo tiene, el local.
                    const params = new URLSearchParams({ lat, lon: lng, limit: '1' });
                    const r = await fetch(`${PHOTON}/reverse?${params}`);
                    if (!r.ok) throw errorDeBuscador(r.status);
                    const datos = await r.json();
                    if (datos.features?.length) this.aplicarEncontrada(armarItem(datos.features[0]));
                } catch (e) {
                    // Sin conexión al buscador: queda lo que ya estaba y el comerciante lo completa a mano
                    console.error('Buscador de direcciones (reverse):', e);
                    this.aviso = 'No pudimos obtener la dirección de ese punto del mapa. Escribila en el buscador y elegí la localidad.';
                }
            },

            // ----- Sugerencias mientras se escribe (Photon, sobre datos de OpenStreetMap) -----
            escribir() {
                clearTimeout(timer);
                if (ctl) ctl.abort();
                this.dirAuto = false; // lo que escribe la persona manda: el mapa ya no lo pisa
                this.aviso = '';
                if (this.locAuto && this.locEstado === 'no-encontrada') {
                    this.locEstado = '';
                    this.locNombre = '';
                }
                this.detectarLocalidadEscrita();
                if (this.q.trim().length < MIN_LETRAS) {
                    this.items = [];
                    this.abierto = false;
                    return;
                }
                timer = setTimeout(() => this.consultar(this.q.trim()), 450);
            },

            // Devuelve las direcciones encontradas (o null si la consulta se canceló o falló).
            async consultar(texto) {
                if (ctl) ctl.abort();
                ctl = new AbortController();
                this.buscando = true;
                try {
                    // Forma documentada por Photon: /api?q=... (sin barra final y sin "lang": con un idioma
                    // que el servidor no tenga, algunas versiones responden con error 400).
                    const params = new URLSearchParams({
                        q: texto, limit: '6', bbox: MISIONES, lat: CENTRO_MISIONES[0], lon: CENTRO_MISIONES[1],
                    });
                    const r = await fetch(`${PHOTON}/api?${params}`, { signal: ctl.signal });
                    if (!r.ok) throw errorDeBuscador(r.status);
                    const datos = await r.json();
                    const encontrados = (datos.features || []).map(armarItem).filter((i) => i.texto && esDeMisiones(i));
                    // Si ya hay una localidad elegida, los resultados de esa localidad van primero
                    // (el orden se mantiene entre los demás: sort es estable)
                    const idLoc = String(this.tmpLoc || '');
                    if (idLoc) {
                        encontrados.sort((a, b) => (this.localidadDe(b.props) === idLoc) - (this.localidadDe(a.props) === idLoc));
                    }
                    this.items = encontrados;
                    this.activo = -1;
                    this.abierto = this.items.length > 0;
                    this.aviso = this.items.length ? '' : 'No encontramos esa dirección en Misiones. Probá agregando la ciudad o marcala en el mapa.';
                    return this.items;
                } catch (e) {
                    if (e.name === 'AbortError') return null;
                    console.error('Buscador de direcciones:', e);
                    this.aviso = e.estado
                        ? `No se pudieron cargar sugerencias (el buscador respondió con el error ${e.estado}). Marcá el punto en el mapa y escribí la dirección en el buscador.`
                        : 'No se pudieron cargar sugerencias (no hay conexión con el buscador de direcciones). Marcá el punto en el mapa y escribí la dirección en el buscador.';
                    return null;
                } finally {
                    this.buscando = false;
                }
            },

            // Botón "Ubicar en el mapa": busca lo escrito y marca el primer resultado en el mapa.
            async ubicar() {
                clearTimeout(timer);
                this.mensaje = '';
                const texto = this.q.trim();
                if (texto.length < MIN_LETRAS) {
                    this.mensaje = `Escribí la dirección (al menos ${MIN_LETRAS} letras) para ubicarla en el mapa.`;
                    return;
                }
                // Si es la dirección que ya se eligió de la lista, se vuelve a ESE punto (sin buscar de nuevo,
                // porque una calle con el mismo nombre en otra ciudad podría llevar a otro lado).
                if (this.elegido && texto === this.elegido.texto) {
                    this.abierto = false;
                    this.colocar(this.elegido.lat, this.elegido.lng, 18);
                    return;
                }
                this.ubicando = true;
                try {
                    const encontrados = await this.consultar(texto);
                    if (encontrados?.length) {
                        this.elegir(encontrados[0]);
                        if (encontrados.length > 1) {
                            // Hay más de una coincidencia: se marca la primera y se dejan las demás a mano
                            this.items = encontrados;
                            this.abierto = true;
                            this.aviso = 'Ubicamos el primer resultado. Si no es el correcto, elegí otro de la lista.';
                        }
                    }
                } finally {
                    this.ubicando = false;
                }
            },

            mover(d) {
                if (!this.abierto || !this.items.length) return;
                this.activo = this.activo === -1 ? (d > 0 ? 0 : this.items.length - 1) : (this.activo + d + this.items.length) % this.items.length;
            },

            elegirActivo(e) {
                e.preventDefault(); // Enter dentro de la ventana nunca envía el formulario
                if (this.abierto && this.items.length) this.elegir(this.items[this.activo > -1 ? this.activo : 0]);
            },

            elegir(item) {
                this.abierto = false;
                this.activo = -1;
                // Una dirección nueva reemplaza lo anterior: la localidad se vuelve a deducir de ella
                // y, si no coincide con el catálogo, el comerciante la elige a mano.
                this.dirAuto = true;
                this.locAuto = true;
                this.tmpLoc = '';
                this.q = '';
                this.colocar(item.lat, item.lng, 18);
                this.aplicarEncontrada(item); // completa el buscador (dirección completa) y la localidad
            },

            // El desplegable de localidad lo cambió la persona: deja de completarse sola
            localidadManual() {
                this.locAuto = false;
                this.locEstado = 'manual';
            },

            limpiarBusqueda() {
                this.q = '';
                this.elegido = null;
                this.dirAuto = true;
                this.items = [];
                this.abierto = false;
                this.aviso = '';
            },

            usarMiUbicacion() {
                if (!navigator.geolocation) {
                    this.mensaje = 'Tu navegador no permite obtener la ubicación. Marcá el punto en el mapa.';
                    return;
                }
                this.mensaje = 'Obteniendo tu ubicación...';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.mensaje = '';
                        this.colocar(pos.coords.latitude, pos.coords.longitude, 18);
                        this.completarDesdePunto(pos.coords.latitude, pos.coords.longitude);
                    },
                    (err) => {
                        this.mensaje =
                            err.code === 1
                                ? 'No hay permiso para leer tu ubicación. Revisá los permisos del navegador (solo funciona en https o localhost) o marcá el punto en el mapa.'
                                : 'No se pudo obtener tu ubicación. Marcá el punto directamente en el mapa.';
                    },
                    { enableHighAccuracy: true, timeout: 10000 },
                );
            },

            confirmar() {
                if (!this.puedeConfirmar) {
                    this.mensaje = 'Para confirmar falta: ' + [
                        this.tmpLat === null ? 'marcar el punto en el mapa' : null,
                        this.q.trim() === '' ? 'escribir la dirección (calle y número)' : null,
                        this.tmpLoc === '' ? 'elegir la localidad' : null,
                    ].filter(Boolean).join(', ') + '.';
                    return;
                }
                this.lat = Number(this.tmpLat.toFixed(7));
                this.lng = Number(this.tmpLng.toFixed(7));
                this.direccion = this.direccionLimpia;
                this.localidadId = this.tmpLoc;
                this.$dispatch('close-modal', NOMBRE_MODAL);
            },

            cancelar() {
                this.$dispatch('close-modal', NOMBRE_MODAL);
            },
        };
    });

    /**
     * Mapa de solo lectura para el perfil público del comercio.
     * Uso: <div x-data="mapaComercio({ lat: ..., lng: ..., nombre: '...' })"></div>
     */
    Alpine.data('mapaComercio', ({ lat, lng, nombre = '' }) => ({
        init() {
            const mapa = L.map(this.$el, {
                scrollWheelZoom: false, // evita que el mapa "atrape" el scroll de la página
                dragging: !L.Browser.mobile, // en celular, un dedo sigue haciendo scroll de la página
            }).setView([lat, lng], 17);

            L.tileLayer(TILES_URL, { maxZoom: 19, attribution: TILES_ATTR }).addTo(mapa);

            const popup = document.createElement('strong');
            popup.textContent = nombre; // textContent evita inyectar HTML
            L.marker([lat, lng]).addTo(mapa).bindPopup(popup);
        },
    }));
}
