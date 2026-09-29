{{--
    Selector de logo (imagen) del comercio.

    Se incluye en comercios/create y comercios/edit, DENTRO del <form>.
    IMPORTANTE: ese <form> debe tener enctype="multipart/form-data", si no el archivo no llega al servidor.

    Envía el archivo en el campo "logo" y, si se pidió borrar el actual, "quitar_logo=1".
    La lógica está en resources/js/logo-comercio.js (componente Alpine "selectorLogo").
    Todos los <button> llevan type="button" para que no envíen el formulario al presionarlos.
--}}
<div class="input-wrapper"
     x-data="selectorLogo({ url: @js(isset($comercio) ? $comercio->logo_url : null) })">

    <span class="input-label">{{ __('Logo / Imagen del comercio') }}</span>

    {{-- Campos que viajan en el formulario --}}
    <input type="file" id="logo-input-final" name="logo" accept="image/png,image/jpeg,image/webp" style="display: none;" tabindex="-1">
    <input type="hidden" name="quitar_logo" :value="quitarActual ? 1 : 0">

    <div class="mt-2 flex flex-wrap items-center gap-4">
        {{-- Vista previa actual --}}
        <div class="w-28 h-28 shrink-0 rounded-lg border border-gray-300 bg-gray-50 flex items-center justify-center overflow-hidden">
            <img x-show="hayLogo" :src="vista" alt="Logo del comercio" class="w-full h-full object-contain" style="display: none;">
            <svg x-show="!hayLogo" class="w-10 h-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button"
                    x-on:click="abrir()"
                    class="inline-flex items-center px-4 py-2 bg-[var(--light-blue)] text-white text-sm font-semibold rounded-lg shadow transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--light-blue)]">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                <span x-text="hayLogo ? 'Cambiar logo' : 'Subir logo'">Subir logo</span>
            </button>

            <button type="button"
                    x-show="hayLogo"
                    x-on:click="quitar()"
                    style="display: none;"
                    class="text-sm font-medium text-red-600 hover:text-red-800 hover:underline">
                Quitar logo
            </button>
        </div>
    </div>

    <p class="mt-2 text-sm"
       :class="(confirmadoUrl || actualUrl) && !quitarActual ? 'text-green-700' : 'text-gray-500'"
       x-text="resumen">
        Todavía no cargaste el logo de tu comercio.
    </p>

    {{-- ============ VENTANA EMERGENTE PARA ELEGIR LA IMAGEN ============ --}}
    <x-modal name="selector-logo" maxWidth="lg">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-800">Logo de tu comercio</h3>
            <p class="mt-1 text-sm text-gray-600">
                Elegí una imagen JPG, PNG o WEBP de hasta 2 MB. Se recomienda una imagen cuadrada.
            </p>

            {{-- Input de la ventana: NO tiene "name", así que no se envía con el formulario --}}
            <input type="file" id="logo-input-tmp" accept="image/png,image/jpeg,image/webp"
                   x-on:change="alElegir($event)" style="display: none;" tabindex="-1">

            {{-- Zona para hacer clic o arrastrar la imagen --}}
            <div class="mt-4 flex flex-col items-center justify-center rounded-lg border-2 border-dashed p-4 text-center transition"
                 :class="arrastrando ? 'border-[var(--primary-green)] bg-green-50' : 'border-gray-300 bg-gray-50'"
                 x-on:dragover.prevent="arrastrando = true"
                 x-on:dragleave.prevent="arrastrando = false"
                 x-on:drop.prevent="alSoltar($event)">

                <template x-if="tmpUrl">
                    <img :src="tmpUrl" alt="Vista previa del logo" class="h-48 w-full object-contain">
                </template>

                <template x-if="!tmpUrl">
                    <div class="py-8 text-sm text-gray-500">
                        Arrastrá una imagen hasta acá<br>o usá el botón de abajo.
                    </div>
                </template>

                <button type="button"
                        x-on:click="elegirArchivo()"
                        class="mt-3 px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-md transition hover:bg-gray-700">
                    <span x-text="tmpUrl ? 'Elegir otra imagen' : 'Elegir imagen'">Elegir imagen</span>
                </button>
            </div>

            <p class="mt-2 text-sm text-red-600" x-show="mensaje" x-text="mensaje" style="display: none;"></p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        x-on:click="cancelar()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg transition hover:bg-gray-200">
                    Cancelar
                </button>
                <button type="button"
                        x-on:click="confirmar()"
                        :disabled="!tmpArchivo"
                        class="px-4 py-2 bg-[var(--primary-green)] text-white text-sm font-semibold rounded-lg transition hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Confirmar logo
                </button>
            </div>
        </div>
    </x-modal>
</div>
