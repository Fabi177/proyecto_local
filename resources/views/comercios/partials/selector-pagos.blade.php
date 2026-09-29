{{--
    Selector de formas de pago.

    Se incluye en comercios/create y comercios/edit, DENTRO del <form>.
    La lógica está en resources/js/horarios-pagos.js (componente Alpine "formasPago").

    Envía UN solo campo oculto: "formas_pago", con las elegidas separadas por coma
    (ej: "Efectivo, MODO, Naranja X"). Es el mismo formato del campo de texto que había antes.
    Todos los <button> llevan type="button" y Enter en el campo "Agregar" no envía el formulario.
--}}
<div class="input-wrapper"
     x-data="formasPago({ guardadas: @js(old('formas_pago', isset($comercio) ? $comercio->formas_pago : null)) })">

    <span class="input-label">{{ __('Formas de pago') }}</span>
    <p class="mt-1 text-sm text-gray-500">Tocá las que aceptás. Si falta alguna, agregala abajo.</p>

    <input type="hidden" name="formas_pago" :value="texto">

    {{-- Formas de pago comunes --}}
    <div class="mt-3 flex flex-wrap gap-2">
        <template x-for="p in base" :key="p[0]">
            <button type="button" x-on:click="alternar(p[0])" :aria-pressed="estaElegido(p[0])"
                    class="rounded-full border px-4 py-1.5 text-sm transition"
                    :class="estaElegido(p[0])
                        ? 'border-[var(--primary-green)] bg-green-50 font-semibold text-green-800'
                        : 'border-gray-300 bg-gray-50 text-gray-700 hover:border-[var(--primary-green)]'">
                <span x-text="(estaElegido(p[0]) ? '✓ ' : '') + p[1] + ' ' + p[0]"></span>
            </button>
        </template>

        {{-- Formas de pago agregadas por el comerciante --}}
        <template x-for="nombre in personalizadas" :key="nombre">
            <span class="inline-flex items-center rounded-full border py-1 pl-4 pr-1 text-sm transition"
                  :class="estaElegido(nombre)
                      ? 'border-[var(--primary-green)] bg-green-50 font-semibold text-green-800'
                      : 'border-gray-300 bg-gray-50 text-gray-700'">
                <button type="button" x-on:click="alternar(nombre)" :aria-pressed="estaElegido(nombre)"
                        x-text="(estaElegido(nombre) ? '✓ ' : '') + nombre"></button>
                <button type="button" x-on:click="quitarPersonalizada(nombre)" :aria-label="'Quitar ' + nombre"
                        class="px-2 text-base leading-none hover:text-red-600">×</button>
            </span>
        </template>
    </div>

    {{-- Agregar otra --}}
    <div class="mt-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <label for="pago-nuevo" class="block text-xs text-gray-500 mb-1">¿Falta alguna? Agregala</label>
            <input id="pago-nuevo" type="text" x-model="nuevo" x-on:keydown.enter.prevent="agregar()"
                   maxlength="80" placeholder="Ej: Naranja X, Cheque, Bitcoin"
                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
        </div>
        <button type="button" x-on:click="agregar()"
                class="px-4 py-2 bg-[var(--primary-green)] text-white text-sm font-semibold rounded-lg transition hover:bg-green-600">
            Agregar
        </button>
    </div>

    <p class="mt-2 text-sm text-red-600" x-show="mensaje" x-text="mensaje" style="display: none;"></p>

    {{-- Vista previa: cómo lo verá el cliente --}}
    <div class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3 text-sm text-gray-700">
        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Así lo verá el cliente</span>
        <div class="flex flex-wrap gap-2">
            <template x-for="nombre in elegidas" :key="nombre">
                <span class="inline-flex rounded-full bg-sky-100 px-3 py-0.5 text-sm text-sky-800" x-text="nombre"></span>
            </template>
        </div>
        <p class="text-gray-500" x-show="elegidas.length === 0" style="display: none;">No elegiste ninguna forma de pago.</p>
    </div>
</div>
