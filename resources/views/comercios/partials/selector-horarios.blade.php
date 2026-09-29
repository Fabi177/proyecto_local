{{--
    Selector de horarios de atención.

    Se incluye en comercios/create y comercios/edit, DENTRO del <form>.
    La lógica está en resources/js/horarios-pagos.js (componente Alpine "horariosAtencion").

    Campos ocultos que viajan en el formulario:
      - horarios_config   -> JSON con los 7 días (para volver a cargar el editor al editar)
      - horarios_atencion -> texto resumen que ve el cliente en el perfil ("Lun a Vie: 09:00 a 18:00 hs")
    Los campos date/time de este bloque NO tienen "name": no viajan, solo arman el JSON y el resumen.
    Todos los <button> llevan type="button" para que no envíen el formulario al presionarlos.
--}}
@php
    $configHorarios = old('horarios_config', isset($comercio) ? $comercio->horarios_config : null);
@endphp

<div class="input-wrapper"
     x-data="horariosAtencion({
        config: @js($configHorarios),
        legacy: @js(isset($comercio) ? $comercio->horarios_atencion : null),
        esNuevo: @js(!isset($comercio)),
     })">

    <span class="input-label">{{ __('Horarios de atención') }}</span>
    <p class="mt-1 text-sm text-gray-500">
        Activá los días que abrís y elegí desde y hasta qué hora. Si cerrás al mediodía, agregá un segundo turno.
    </p>

    {{-- Campos que viajan en el formulario. Si solo existe el texto libre anterior, se deshabilitan para no pisarlo. --}}
    <input type="hidden" name="horarios_config" :value="json" :disabled="conservarAnterior">
    <input type="hidden" name="horarios_atencion" :value="resumenTexto" :disabled="conservarAnterior">

    {{-- Aviso para comercios que ya tenían un horario escrito a mano --}}
    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800"
       x-show="textoAnterior" style="display: none;">
        Tu horario actual está guardado como texto:
        <span class="font-semibold whitespace-pre-line" x-text="textoAnterior"></span><br>
        Configurá los días acá abajo para reemplazarlo. Mientras no cargues nada, se mantiene como está.
    </p>

    {{-- Atajos --}}
    <div class="mt-3 flex flex-wrap gap-2">
        <button type="button" x-on:click="preset('lv')"
                class="px-3 py-1.5 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-300 rounded-lg transition hover:border-[var(--primary-green)]">Lun a Vie 9 a 18</button>
        <button type="button" x-on:click="preset('todos')"
                class="px-3 py-1.5 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-300 rounded-lg transition hover:border-[var(--primary-green)]">Todos los días 9 a 21</button>
        <button type="button" x-on:click="preset('24')"
                class="px-3 py-1.5 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-300 rounded-lg transition hover:border-[var(--primary-green)]">24 hs</button>
        <button type="button" x-on:click="preset('limpiar')"
                class="px-3 py-1.5 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-300 rounded-lg transition hover:border-[var(--primary-green)]">Limpiar</button>
    </div>

    {{-- Un renglón por día --}}
    <div class="mt-3 border-y border-gray-200 divide-y divide-gray-200">
        <template x-for="(d, i) in dias" :key="i">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3">
                <span class="w-24 font-semibold text-gray-800" x-text="nombres[i]"></span>

                {{-- Interruptor abierto / cerrado --}}
                <button type="button" role="switch"
                        :aria-checked="d.open"
                        :aria-label="nombres[i] + ' abierto'"
                        x-on:click="alternar(i)"
                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition focus:outline-none focus:ring-2 focus:ring-[var(--primary-green)] focus:ring-offset-2"
                        :class="d.open ? 'bg-[var(--primary-green)]' : 'bg-gray-300'">
                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"
                          :class="d.open ? 'translate-x-5' : 'translate-x-0.5'"></span>
                </button>

                {{-- Turnos --}}
                <div class="flex-1 min-w-[220px]">
                    <template x-if="!d.open">
                        <span class="italic text-gray-500">Cerrado</span>
                    </template>

                    <template x-if="d.open">
                        <div class="space-y-2">
                            <template x-for="(t, j) in d.t" :key="j">
                                <div class="flex flex-wrap items-center gap-2">
                                    <input type="time" x-model="t[0]" aria-label="Hora de apertura"
                                           x-on:keydown.enter.prevent
                                           class="rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
                                    <span class="text-gray-500">a</span>
                                    <input type="time" x-model="t[1]" aria-label="Hora de cierre"
                                           x-on:keydown.enter.prevent
                                           class="rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
                                    <button type="button" x-show="j > 0" x-on:click="quitarTurno(i, j)"
                                            class="text-sm font-medium text-red-600 hover:text-red-800 hover:underline">Quitar turno</button>
                                </div>
                            </template>
                            <p class="text-sm text-red-600" x-show="diaConHorasVacias(i)" style="display: none;">
                                Completá la hora de apertura y de cierre. Los turnos incompletos no se guardan.
                            </p>
                        </div>
                    </template>
                </div>

                {{-- Acciones del día --}}
                <div class="ml-auto flex flex-wrap gap-3 text-sm" x-show="d.open" style="display: none;">
                    <button type="button" x-show="d.t.length < 2" x-on:click="agregarTurno(i)"
                            class="font-medium text-[var(--light-blue)] hover:underline">+ Segundo turno</button>
                    <button type="button" x-on:click="copiarATodos(i)"
                            class="font-medium text-[var(--light-blue)] hover:underline">Copiar a todos</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Vista previa: cómo lo verá el cliente --}}
    <div class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3 text-sm text-gray-700">
        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Así lo verá el cliente</span>
        <template x-for="linea in resumenLineas" :key="linea">
            <p x-text="linea"></p>
        </template>
        <p class="text-gray-500" x-show="resumenLineas.length === 0" style="display: none;">Todavía no cargaste horarios.</p>
    </div>
</div>
