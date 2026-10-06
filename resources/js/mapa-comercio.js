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

export function registrarComponentesMapa(Alpine) {
    /**
     * Formulario del comerciante (crear / editar).
     * Guarda latitud/longitud en campos ocultos y usa una ventana modal con un
     * mapa para elegirlas. Las variables de Leaflet (mapa, marcador) viven en
     * el closure y NO dentro del estado de Alpine, porque Alpine envuelve el
     * estado en Proxies y eso rompe los objetos de Leaflet.
     */
    Alpine.data('selectorUbicacion', (inicial = {}) => {
        let mapa = null;
        let marcador = null;

        return {
            // Valor confirmado (es lo que viaja en el formulario)
            lat: aNumero(inicial.lat),
            lng: aNumero(inicial.lng),
            // Valor provisional mientras el modal está abierto
            tmpLat: null,
            tmpLng: null,
            busqueda: '',
            buscando: false,
            mensaje: '',
            porConfirmar: false,

            get resumen() {
                if (this.lat === null) {
                    return 'Todavía no marcaste la ubicación de tu comercio.';
                }
                return `Ubicación seleccionada: ${formato(this.lat)}, ${formato(this.lng)}. Se guardará al enviar el formulario.`;
            },

            get seleccion() {
                return this.tmpLat === null ? 'ninguna' : `${formato(this.tmpLat)}, ${formato(this.tmpLng)}`;
            },

            // El comerciante eligió una dirección de las sugerencias: se abre el mapa en ese punto
            // para que confirme (o arrastre el marcador si hace falta).
            desdeDireccion(d) {
                this.tmpLat = d.lat;
                this.tmpLng = d.lng;
                this.busqueda = d.texto;
                this.mensaje = '';
                this.porConfirmar = true;
                this.$dispatch('open-modal', NOMBRE_MODAL);
                this.$nextTick(() => {
                    this.montarMapa();
                    setTimeout(() => this.refrescarMapa(), 350);
                });
            },

            abrir() {
                this.porConfirmar = false;
                this.tmpLat = this.lat;
                this.tmpLng = this.lng;
                this.mensaje = '';
                if (!this.busqueda) {
                    this.busqueda = document.getElementById('direccion')?.value ?? '';
                }
                this.$dispatch('open-modal', NOMBRE_MODAL);
                this.$nextTick(() => {
                    this.montarMapa();
                    // El modal tiene una animación de ~300 ms: recalculamos el tamaño al terminar.
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
                    });
                }

                if (this.tmpLat !== null) {
                    this.colocar(this.tmpLat, this.tmpLng, 17);
                } else {
                    if (marcador) {
                        marcador.remove();
                        marcador = null;
                    }
                    mapa.setView(CENTRO_POR_DEFECTO, ZOOM_POR_DEFECTO);
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
                    });
                } else {
                    marcador.setLatLng([lat, lng]);
                }

                if (zoom !== null) {
                    mapa.setView([lat, lng], zoom);
                }
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

            // Busca la dirección con Nominatim (OpenStreetMap). Solo se ejecuta al
            // hacer clic o Enter (nunca mientras se escribe), como pide su política de uso.
            async buscar() {
                const consulta = this.busqueda.trim();
                if (!consulta || this.buscando) return;

                this.buscando = true;
                this.mensaje = '';
                try {
                    const params = new URLSearchParams({
                        q: consulta,
                        format: 'jsonv2',
                        limit: '1',
                        countrycodes: 'ar',
                        'accept-language': 'es',
                    });
                    const respuesta = await fetch(`https://nominatim.openstreetmap.org/search?${params}`);
                    if (!respuesta.ok) throw new Error(`HTTP ${respuesta.status}`);
                    const resultados = await respuesta.json();

                    if (!resultados.length) {
                        this.mensaje = 'No se encontró esa dirección. Probá agregando la ciudad o marcá el punto en el mapa.';
                        return;
                    }
                    this.colocar(parseFloat(resultados[0].lat), parseFloat(resultados[0].lon), 18);
                } catch (e) {
                    this.mensaje = 'No se pudo buscar la dirección. Marcá el punto directamente en el mapa.';
                } finally {
                    this.buscando = false;
                }
            },

            confirmar() {
                if (this.tmpLat === null) return;
                this.lat = Number(this.tmpLat.toFixed(7));
                this.lng = Number(this.tmpLng.toFixed(7));
                this.$dispatch('close-modal', NOMBRE_MODAL);
            },

            cancelar() {
                this.$dispatch('close-modal', NOMBRE_MODAL);
            },

            quitar() {
                this.lat = null;
                this.lng = null;
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
