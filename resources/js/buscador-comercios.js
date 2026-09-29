/**
 * Buscador en vivo de los comercios del panel del comerciante.
 *
 * Funciona en dos partes que comparten el mismo texto de búsqueda:
 *  - el campo de búsqueda (arriba, en el encabezado del panel) escribe en el "store" buscadorComercios;
 *  - la lista de comercios (componente "listaComercios") muestra u oculta cada tarjeta según ese texto.
 *
 * Es solo comodidad visual: no envía nada al servidor, no recarga la página y Enter no hace nada.
 * Cuando el campo está vacío se ven todos los comercios.
 */

// Saca tildes, pasa a minúsculas y junta espacios: "  Ferretería  López " -> "ferreteria lopez"
function normalizar(texto) {
    return String(texto ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}

export function registrarBuscadorComercios(Alpine) {
    Alpine.store('buscadorComercios', {
        q: '',

        // true si TODAS las palabras escritas aparecen en el texto del comercio (en cualquier orden)
        coincide(texto) {
            const buscado = normalizar(this.q);
            if (buscado === '') return true;

            const base = normalizar(texto);
            return buscado.split(' ').every((palabra) => base.includes(palabra));
        },

        limpiar() {
            this.q = '';
        },
    });

    // "textos" = un texto por comercio (nombre + rubro + dirección), en el mismo orden que las tarjetas.
    Alpine.data('listaComercios', (textos = []) => ({
        textos,

        get buscador() {
            return Alpine.store('buscadorComercios');
        },

        visible(indice) {
            return this.buscador.coincide(this.textos[indice] ?? '');
        },

        get cantidadVisible() {
            return this.textos.filter((_, i) => this.visible(i)).length;
        },

        get sinResultados() {
            return this.textos.length > 0 && this.cantidadVisible === 0;
        },
    }));
}
