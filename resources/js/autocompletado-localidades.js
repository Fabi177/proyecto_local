/**
 * Selector de ciudad / código postal con sugerencias en vivo (buscador público).
 *
 * Mientras la persona escribe aparece una lista de localidades que tienen comercios
 * (por nombre o por código postal: "obera", "3315"). Al elegir una, se guarda su id en un
 * campo oculto y el formulario se envía, así se ven los comercios de esa zona.
 *
 * Uso en una vista (ver resources/views/comercios/partials/sugerencias-localidades.blade.php):
 *   <div x-data="autocompletadoLocalidades({ url: @js(route('localidades.sugerencias')), id: @js($id), texto: @js($texto) })" @click.outside="cerrar()">
 *       <input type="hidden" name="localidad" :value="valorId" :disabled="!valorId">
 *       <input type="text" x-model="texto" x-bind="entrada" ...>
 *       @include('comercios.partials.sugerencias-localidades')
 *   </div>
 *
 * Detalles:
 *  - El campo de texto NO tiene "name": lo que viaja en el formulario es solo el id (campo oculto).
 *  - Si la persona cambia el texto después de elegir una localidad, la elección se descarta.
 *  - Enter en este campo nunca envía el formulario: elige la sugerencia marcada (o la única que haya).
 *  - Espera un momento después de la última tecla y descarta respuestas viejas.
 *  - Si el servidor falla, la lista simplemente no aparece.
 *  - Teclado: flechas para moverse, Enter para elegir, Escape o Tab para cerrar.
 */
export function registrarAutocompletadoLocalidades(Alpine) {
    Alpine.data('autocompletadoLocalidades', ({ url, id = '', texto = '', espera = 250 } = {}) => ({
        url,
        espera,
        valorId: id ? String(id) : '',
        texto: String(texto ?? ''),
        items: [],
        abierto: false,
        activo: -1,
        uid: Math.random().toString(36).slice(2, 8),
        temporizador: null,
        controlador: null,

        // Atributos y eventos que se le aplican al <input> de texto con x-bind="entrada"
        entrada: {
            ['@input']() {
                this.escribir();
            },
            ['@focus']() {
                // Al entrar al campo se ofrecen las opciones (si ya hay una elegida, todas las demás)
                this.consultar(this.valorId ? '' : this.textoLimpio());
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
            return `localidades-${this.uid}`;
        },

        idOpcion(indice) {
            return `localidad-${this.uid}-${indice}`;
        },

        textoLimpio() {
            return String(this.texto ?? '').replace(/\s+/g, ' ').trim();
        },

        escribir() {
            // Si había una localidad elegida y la persona edita el texto, la elección ya no vale
            this.valorId = '';
            this.activo = -1;
            clearTimeout(this.temporizador);
            this.temporizador = setTimeout(() => this.consultar(this.textoLimpio()), this.espera);
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
                const vigente = this.valorId ? '' : this.textoLimpio();
                if (vigente !== texto) return;

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

        // Enter nunca envía el formulario desde este campo. Si hay una sugerencia marcada con las
        // flechas (o una sola en la lista), se elige; si no, no pasa nada.
        enter(evento) {
            evento.preventDefault();
            if (!this.abierto || this.items.length === 0) return;

            if (this.activo >= 0 && this.items[this.activo]) {
                this.elegir(this.items[this.activo]);
            } else if (this.items.length === 1) {
                this.elegir(this.items[0]);
            }
        },

        // Se elige una localidad: se guarda su id y se envía el formulario para ver sus comercios
        elegir(item) {
            // El formulario se toma ANTES de cambiar el estado: al vaciar la lista Alpine quita del
            // DOM el botón que recibió el clic y, después de eso, this.$root ya no existe.
            const formulario = this.$root.closest('form');

            this.cancelarConsulta();
            clearTimeout(this.temporizador);
            this.valorId = String(item.id);
            this.texto = item.etiqueta;
            this.items = [];
            this.abierto = false;
            this.activo = -1;
            this.enviar(formulario);
        },

        // Botón "×": se quita la localidad elegida (y, si había una, se actualizan los resultados)
        limpiar() {
            const formulario = this.$root.closest('form');
            const habiaElegida = this.valorId !== '';
            this.cancelarConsulta();
            clearTimeout(this.temporizador);
            this.valorId = '';
            this.texto = '';
            this.items = [];
            this.abierto = false;
            this.activo = -1;
            if (habiaElegida) this.enviar(formulario);
        },

        // El formulario se pasa como argumento (no se guarda en el estado de Alpine, que envolvería
        // el elemento del DOM en un proxy y requestSubmit() fallaría).
        enviar(formulario) {
            this.$nextTick(() => {
                if (formulario) formulario.requestSubmit();
            });
        },

        cerrar() {
            clearTimeout(this.temporizador);
            this.cancelarConsulta();
            this.abierto = false;
            this.activo = -1;
        },
    }));
}
