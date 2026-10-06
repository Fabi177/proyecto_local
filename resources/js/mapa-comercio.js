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
const CENTRO_ALEM = [-27.6, -55.32]; // Leandro N. Alem: el mapa y las sugerencias arrancan por acá
const ARGENTINA = '-73.6,-55.1,-53.6,-21.7'; // minLon,minLat,maxLon,maxLat
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
    const texto = [linea1, p.district, ciudad, p.state].filter(Boolean).join(', ');
    return { texto, linea1, props: p, lat: f.geometry.coordinates[1], lng: f.geometry.coordinates[0] };
};

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
            tmpDir: '',
            tmpLoc: '',
            dirAuto: true,
            locAuto: true,
            q: '',
            items: [],
            abierto: false,
            activo: -1,
            buscando: false,
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
                return this.tmpLat !== null && this.tmpDir.trim() !== '' && this.tmpLoc !== '';
            },

            abrir() {
                this.tmpLat = this.lat;
                this.tmpLng = this.lng;
                this.tmpDir = this.direccion;
                this.tmpLoc = this.localidadId;
                this.dirAuto = this.direccion === '';
                this.locAuto = this.localidadId === '';
                this.q = '';
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
                    mapa.setView(CENTRO_ALEM, 14);
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
                const nombres = [props.city, props.town, props.village, props.locality, props.district, props.county]
                    .filter(Boolean)
                    .map(norm);
                const porNombre = this.localidades.find((l) => nombres.includes(norm(l.nombre)));
                if (porNombre) return String(porNombre.id);

                const cp = String(props.postcode ?? '').match(/\d{4}/)?.[0];
                if (cp) {
                    const mismas = this.localidades.filter((l) => String(l.codigo_postal) === cp);
                    if (mismas.length === 1) return String(mismas[0].id);
                }
                return '';
            },

            aplicarEncontrada(item) {
                if (this.dirAuto || this.tmpDir.trim() === '') {
                    this.tmpDir = item.linea1 || item.texto;
                    this.dirAuto = true;
                }
                const loc = this.localidadDe(item.props);
                if (loc && (this.locAuto || this.tmpLoc === '')) {
                    this.tmpLoc = loc;
                    this.locAuto = true;
                }
            },

            // Al marcar un punto a mano o arrastrar el marcador, se busca la dirección más cercana.
            async completarDesdePunto(lat, lng) {
                try {
                    const params = new URLSearchParams({ lat, lon: lng, lang: 'es', limit: '1' });
                    const r = await fetch(`${PHOTON}/reverse?${params}`);
                    if (!r.ok) return;
                    const datos = await r.json();
                    if (datos.features?.length) this.aplicarEncontrada(armarItem(datos.features[0]));
                } catch (e) {
                    /* sin conexión al buscador: queda lo que ya estaba y el comerciante lo completa a mano */
                }
            },

            // ----- Sugerencias mientras se escribe (Photon, sobre datos de OpenStreetMap) -----
            escribir() {
                clearTimeout(timer);
                if (ctl) ctl.abort();
                this.aviso = '';
                if (this.q.trim().length < MIN_LETRAS) {
                    this.items = [];
                    this.abierto = false;
                    return;
                }
                timer = setTimeout(() => this.consultar(this.q.trim()), 450);
            },

            async consultar(texto) {
                ctl = new AbortController();
                this.buscando = true;
                try {
                    const params = new URLSearchParams({
                        q: texto, limit: '6', lang: 'es', bbox: ARGENTINA, lat: CENTRO_ALEM[0], lon: CENTRO_ALEM[1],
                    });
                    const r = await fetch(`${PHOTON}/api/?${params}`, { signal: ctl.signal });
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    const datos = await r.json();
                    this.items = (datos.features || []).map(armarItem).filter((i) => i.texto);
                    this.activo = -1;
                    this.abierto = this.items.length > 0;
                    this.aviso = this.items.length ? '' : 'No encontramos esa dirección. Probá agregando la ciudad o marcala en el mapa.';
                } catch (e) {
                    if (e.name !== 'AbortError') this.aviso = 'No se pudieron cargar sugerencias. Marcá el punto en el mapa y escribí la dirección abajo.';
                } finally {
                    this.buscando = false;
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
                this.q = item.texto;
                this.abierto = false;
                this.activo = -1;
                // Una dirección nueva reemplaza lo anterior: la localidad se vuelve a deducir de ella
                // y, si no coincide con el catálogo, el comerciante la elige a mano.
                this.dirAuto = true;
                this.locAuto = true;
                this.tmpDir = '';
                this.tmpLoc = '';
                this.colocar(item.lat, item.lng, 18);
                this.aplicarEncontrada(item);
            },

            limpiarBusqueda() {
                this.q = '';
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
                        this.tmpDir.trim() === '' ? 'escribir la dirección (calle y número)' : null,
                        this.tmpLoc === '' ? 'elegir la localidad' : null,
                    ].filter(Boolean).join(', ') + '.';
                    return;
                }
                this.lat = Number(this.tmpLat.toFixed(7));
                this.lng = Number(this.tmpLng.toFixed(7));
                this.direccion = this.tmpDir.trim();
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
