// Componentes Alpine de los apartados "Horarios de atención", "Días no laborales" y "Formas de pago".
//
// Funcionan como "selectorLogo" (logo-comercio.js) y "selectorUbicacion" (mapa-comercio.js):
//  - el comerciante arma todo con controles visuales (interruptores, horas, fechas, botones);
//  - el resultado viaja en campos ocultos dentro del formulario;
//  - recién se guarda en el servidor al presionar "Guardar Cambios" / "Registrar".
//
// IMPORTANTE: acá no van clases de Tailwind. Tailwind solo escanea resources/views/**/*.blade.php,
// así que todo el HTML (y sus clases) está en los parciales de resources/views/comercios/partials/.

const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const DIAS_CORTOS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

const MAX_CIERRES = 50; // Debe coincidir con 'max:50' de dias_cierre en ComercioController
const REGEX_HORA = /^\d{2}:\d{2}$/;
const REGEX_FECHA = /^\d{4}-\d{2}-\d{2}$/;

const esVerdadero = (v) => v === true || v === 1 || v === '1' || v === 'true';

// -------------------------------------------------------------------
// Horarios de atención
// -------------------------------------------------------------------

// Lunes a viernes 9 a 18, sábado 9 a 13, domingo cerrado (los mismos valores de la maqueta).
const horarioPorDefecto = () =>
    DIAS.map((_, i) => ({ open: i < 6, t: [i < 5 ? ['09:00', '18:00'] : ['09:00', '13:00']] }));

const horarioCerrado = () => DIAS.map(() => ({ open: false, t: [['09:00', '13:00']] }));

/**
 * Valida y copia una configuración guardada (o devuelta por old() tras un error de validación).
 * Devuelve null si no tiene la forma esperada, para que el componente use un valor seguro.
 */
function sanearHorarios(config) {
    if (!Array.isArray(config) || config.length !== 7) return null;

    const resultado = [];
    for (const dia of config) {
        if (!dia || typeof dia !== 'object' || !Array.isArray(dia.t)) return null;

        const turnos = dia.t
            .filter((t) => Array.isArray(t) && REGEX_HORA.test(t[0] ?? '') && REGEX_HORA.test(t[1] ?? ''))
            .slice(0, 2)
            .map((t) => [t[0], t[1]]);

        resultado.push({ open: esVerdadero(dia.open), t: turnos.length ? turnos : [['09:00', '13:00']] });
    }
    return resultado;
}

const turnoCompleto = (t) => Boolean(t[0]) && Boolean(t[1]);

export function registrarComponentesHorarios(Alpine) {
    /**
     * Horarios de atención: un interruptor por día, hasta 2 turnos por día y atajos rápidos.
     *
     * Parámetros:
     *  - config: horarios guardados (array de 7 días) o null;
     *  - legacy: el texto libre que tenía el comercio antes de existir este selector (o null);
     *  - esNuevo: true en la pantalla de registro (arranca con horarios de ejemplo).
     *    En edición, si el comercio solo tiene texto libre, arranca con todo cerrado para no pisar
     *    ese texto con valores inventados, y se lo muestra como referencia.
     */
    Alpine.data('horariosAtencion', (inicial = {}) => {
        const guardado = sanearHorarios(inicial.config);

        return {
            dias: guardado ?? (inicial.esNuevo ? horarioPorDefecto() : horarioCerrado()),
            textoAnterior: guardado ? null : inicial.legacy ?? null,
            nombres: DIAS,

            alternar(i) {
                this.dias[i].open = !this.dias[i].open;
            },

            agregarTurno(i) {
                if (this.dias[i].t.length < 2) this.dias[i].t.push(['16:00', '20:00']);
            },

            quitarTurno(i, j) {
                if (j > 0) this.dias[i].t.splice(j, 1);
            },

            copiarATodos(i) {
                const origen = this.dias[i];
                this.dias = this.dias.map(() => ({ open: origen.open, t: origen.t.map((t) => [...t]) }));
            },

            preset(nombre) {
                this.textoAnterior = null;
                const base = horarioCerrado();
                if (nombre === 'lv') {
                    base.forEach((d, i) => {
                        if (i < 5) {
                            d.open = true;
                            d.t = [['09:00', '18:00']];
                        }
                    });
                } else if (nombre === 'todos') {
                    base.forEach((d) => {
                        d.open = true;
                        d.t = [['09:00', '21:00']];
                    });
                } else if (nombre === '24') {
                    base.forEach((d) => {
                        d.open = true;
                        d.t = [['00:00', '23:59']];
                    });
                }
                this.dias = base;
            },

            // true mientras el comercio solo tenga su texto libre anterior y no se haya configurado nada nuevo.
            // En ese caso los campos ocultos se deshabilitan (no viajan) y el servidor no toca lo que ya había.
            get conservarAnterior() {
                return this.textoAnterior !== null && this.resumenLineas.length === 0;
            },

            // true si algún día abierto tiene un turno con la hora de apertura o de cierre vacía
            diaConHorasVacias(i) {
                const d = this.dias[i];
                return d.open && d.t.some((t) => !turnoCompleto(t));
            },

            // Valor del campo oculto "horarios_config": se descartan los turnos incompletos.
            get json() {
                return JSON.stringify(
                    this.dias.map((d) => ({ open: d.open, t: d.t.filter(turnoCompleto).map((t) => [t[0], t[1]]) })),
                );
            },

            // Líneas del resumen, agrupando días seguidos con el mismo horario ("Lun a Vie: 09:00 a 18:00 hs")
            get resumenLineas() {
                const clave = (d) => {
                    if (!d.open) return 'Cerrado';
                    const turnos = d.t.filter(turnoCompleto);
                    return turnos.length ? turnos.map((t) => `${t[0]} a ${t[1]}`).join(' y ') + ' hs' : 'Cerrado';
                };

                const todo24 = this.dias.every(
                    (d) => d.open && d.t.length === 1 && d.t[0][0] === '00:00' && d.t[0][1] === '23:59',
                );
                if (todo24) return ['Abierto las 24 hs, todos los días.'];

                if (this.dias.every((d) => clave(d) === 'Cerrado')) return [];

                const lineas = [];
                let inicio = 0;
                for (let i = 1; i <= 7; i++) {
                    if (i === 7 || clave(this.dias[i]) !== clave(this.dias[inicio])) {
                        const cantidad = i - inicio;
                        const rotulo =
                            cantidad === 1
                                ? DIAS_CORTOS[inicio]
                                : cantidad === 2
                                  ? `${DIAS_CORTOS[inicio]} y ${DIAS_CORTOS[i - 1]}`
                                  : `${DIAS_CORTOS[inicio]} a ${DIAS_CORTOS[i - 1]}`;
                        lineas.push(`${rotulo}: ${clave(this.dias[inicio])}`);
                        inicio = i;
                    }
                }
                return lineas;
            },

            // Valor del campo oculto "horarios_atencion" (texto que ve el cliente en el perfil)
            get resumenTexto() {
                return this.resumenLineas.join('\n');
            },
        };
    });
}

