/**
 * Autocompletado (sugerencias en vivo) de los buscadores públicos de comercios.
 *
 * Mientras la persona escribe, aparece una lista con los comercios que coinciden y al
 * elegir uno se va directo a su perfil. Enter SIN elegir ninguna sugerencia hace la
 * búsqueda normal (el formulario se envía como siempre).
 *
 * Uso en una vista (ver resources/views/comercios/partials/sugerencias.blade.php):
 *   <div x-data="autocompletadoComercios({ url: @js(route('comercios.sugerencias')) })" @click.outside="cerrar()">
 *       <input type="text" name="search" x-bind="entrada" ...>
 *       @include('comercios.partials.sugerencias')
 *   </div>
 *
 * Detalles:
 *  - Espera un momento después de la última tecla antes de consultar (no una consulta por letra).
 *  - Si la respuesta llega tarde y el texto ya cambió, se descarta.
 *  - Con menos de 2 letras no consulta. Si el servidor falla, la lista simplemente no aparece.
 *  - Teclado: flechas para moverse, Enter para ir al comercio elegido, Escape o Tab para cerrar.
 */
export function registrarAutocompletadoComercios(Alpine) {
    Alpine.data('autocompletadoComercios', ({ url, minimo = 2, espera = 250 } = {}) => ({
        url,
        minimo,
        espera,
        items: [],
        abierto: false,
        activo: -1,
        uid: Math.random().toString(36).slice(2, 8),
        temporizador: null,
        controlador: null,

        // Atributos y eventos que se le aplican al <input> con x-bind="entrada"
        entrada: {
            ['@input']() {
                this.escribir();
            },
            ['@keydown.down.prevent']() {
                this.mover(1);
            },
            ['@keydown.up.prevent']() {
                this.mover(-1);
            },
            ['@keydown.enter'](evento) {
                this.enter(evento);
            },
            ['@keydown.escape']() {
                this.cerrar();
            },
            ['@keydown.tab']() {
                this.cerrar();
            },
            ['@focus']() {
                if (this.items.length > 0) this.abierto = true;
            },
            [':aria-expanded']() {
                return this.abierto ? 'true' : 'false';
            },
            [':aria-controls']() {
                return this.listaId;
            },
            [':aria-activedescendant']() {
                return this.activo >= 0 ? this.idOpcion(this.activo) : null;
            },
        },

        get listaId() {
            return `sugerencias-${this.uid}`;
        },

        idOpcion(indice) {
            return `sugerencia-${this.uid}-${indice}`;
        },

        // Texto actual del campo, sin espacios de más
        texto() {
            const campo = this.$root.querySelector('input[name="search"]');
            return String(campo?.value ?? '').replace(/\s+/g, ' ').trim();
        },

        escribir() {
            const texto = this.texto();
            this.activo = -1;
            clearTimeout(this.temporizador);

            if (texto.length < this.minimo) {
                this.cancelarConsulta();
                this.items = [];
                this.abierto = false;
                return;
            }

            this.temporizador = setTimeout(() => this.consultar(texto), this.espera);
        },

        cancelarConsulta() {
            if (this.controlador) {
                this.controlador.abort();
                this.controlador = null;
            }
        },

        async consultar(texto) {
            this.cancelarConsulta();
            const controlador = new AbortController();
            this.controlador = controlador;

            try {
                const respuesta = await fetch(`${this.url}?q=${encodeURIComponent(texto)}`, {
                    headers: { Accept: 'application/json' },
                    signal: controlador.signal,
                });
                if (!respuesta.ok) throw new Error('respuesta ' + respuesta.status);

                const datos = await respuesta.json();

                // Si mientras esperábamos la persona siguió escribiendo, esta respuesta ya no sirve
                if (this.texto() !== texto) return;

                this.items = Array.isArray(datos.sugerencias) ? datos.sugerencias : [];
                this.activo = -1;
                this.abierto = true;
            } catch (error) {
                if (error?.name === 'AbortError') return;
                this.items = [];
                this.abierto = false;
            }
        },

        mover(direccion) {
            if (this.items.length === 0) return;
            this.abierto = true;

            const total = this.items.length;
            if (this.activo === -1) {
                this.activo = direccion > 0 ? 0 : total - 1;
            } else {
                this.activo = (this.activo + direccion + total) % total;
            }
        },

        // Enter: si hay una sugerencia elegida con las flechas, se va a ese comercio.
        // Si no, no se hace nada acá y el formulario se envía normal (búsqueda completa).
        enter(evento) {
            if (this.abierto && this.activo >= 0 && this.items[this.activo]) {
                evento.preventDefault();
                window.location.href = this.items[this.activo].url;
            }
        },

        cerrar() {
            clearTimeout(this.temporizador);
            this.cancelarConsulta();
            this.abierto = false;
            this.activo = -1;
        },
    }));
}
