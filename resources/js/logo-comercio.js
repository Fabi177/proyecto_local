// Tipos y tamaño permitidos. Deben coincidir con la validación del controlador
// (ComercioController: 'mimes:jpg,jpeg,png,webp' y 'max:2048' = 2 MB).
const TIPOS_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];
const TAMANO_MAXIMO = 2 * 1024 * 1024;

const NOMBRE_MODAL = 'selector-logo';
const ID_INPUT_TMP = 'logo-input-tmp'; // <input type="file"> de la ventana (sin "name": no viaja en el formulario)
const ID_INPUT_FINAL = 'logo-input-final'; // <input type="file" name="logo"> oculto (el que viaja en el formulario)

/**
 * Componente Alpine "selectorLogo".
 *
 * Funciona igual que "selectorUbicacion" (mapa-comercio.js):
 *  - dentro de la ventana modal se elige la imagen y se ve una vista previa (valor PROVISIONAL);
 *  - al presionar "Confirmar logo" la imagen pasa al campo oculto que viaja en el formulario;
 *  - recién se guarda en el servidor cuando se envía el formulario ("Guardar Cambios").
 *
 * Se usa document.getElementById y no $refs porque el modal es otro componente Alpine anidado.
 */
export function registrarComponenteLogo(Alpine) {
    Alpine.data('selectorLogo', (inicial = {}) => ({
        actualUrl: inicial.url ?? null, // logo que ya está guardado en la base (o null)
        confirmadoUrl: null, // vista previa del logo nuevo ya confirmado
        quitarActual: false, // true si el comerciante pidió borrar el logo guardado
        tmpUrl: null, // vista previa provisional (dentro de la ventana)
        tmpArchivo: null,
        mensaje: '',
        arrastrando: false,

        // Imagen que se muestra en el formulario
        get vista() {
            if (this.confirmadoUrl) return this.confirmadoUrl;
            return this.quitarActual ? null : this.actualUrl;
        },

        get resumen() {
            if (this.confirmadoUrl) return 'Logo nuevo seleccionado. Se guardará al enviar el formulario.';
            if (this.quitarActual) return 'Se quitará el logo actual al enviar el formulario.';
            if (this.actualUrl) return 'Este es el logo actual de tu comercio.';
            return 'Todavía no cargaste el logo de tu comercio.';
        },

        get hayLogo() {
            return this.vista !== null;
        },

        abrir() {
            this.limpiarTmp();
            this.mensaje = '';
            this.$dispatch('open-modal', NOMBRE_MODAL);
        },

        elegirArchivo() {
            document.getElementById(ID_INPUT_TMP).click();
        },

        // Se ejecuta cuando el input de la ventana cambia (el usuario eligió un archivo)
        alElegir(evento) {
            this.procesar(evento.target.files[0] ?? null);
        },

        // Se ejecuta al soltar un archivo arrastrado sobre la zona punteada
        alSoltar(evento) {
            this.arrastrando = false;
            const archivo = evento.dataTransfer?.files?.[0] ?? null;
            if (!archivo) return;
            try {
                document.getElementById(ID_INPUT_TMP).files = evento.dataTransfer.files;
            } catch (e) {
                // Si el navegador no permite asignarlo, el archivo igual se usa desde tmpArchivo.
            }
            this.procesar(archivo);
        },

        procesar(archivo) {
            this.mensaje = '';
            this.liberarTmp();

            if (!archivo) return;

            if (!TIPOS_PERMITIDOS.includes(archivo.type)) {
                this.mensaje = 'El archivo debe ser una imagen JPG, PNG o WEBP.';
                this.limpiarTmp();
                return;
            }
            if (archivo.size > TAMANO_MAXIMO) {
                const mb = (archivo.size / 1024 / 1024).toFixed(1);
                this.mensaje = `La imagen pesa ${mb} MB y el máximo es 2 MB. Elegí una más liviana.`;
                this.limpiarTmp();
                return;
            }

            this.tmpArchivo = archivo;
            this.tmpUrl = URL.createObjectURL(archivo);
        },

        confirmar() {
            if (!this.tmpArchivo) return;

            // Copiamos el archivo elegido al input oculto que sí viaja en el formulario.
            const transferencia = new DataTransfer();
            transferencia.items.add(this.tmpArchivo);
            document.getElementById(ID_INPUT_FINAL).files = transferencia.files;

            if (this.confirmadoUrl) URL.revokeObjectURL(this.confirmadoUrl);
            this.confirmadoUrl = this.tmpUrl;
            this.tmpUrl = null; // ahora lo usa confirmadoUrl: no hay que liberarlo
            this.tmpArchivo = null;
            this.quitarActual = false;
            this.$dispatch('close-modal', NOMBRE_MODAL);
        },

        cancelar() {
            this.limpiarTmp();
            this.$dispatch('close-modal', NOMBRE_MODAL);
        },

        // Botón "Quitar logo" del formulario
        quitar() {
            if (this.confirmadoUrl) {
                URL.revokeObjectURL(this.confirmadoUrl);
                this.confirmadoUrl = null;
                document.getElementById(ID_INPUT_FINAL).value = '';
            }
            // Si había un logo guardado en la base, avisamos al servidor que lo borre.
            this.quitarActual = this.actualUrl !== null;
        },

        liberarTmp() {
            if (this.tmpUrl) URL.revokeObjectURL(this.tmpUrl);
            this.tmpUrl = null;
            this.tmpArchivo = null;
        },

        limpiarTmp() {
            this.liberarTmp();
            const input = document.getElementById(ID_INPUT_TMP);
            if (input) input.value = '';
        },
    }));
}
