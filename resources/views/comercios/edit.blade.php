<x-comerciante-layout>
<x-slot name="header">
<div class="flex flex-wrap items-center gap-3">
    <!-- Si hay cambios sin guardar, en vez de salir directo se abre el aviso (ver abajo) -->
    <a href="{{ $panelUrl }}" x-data @click.prevent="$dispatch('intentar-volver')"
       class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-gray-300 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">
        <span aria-hidden="true">←</span> {{ $panelTexto }}
    </a>
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    {{ __('Editar mi Comercio') }}
    </h2>
</div>
</x-slot>

{{-- Aviso de cambios sin guardar:
     - Se compara el formulario (incluidos mapa, horarios, pagos y logo) con cómo estaba al abrir la página.
       (De los archivos solo se mira nombre y tamaño: un campo de archivo vacío trae la fecha de "ahora"
       y, si se comparara, siempre daría "hay cambios".)
     - Si hay cambios y el comerciante intenta salir, se le pregunta si guarda o descarta.
     - También avisa el navegador si cierra la pestaña o toca "atrás" con cambios sin guardar. --}}
<div class="max-w-4xl mx-auto"
     x-data="{
         url: @js($panelUrl),
         base: null,
         tocado: @js($errors->any()),
         aviso: false,
         saliendo: false,
         foto() {
             const filas = [];
             for (const [clave, valor] of new FormData(this.$refs.form).entries()) {
                 if (clave === '_token' || clave === '_method') continue;
                 filas.push(clave + '=' + (valor instanceof File ? 'archivo:' + valor.name + ':' + valor.size : valor));
             }
             return filas.join('\n');
         },
         sucio() { return this.tocado || (this.base !== null && this.foto() !== this.base); },
         intentar() { this.sucio() ? this.aviso = true : this.irAlPanel(); },
         irAlPanel() { this.saliendo = true; window.location.href = this.url; },
         guardar() { this.aviso = false; this.$refs.form.requestSubmit(); }
     }"
     x-init="setTimeout(() => base = foto(), 800)"
     @intentar-volver.window="intentar()"
     @keydown.escape.window="aviso = false"
     @submit.window="if (! $event.defaultPrevented) saliendo = true"
     @beforeunload.window="if (sucio() && ! saliendo) { $event.preventDefault(); $event.returnValue = ''; }">

    <div x-show="aviso" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
         @click.self="aviso = false" data-aviso-cambios>
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" role="alertdialog" aria-modal="true" aria-labelledby="titulo-cambios">
            <h4 id="titulo-cambios" class="text-lg font-bold text-gray-800">Tenés cambios sin guardar</h4>
            <p class="mt-2 text-gray-600">¿Querés aplicar los cambios antes de volver al panel?</p>
            <div class="mt-6 flex flex-col gap-2 sm:flex-row-reverse">
                <button type="button" @click="guardar()" class="rounded-lg bg-[var(--primary-green)] px-5 py-2 font-bold text-white hover:bg-green-600">Guardar y volver</button>
                <button type="button" @click="irAlPanel()" class="rounded-lg border border-red-300 px-5 py-2 font-semibold text-red-700 hover:bg-red-50">Salir sin guardar</button>
                <button type="button" @click="aviso = false" class="rounded-lg px-5 py-2 font-semibold text-gray-600 hover:bg-gray-100">Seguir editando</button>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

        <div class="p-6 bg-gradient-to-r from-[var(--light-blue)] to-[var(--primary-green)] border-b border-gray-200">
            <h3 class="text-2xl font-bold text-white">Modificar mi Comercio</h3>
            <p class="mt-1 text-white/90">
                Actualiza la información de tu negocio para que tus clientes la vean.
            </p>
        </div>

        <!-- ================================== -->
        <!-- === FORMULARIO 1: ACTUALIZAR (PATCH) === -->
        <!-- ================================== -->
        <form method="POST" action="{{ route('comercio.update', $comercio) }}" enctype="multipart/form-data" x-ref="form">
            @csrf
            @method('PATCH')

            <div class="p-6 md:p-8">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <p class="font-bold">¡Ups! Algo salió mal.</p>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h4 class="text-lg font-semibold text-gray-800">Información Básica</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">

                    <!-- Nombre del Comercio -->
                    <div class="input-wrapper">
                        <label for="nombre" class="input-label">{{ __('Nombre del Comercio *') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5m-3 0V21m0-11.422L2.25 10.5 12 5.25l9.75 5.25-9.75 5.25Zm0 0V21m0-4.5H9.75M12 9V3M12 9l3-1.5M12 9l-3-1.5M12 9l3 1.5M12 9l-3 1.5M12 9V3M12 9l3 1.5" /></svg>
                            <x-text-input id="nombre" class="w-full input-field" type="text" name="nombre" :value="old('nombre', $comercio->nombre)" required />
                        </div>
                    </div>

                    <!-- Rubro -->
                    <div class="input-wrapper">
                        <label for="rubro" class="input-label">{{ __('Rubro / Categoría *') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                            <select id="rubro" name="rubro" class="input-field" required>
                                <option value="" disabled>Selecciona un rubro...</option>

                                <optgroup label="Gastronomía">
                                    <option value="Restaurante" {{ old('rubro', $comercio->rubro) == 'Restaurante' ? 'selected' : '' }}>Restaurante</option>
                                    <option value="Cafe" {{ old('rubro', $comercio->rubro) == 'Cafe' ? 'selected' : '' }}>Cafetería / Bar</option>
                                    <option value="Panaderia" {{ old('rubro', $comercio->rubro) == 'Panaderia' ? 'selected' : '' }}>Panadería / Pastelería</option>
                                    <option value="Supermercado" {{ old('rubro', $comercio->rubro) == 'Supermercado' ? 'selected' : '' }}>Supermercado / Almacén</option>
                                    <option value="Verduleria" {{ old('rubro', $comercio->rubro) == 'Verduleria' ? 'selected' : '' }}>Verdulería / Frutería</option>
                                    <option value="Carniceria" {{ old('rubro', $comercio->rubro) == 'Carniceria' ? 'selected' : '' }}>Carnicería / Pescadería</option>
                                    <option value="Delivery" {{ old('rubro', $comercio->rubro) == 'Delivery' ? 'selected' : '' }}>Solo Delivery</option>
                                </optgroup>

                                <optgroup label="Tiendas y Compras">
                                    <option value="Indumentaria" {{ old('rubro', $comercio->rubro) == 'Indumentaria' ? 'selected' : '' }}>Indumentaria y Accesorios</option>
                                    <option value="Calzado" {{ old('rubro', $comercio->rubro) == 'Calzado' ? 'selected' : '' }}>Zapatería</option>
                                    <option value="Tecnologia" {{ old('rubro', $comercio->rubro) == 'Tecnologia' ? 'selected' : '' }}>Tecnología / Computación</option>
                                    <option value="Hogar" {{ old('rubro', $comercio->rubro) == 'Hogar' ? 'selected' : '' }}>Hogar / Decoración / Muebles</option>
                                    <option value="Libreria" {{ old('rubro', $comercio->rubro) == 'Libreria' ? 'selected' : '' }}>Librería / Artística</option>
                                    <option value="Jugueteria" {{ old('rubro', $comercio->rubro) == 'Jugueteria' ? 'selected' : '' }}>Juguetería</option>
                                    <option value="Ferreteria" {{ old('rubro', $comercio->rubro) == 'Ferreteria' ? 'selected' : '' }}>Ferretería</option>
                                    <option value="Kiosco" {{ old('rubro', $comercio->rubro) == 'Kiosco' ? 'selected' : '' }}>Kiosco / Drugstore</option>
                                </optgroup>

                                <optgroup label="Salud y Bienestar">
                                    <option value="Farmacia" {{ old('rubro', $comercio->rubro) == 'Farmacia' ? 'selected' : '' }}>Farmacia</option>
                                    <option value="Optica" {{ old('rubro', $comercio->rubro) == 'Optica' ? 'selected' : '' }}>Óptica</option>
                                    <option value="Gimnasio" {{ old('rubro', $comercio->rubro) == 'Gimnasio' ? 'selected' : '' }}>Gimnasio / Fitness</option>
                                    <option value="Peluqueria" {{ old('rubro', $comercio->rubro) == 'Peluqueria' ? 'selected' : '' }}>Peluquería / Barbería</option>
                                    <option value="Estetica" {{ old('rubro', $comercio->rubro) == 'Estetica' ? 'selected' : '' }}>Belleza / Estética</option>
                                </optgroup>

                                <optgroup label="Servicios y Profesionales">
                                    <option value="Mecanico" {{ old('rubro', $comercio->rubro) == 'Mecanico' ? 'selected' : '' }}>Taller Mecánico / Repuestos</option>
                                    <option value="Mascotas" {{ old('rubro', $comercio->rubro) == 'Mascotas' ? 'selected' : '' }}>Veterinaria / Pet Shop</option>
                                    <option value="Lavanderia" {{ old('rubro', $comercio->rubro) == 'Lavanderia' ? 'selected' : '' }}>Lavandería / Tintorería</option>
                                    <option value="ReparacionesHogar" {{ old('rubro', $comercio->rubro) == 'ReparacionesHogar' ? 'selected' : '' }}>Reparaciones del Hogar (Plomería, etc.)</option>
                                    <option value="ServiciosProfesionales" {{ old('rubro', $comercio->rubro) == 'ServiciosProfesionales' ? 'selected' : '' }}>Servicios Profesionales (Abogado, Contador)</option>
                                </optgroup>

                                <optgroup label="Ocio y Otros">
                                    <option value="Hoteleria" {{ old('rubro', $comercio->rubro) == 'Hoteleria' ? 'selected' : '' }}>Hotelería / Turismo</option>
                                    <option value="Entretenimiento" {{ old('rubro', $comercio->rubro) == 'Entretenimiento' ? 'selected' : '' }}>Entretenimiento</option>
                                    <option value="Otro" {{ old('rubro', $comercio->rubro) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ============================================= -->
                <!-- ==== INICIO DE CAMPOS QUE FALTABAN ==== -->
                <!-- ============================================= -->

                @include('comercios.partials.selector-logo')

                @include('comercios.partials.campo-direccion', ['valor' => old('direccion', $comercio->direccion)])

                @include('comercios.partials.selector-localidad')

                @include('comercios.partials.selector-ubicacion')

                <div class="input-wrapper">
                    <label for="descripcion" class="input-label">{{ __('Descripción (qué hace tu comercio)') }}</label>
                    <div class="input-field-container">
                        <textarea id="descripcion" class="w-full input-field" name="descripcion" rows="3" placeholder="Ej: Ofrecemos la mejor comida casera...">{{ old('descripcion', $comercio->descripcion) }}</textarea>
                    </div>
                </div>

                <h4 class="text-lg font-semibold text-gray-800 mt-8">Horarios y Contacto</h4>
                @include('comercios.partials.selector-horarios')

                @include('comercios.partials.selector-dias-no-laborales')


                <div class="input-wrapper">
                    <label for="telefono" class="input-label">{{ __('Teléfono fijo / sin WhatsApp') }}</label>
                    <div class="input-field-container">
                        <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.63C11.24 16.088 9.917 14.76 8.163 12.998l1.293-.97c.362-.271.527-.734.417-1.173L8.756 6.463c-.125-.501-.575-.852-1.091-.852H6.375A2.25 2.25 0 0 0 4.125 7.875v.375Z" /></svg>
                        <x-text-input id="telefono" class="w-full input-field" type="text" name="telefono" :value="old('telefono', $comercio->telefono)" placeholder="Ej: 11-5555-1234" />
                    </div>
                </div>


                <h4 class="text-lg font-semibold text-gray-800 mt-8">Servicios y Pagos</h4>

                @include('comercios.partials.selector-pagos')

                <div class="input-wrapper">
                    <label for="servicios_adicionales" class="input-label">{{ __('Otros servicios que ofrezca') }}</label>
                    <div class="input-field-container">
                        <textarea id="servicios_adicionales" class="w-full input-field" name="servicios_adicionales" rows="2" placeholder="Ej: Delivery, Asesoramiento, Estacionamiento Bicis">{{ old('servicios_adicionales', $comercio->servicios_adicionales) }}</textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 mt-2">
                    @foreach (\App\Models\Comercio::ACCESIBILIDAD as $campo => $item)
                        <div class="checkbox-wrapper">
                            <input id="{{ $campo }}" name="{{ $campo }}" type="checkbox" class="checkbox-input" value="1"
                                {{ old($campo, $comercio->$campo) ? 'checked' : '' }}>
                            <label for="{{ $campo }}" class="checkbox-label">{{ $item['pregunta'] }}</label>
                        </div>
                    @endforeach
                </div>

                <h4 class="text-lg font-semibold text-gray-800 mt-8">Presencia Online</h4>

                <div class="input-wrapper">
                    <label for="sitio_web" class="input-label">{{ __('Sitio Web') }}</label>
                    <div class="input-field-container">
                        <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c-4.832 0-8.716-3.914-8.716-8.747M12 21c4.832 0 8.716-3.914 8.716-8.747m-8.716 8.747v-7.5M12 12.253v-7.5M12 12.253a2.25 2.25 0 0 0-2.25 2.25M12 12.253a2.25 2.25 0 0 1 2.25 2.25M12 12.253a2.25 2.25 0 0 1-2.25-2.25M12 12.253a2.25 2.25 0 0 0 2.25-2.25M3.284 5.253a9.004 9.004 0 0 1 17.432 0M3.284 18.747a9.004 9.004 0 0 0 17.432 0" /></svg>
                        <x-text-input id="sitio_web" class="w-full input-field" type="url" name="sitio_web" :value="old('sitio_web', $comercio->sitio_web)" placeholder="https://www.tucomercio.com" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6">
                    <div class="input-wrapper">
                        <label for="red_instagram" class="input-label">{{ __('Instagram') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" /></svg>
                            <x-text-input id="red_instagram" class="w-full input-field" type="text" name="red_instagram" :value="old('red_instagram', $comercio->red_instagram)" placeholder="@tucomercio" />
                        </div>
                    </div>
                    <div class="input-wrapper">
                        <label for="red_facebook" class="input-label">{{ __('Facebook') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-2.25 9h-1.5v3.75h1.5V15h-1.5v2.25h1.5v2.25h3.75v-2.25h1.5v-2.25h-1.5v-3.75h1.5v-2.25h-1.5V6.75h-1.5v2.25h-1.5v2.25Z" /></svg>
                            <x-text-input id="red_facebook" class="w-full input-field" type="text" name="red_facebook" :value="old('red_facebook', $comercio->red_facebook)" placeholder="/tucomercio" />
                        </div>
                    </div>
                    <div class="input-wrapper">
                        <label for="red_whatsapp" class="input-label">{{ __('WhatsApp') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.63C11.24 16.088 9.917 14.76 8.163 12.998l1.293-.97c.362-.271.527-.734.417-1.173L8.756 6.463c-.125-.501-.575-.852-1.091-.852H6.375A2.25 2.25 0 0 0 4.125 7.875v.375Z" /></svg>
                            <x-text-input id="red_whatsapp" class="w-full input-field" type="text" name="red_whatsapp" :value="old('red_whatsapp', $comercio->red_whatsapp)" placeholder="11 5555 1234" />
                        </div>
                    </div>
                </div>

                <!-- ============================================= -->
                <!-- ==== FIN DE CAMPOS QUE FALTABAN ==== -->
                <!-- ============================================= -->

                <!-- Botón de Guardar Cambios -->
                <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" class="ms-4 px-8 py-3 bg-[var(--primary-green)] text-white font-bold rounded-lg transition duration-300 ease-in-out hover:bg-green-600 focus:bg-green-700 active:bg-green-800">
                        {{ __('Guardar Cambios') }}
                    </button>
                </div>

            </div>
        </form>
        <!-- === FIN DEL FORMULARIO 1 === -->


        <!-- === VISIBILIDAD DEL COMERCIO: habilitar / deshabilitar (PATCH) === -->
        <div class="p-6 md:p-8 border-t border-gray-200 rounded-b-lg {{ $comercio->habilitado ? 'bg-amber-50' : 'bg-green-50' }}">
            @if ($comercio->habilitado)
                <h4 class="text-lg font-semibold text-amber-900">Visibilidad del comercio</h4>
                <p class="mt-1 text-sm text-amber-800">
                    Si lo deshabilitás, tu comercio deja de aparecer en el buscador y en el perfil público. No se borra nada y podés volver a habilitarlo cuando quieras.
                </p>
            @else
                <h4 class="text-lg font-semibold text-green-900">Comercio deshabilitado</h4>
                <p class="mt-1 text-sm text-green-800">
                    Hoy tu comercio no se muestra al público. Habilitalo para que vuelva a aparecer en el buscador.
                </p>
            @endif

            <form method="POST" action="{{ route('comercio.estado', $comercio) }}"
                  @if ($comercio->habilitado) onsubmit="return confirm(@js('¿Deshabilitar «' . $comercio->nombre . '»? Dejará de mostrarse al público hasta que lo vuelvas a habilitar. Los cambios sin guardar de este formulario no se guardan.'));" @endif>
                @csrf
                @method('PATCH')

                <div class="mt-4">
                    @if ($comercio->habilitado)
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-amber-600 text-white font-bold rounded-lg shadow transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Deshabilitar comercio
                        </button>
                    @else
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-[var(--primary-green)] text-white font-bold rounded-lg shadow transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Habilitar comercio
                        </button>
                    @endif
                </div>
            </form>
        </div>
        <!-- === FIN DE VISIBILIDAD === -->

    </div>
</div>


</x-comerciante-layout>