// -------------------------------------------------------------------
// Días no laborales (fechas puntuales de cierre)
// -------------------------------------------------------------------

const formatoFecha = (fecha) => fecha.split('-').reverse().join('/'); // 2026-12-24 -> 24/12/2026

const rangoCierre = (c) => (c.b && c.b !== c.a ? `${formatoFecha(c.a)} al ${formatoFecha(c.b)}` : formatoFecha(c.a));

function sanearCierres(lista) {
    if (!Array.isArray(lista)) return [];

    return lista
        .filter((c) => c && REGEX_FECHA.test(c.a ?? ''))
        .slice(0, MAX_CIERRES)
        .map((c) => ({
            a: c.a,
            b: REGEX_FECHA.test(c.b ?? '') && c.b > c.a ? c.b : null,
            w: typeof c.w === 'string' ? c.w.slice(0, 40) : '',
        }))
        .sort((x, y) => (x.a < y.a ? -1 : x.a > y.a ? 1 : 0));
}

export function registrarComponenteDiasNoLaborales(Alpine) {
    /**
     * Fechas de cierre (vacaciones, feriados puente, inventario) + "Cerramos los feriados nacionales".
     *
     * Parámetros:
     *  - cierres: fechas guardadas o null;
     *  - feriados: valor del checkbox de feriados;
     *  - legacy: texto libre anterior del comercio (o null) para mostrarlo como referencia.
     */
    Alpine.data('diasNoLaborales', (inicial = {}) => ({
        cierres: sanearCierres(inicial.cierres),
        feriados: esVerdadero(inicial.feriados),
        textoAnterior: Array.isArray(inicial.cierres) ? null : inicial.legacy ?? null,
        desde: '',
        hasta: '',
        motivo: '',
        mensaje: '',

        agregar() {
            this.mensaje = '';

            if (!this.desde) {
                this.mensaje = 'Elegí al menos la fecha "Desde".';
                return;
            }
            if (this.hasta && this.hasta < this.desde) {
                this.mensaje = '"Hasta" no puede ser anterior a "Desde".';
                return;
            }
            if (this.cierres.length >= MAX_CIERRES) {
                this.mensaje = `Podés cargar hasta ${MAX_CIERRES} fechas de cierre.`;
                return;
            }

            this.cierres.push({
                a: this.desde,
                b: this.hasta && this.hasta !== this.desde ? this.hasta : null,
                w: this.motivo.trim().slice(0, 40),
            });
            this.cierres.sort((x, y) => (x.a < y.a ? -1 : x.a > y.a ? 1 : 0));
            this.desde = this.hasta = this.motivo = '';
        },

        quitar(i) {
            this.cierres.splice(i, 1);
        },

        rango(c) {
            return rangoCierre(c);
        },

        // true mientras el comercio solo tenga su texto libre anterior y no se haya cargado nada nuevo.
        // En ese caso los campos ocultos se deshabilitan (no viajan) y el servidor no toca lo que ya había.
        get conservarAnterior() {
            return this.textoAnterior !== null && this.resumenLineas.length === 0;
        },

        // Valor del campo oculto "dias_cierre"
        get json() {
            return JSON.stringify(this.cierres);
        },

        // Líneas del resumen (también es el valor del campo oculto "dias_no_laborales")
        get resumenLineas() {
            const lineas = [];
            if (this.feriados) lineas.push('Cerrado los feriados nacionales.');
            if (this.cierres.length) {
                const detalle = this.cierres.map((c) => rangoCierre(c) + (c.w ? ` (${c.w})` : '')).join(', ');
                lineas.push(`Cierres: ${detalle}.`);
            }
            return lineas;
        },

        get resumenTexto() {
            return this.resumenLineas.join('\n');
        },
    }));
}

