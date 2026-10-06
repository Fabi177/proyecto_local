/**
 * Sugerencias de dirección mientras el comerciante escribe (formularios de alta y edición).
 *
 * Usa Photon (https://photon.komoot.io), un buscador de direcciones sobre datos de OpenStreetMap
 * pensado para autocompletar. (Nominatim, el que usa el botón "Buscar" del mapa, no permite
 * autocompletar en su servidor público.) Al elegir una sugerencia se completa el campo
 * "Dirección" y se avisa al mapa (evento "direccion-elegida") para que lo ubique y el
 * comerciante confirme que el punto es correcto.
 */
const PHOTON = 'https://photon.komoot.io/api/';
const CENTRO = { lat: -27.6, lon: -55.32 }; // Leandro N. Alem: las sugerencias cercanas salen primero
const ARGENTINA = '-73.6,-55.1,-53.6,-21.7'; // minLon,minLat,maxLon,maxLat
const MIN_LETRAS = 4;

const etiqueta = (p) => {
    const calle = p.street || p.name || '';
    const linea1 = [calle, p.housenumber].filter(Boolean).join(' ');
    const zona = [p.district, p.city || p.town || p.village || p.county, p.state].filter(Boolean);
    return [linea1, ...zona].filter(Boolean).join(', ');
};

export function registrarAutocompletadoDireccion(Alpine) {
    Alpine.data('autocompletadoDireccion', () => {
        let ctl = null;
        let timer = null;

        return {
            items: [],
            abierto: false,
            activo: -1,
            buscando: false,
            aviso: '',

            escribir(texto) {
                clearTimeout(timer);
                if (ctl) ctl.abort();
                this.aviso = '';
                if (texto.trim().length < MIN_LETRAS) {
                    this.items = [];
                    this.abierto = false;
                    return;
                }
                timer = setTimeout(() => this.consultar(texto.trim()), 450);
            },

            async consultar(texto) {
                ctl = new AbortController();
                this.buscando = true;
                try {
                    const params = new URLSearchParams({
                        q: texto, limit: '6', lang: 'es', bbox: ARGENTINA,
                        lat: CENTRO.lat, lon: CENTRO.lon,
                    });
                    const r = await fetch(`${PHOTON}?${params}`, { signal: ctl.signal });
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    const datos = await r.json();
                    this.items = (datos.features || [])
                        .map((f) => ({ texto: etiqueta(f.properties || {}), lat: f.geometry.coordinates[1], lng: f.geometry.coordinates[0] }))
                        .filter((i) => i.texto);
                    this.activo = -1;
                    this.abierto = this.items.length > 0;
                    this.aviso = this.items.length ? '' : 'No encontramos esa dirección. Probá agregando la ciudad o marcala a mano en el mapa.';
                } catch (e) {
                    if (e.name !== 'AbortError') this.aviso = 'No se pudieron cargar sugerencias. Podés escribir la dirección y marcarla en el mapa.';
                } finally {
                    this.buscando = false;
                }
            },

            mover(d) {
                if (!this.abierto || !this.items.length) return;
                this.activo = this.activo === -1 ? (d > 0 ? 0 : this.items.length - 1) : (this.activo + d + this.items.length) % this.items.length;
            },

            elegirActivo(e) {
                if (this.abierto && this.activo > -1) {
                    e.preventDefault();
                    this.elegir(this.items[this.activo]);
                }
            },

            elegir(item) {
                const campo = this.$refs.campo.querySelector('input');
                campo.value = item.texto;
                campo.dispatchEvent(new Event('input', { bubbles: true }));
                this.cerrar();
                // El mapa se abre en ese punto para que el comerciante confirme (o ajuste) la ubicación.
                window.dispatchEvent(new CustomEvent('direccion-elegida', { detail: item }));
            },

            cerrar() {
                this.abierto = false;
                this.activo = -1;
            },
        };
    });
}
