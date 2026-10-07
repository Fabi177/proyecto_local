/**
 * Selector de rubros en dos niveles.
 *
 *  1) Se ve la lista corta de categorías principales (🍔 Gastronomía, 🚗 Automotores, ...).
 *  2) Al tocar una categoría recién aparecen sus rubros, para marcar los que correspondan.
 *
 * Lo usan el filtro del dashboard público y el formulario del comerciante. Los rubros marcados
 * viven en "sel" (lista de claves); cada categoría muestra cuántos tiene marcados.
 * Los checkboxes de todas las categorías están siempre en la página (solo se ocultan los paneles),
 * así lo marcado viaja con el formulario aunque su categoría esté cerrada.
 *
 * Se usa con:  x-data="selectorRubros({ seleccionados: [...], etiquetas: {...}, grupos: {...} })"
 * o mezclado con otras cosas del componente:  x-data="{ ...selectorRubros({...}), otra: 1 }"
 */
export function registrarSelectorRubros(Alpine) {
    Alpine.data('selectorRubros', ({ seleccionados = [], etiquetas = {}, grupos = {} } = {}) => ({
        // Claves de los rubros marcados.
        sel: [...seleccionados],
        // clave => etiqueta, para mostrar los marcados.
        etiquetas,
        // categoría => [claves de sus rubros].
        grupos,
        // Categoría abierta en este momento (o null si ninguna).
        abierta: null,

        // Abre la categoría tocada; si ya estaba abierta, la cierra.
        alternar(grupo) {
            this.abierta = this.abierta === grupo ? null : grupo;
        },

        // Cuántos rubros de la categoría están marcados.
        contar(grupo) {
            return (this.grupos[grupo] ?? []).filter((clave) => this.sel.includes(clave)).length;
        },

        etiqueta(clave) {
            return this.etiquetas[clave] ?? clave;
        },

        // Los marcados en el orden del catálogo (no en el orden en que se fueron tocando).
        ordenados() {
            const catalogo = Object.keys(this.etiquetas);
            return [...this.sel].sort((a, b) => catalogo.indexOf(a) - catalogo.indexOf(b));
        },

        quitar(clave) {
            this.sel = this.sel.filter((c) => c !== clave);
        },

        limpiar() {
            this.sel = [];
        },
    }));
}