// -------------------------------------------------------------------
// Formas de pago
// -------------------------------------------------------------------

const PAGOS_BASE = [
    ['Efectivo', '💵'],
    ['Tarjeta de débito', '💳'],
    ['Tarjeta de crédito', '💳'],
    ['Transferencia', '🏦'],
    ['Mercado Pago', '📱'],
    ['Cuenta DNI', '📱'],
    ['MODO', '📱'],
    ['Ualá', '📱'],
    ['QR', '🔳'],
];

const MAX_PAGOS_PERSONALIZADOS = 15;
const MAX_LARGO_PAGO = 30;

export function registrarComponenteFormasPago(Alpine) {
    /**
     * Formas de pago: botones para las más comunes y un campo para agregar otras.
     * Se guardan en "formas_pago" como texto separado por comas ("Efectivo, MODO, Naranja X"),
     * que es el mismo formato que ya usaba el campo de texto libre, así que los comercios
     * existentes se cargan sin problema.
     *
     * Parámetro: guardadas = el texto guardado en la base (o null).
     */
    Alpine.data('formasPago', (inicial = {}) => {
        const base = PAGOS_BASE.map((p) => p[0]);
        const guardadas = String(inicial.guardadas ?? '')
            .split(',')
            .map((n) => n.replace(/\s+/g, ' ').trim())
            .filter(Boolean);

        const seleccion = [];
        const personalizadas = [];
        for (const nombre of guardadas) {
            const existente = [...base, ...personalizadas].find((n) => n.toLowerCase() === nombre.toLowerCase());
            if (existente) {
                if (!seleccion.includes(existente)) seleccion.push(existente);
            } else {
                personalizadas.push(nombre.slice(0, MAX_LARGO_PAGO));
                seleccion.push(personalizadas[personalizadas.length - 1]);
            }
        }

        return {
            base: PAGOS_BASE,
            seleccion,
            personalizadas,
            nuevo: '',
            mensaje: '',

            estaElegido(nombre) {
                return this.seleccion.includes(nombre);
            },

            alternar(nombre) {
                this.seleccion = this.estaElegido(nombre)
                    ? this.seleccion.filter((n) => n !== nombre)
                    : [...this.seleccion, nombre];
            },

            // Acepta varios nombres separados por coma ("Naranja X, Cheque")
            agregar() {
                this.mensaje = '';
                const nombres = this.nuevo
                    .split(',')
                    .map((n) => n.replace(/\s+/g, ' ').trim().slice(0, MAX_LARGO_PAGO))
                    .filter(Boolean);
                if (!nombres.length) return;

                const yaEstaban = [];
                for (const nombre of nombres) {
                    const existente = [...PAGOS_BASE.map((p) => p[0]), ...this.personalizadas].find(
                        (n) => n.toLowerCase() === nombre.toLowerCase(),
                    );
                    if (existente) {
                        if (!this.estaElegido(existente)) this.seleccion.push(existente);
                        yaEstaban.push(existente);
                    } else if (this.personalizadas.length >= MAX_PAGOS_PERSONALIZADOS) {
                        this.mensaje = `Podés agregar hasta ${MAX_PAGOS_PERSONALIZADOS} formas de pago propias.`;
                        break;
                    } else {
                        this.personalizadas.push(nombre);
                        this.seleccion.push(nombre);
                    }
                }

                if (yaEstaban.length && !this.mensaje) {
                    this.mensaje = `"${yaEstaban.join('", "')}" ya estaba en la lista: la dejé seleccionada.`;
                }
                this.nuevo = '';
            },

            quitarPersonalizada(nombre) {
                this.personalizadas = this.personalizadas.filter((n) => n !== nombre);
                this.seleccion = this.seleccion.filter((n) => n !== nombre);
            },

            // Elegidas, en el orden en que se muestran (primero las comunes, después las propias)
            get elegidas() {
                return [...PAGOS_BASE.map((p) => p[0]), ...this.personalizadas].filter((n) => this.seleccion.includes(n));
            },

            // Valor del campo oculto "formas_pago"
            get texto() {
                return this.elegidas.join(', ');
            },
        };
    });
}

// Registra los tres componentes. Se llama desde app.js antes de Alpine.start().
export function registrarComponentesHorariosPagos(Alpine) {
    registrarComponentesHorarios(Alpine);
    registrarComponenteDiasNoLaborales(Alpine);
    registrarComponenteFormasPago(Alpine);
}
