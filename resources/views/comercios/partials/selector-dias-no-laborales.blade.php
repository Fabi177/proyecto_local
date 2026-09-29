{{--
    Selector de días no laborales: fechas puntuales de cierre + "Cerramos los feriados nacionales".

    Se incluye en comercios/create y comercios/edit, DENTRO del <form>.
    La lógica está en resources/js/horarios-pagos.js (componente Alpine "diasNoLaborales").

    Campos que viajan en el formulario:
      - dias_cierre       -> (oculto) JSON con las fechas cargadas
      - dias_no_laborales -> (oculto) texto resumen que ve el cliente
      - cierra_feriados   -> checkbox real (llega solo si está tildado, igual que ingreso_discapacitados)
    Los campos date/text de "Agregar" NO tienen "name": no viajan.
    Todos los <button> llevan type="button" y Enter dentro de los campos agrega la fecha (no envía el formulario).
--}}
@php
    $cierresIniciales = old('dias_cierre', isset($comercio) ? $comercio->dias_cierre : null);

    // Tras un error de validación, un checkbox destildado no aparece en old(): por eso se mira si hubo old input.
    $feriadosInicial = session()->hasOldInput()
        ? (bool) old('cierra_feriados')
        : (isset($comercio) ? (bool) $comercio->cierra_feriados : true);
@endphp

<div class="input-wrapper"
     x-data="diasNoLaborales({
        cierres: @js($cierresIniciales),
        feriados: @js($feriadosInicial),
        legacy: @js(isset($comercio) ? $comercio->dias_no_laborales : null),
     })">

    <span class="input-label">{{ __('Días no laborales') }}</span>
    <p class="mt-1 text-sm text-gray-500">
        Los días de la semana que cerrás ya se marcan en los horarios. Acá cargá fechas puntuales:
        vacaciones, feriados puente, cierres por inventario.
    </p>

    {{-- Campos ocultos. Si solo existe el texto libre anterior, se deshabilitan para no pisarlo. --}}
    <input type="hidden" name="dias_cierre" :value="json" :disabled="conservarAnterior">
    <input type="hidden" name="dias_no_laborales" :value="resumenTexto" :disabled="conservarAnterior">

    {{-- Aviso para comercios que ya tenían los días escritos a mano --}}
    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800"
       x-show="textoAnterior" style="display: none;">
        Tus días no laborales actuales están guardados como texto:
        <span class="font-semibold whitespace-pre-line" x-text="textoAnterior"></span><br>
        Cargá las fechas acá abajo para reemplazarlos. Mientras no cargues nada, se mantienen como están.
    </p>

    {{-- Formulario para agregar una fecha o un rango --}}
    <div class="mt-3 grid grid-cols-1 sm:grid-cols-[auto_auto_1fr_auto] gap-3 items-end">
        <div>
            <label for="cierre-desde" class="block text-xs text-gray-500 mb-1">Desde</label>
            <input id="cierre-desde" type="date" x-model="desde" x-on:keydown.enter.prevent="agregar()"
                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
        </div>
        <div>
            <label for="cierre-hasta" class="block text-xs text-gray-500 mb-1">Hasta (opcional)</label>
            <input id="cierre-hasta" type="date" x-model="hasta" x-on:keydown.enter.prevent="agregar()"
                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
        </div>
        <div>
            <label for="cierre-motivo" class="block text-xs text-gray-500 mb-1">Motivo (opcional)</label>
            <input id="cierre-motivo" type="text" x-model="motivo" x-on:keydown.enter.prevent="agregar()"
                   maxlength="40" placeholder="Ej: Vacaciones"
                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
        </div>
        <button type="button" x-on:click="agregar()"
                class="px-4 py-2 bg-[var(--primary-green)] text-white text-sm font-semibold rounded-lg transition hover:bg-green-600">
            Agregar
        </button>
    </div>

    <p class="mt-2 text-sm text-red-600" x-show="mensaje" x-text="mensaje" style="display: none;"></p>

    {{-- Fechas cargadas --}}
    <div class="mt-3 flex flex-wrap gap-2">
        <template x-for="(c, i) in cierres" :key="c.a + '-' + i">
            <span class="inline-flex items-center gap-1 rounded-full border border-[var(--primary-green)] bg-green-50 py-1 pl-3 pr-1 text-sm text-green-800">
                <span x-text="rango(c) + (c.w ? ' · ' + c.w : '')"></span>
                <button type="button" x-on:click="quitar(i)" aria-label="Quitar esta fecha"
                        class="px-2 text-base leading-none hover:text-red-600">×</button>
            </span>
        </template>
        <span class="text-sm text-gray-500" x-show="cierres.length === 0" style="display: none;">No cargaste ninguna fecha todavía.</span>
    </div>

    <label class="mt-4 flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="cierra_feriados" value="1" x-model="feriados"
               class="rounded border-gray-300 text-[var(--primary-green)] shadow-sm focus:ring-[var(--primary-green)]">
        Cerramos los feriados nacionales
    </label>

    {{-- Vista previa: cómo lo verá el cliente --}}
    <div class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3 text-sm text-gray-700">
        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Así lo verá el cliente</span>
        <template x-for="linea in resumenLineas" :key="linea">
            <p x-text="linea"></p>
        </template>
        <p class="text-gray-500" x-show="resumenLineas.length === 0" style="display: none;">Sin cierres especiales.</p>
    </div>
</div>
