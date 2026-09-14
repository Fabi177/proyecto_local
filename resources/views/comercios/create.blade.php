<x-comerciante-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registro de Comercio') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto"> <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

            <div class="p-6 bg-gradient-to-r from-[var(--light-blue)] to-[var(--primary-green)] border-b border-gray-200">
                <h3 class="text-2xl font-bold text-white">¡Bienvenido, Comerciante!</h3>
                <p class="mt-1 text-white/90">
                    Completa los siguientes datos para dar de alta tu negocio en la plataforma.
                </p>
            </div>

            <form method="POST" action="{{ route('comercio.store') }}">
                @csrf

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

                        <div class="input-wrapper">
                            <label for="nombre" class="input-label">{{ __('Nombre del Comercio *') }}</label>
                            <div class="input-field-container">
                                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5m-3 0V21m0-11.422L2.25 10.5 12 5.25l9.75 5.25-9.75 5.25Zm0 0V21m0-4.5H9.75M12 9V3M12 9l3-1.5M12 9l-3-1.5M12 9l3 1.5M12 9l-3 1.5M12 9V3M12 9l3 1.5" /></svg>
                                <x-text-input id="nombre" class="w-full input-field" type="text" name="nombre" :value="old('nombre')" required />
                            </div>
                        </div>

                        <div class="input-wrapper">
                            <label for="rubro" class="input-label">{{ __('Rubro / Categoría *') }}</label>
                            <div class="input-field-container">
                                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>

                                <select id="rubro" name="rubro" class="input-field" required>
                                    <option value="" disabled {{ old('rubro') ? '' : 'selected' }}>Selecciona un rubro...</option>

                                    <optgroup label="Gastronomía">
                                        <option value="Restaurante" {{ old('rubro') == 'Restaurante' ? 'selected' : '' }}>Restaurante</option>
                                        <option value="Cafe" {{ old('rubro') == 'Cafe' ? 'selected' : '' }}>Cafetería / Bar</option>
                                        <option value="Panaderia" {{ old('rubro') == 'Panaderia' ? 'selected' : '' }}>Panadería / Pastelería</option>
                                        <option value="Supermercado" {{ old('rubro') == 'Supermercado' ? 'selected' : '' }}>Supermercado / Almacén</option>
                                        <option value="Verduleria" {{ old('rubro') == 'Verduleria' ? 'selected' : '' }}>Verdulería / Frutería</option>
                                        <option value="Carniceria" {{ old('rubro') == 'Carniceria' ? 'selected' : '' }}>Carnicería / Pescadería</option>
                                        <option value="Delivery" {{ old('rubro') == 'Delivery' ? 'selected' : '' }}>Solo Delivery</option>
                                    </optgroup>

                                    <optgroup label="Tiendas y Compras">
                                        <option value="Indumentaria" {{ old('rubro') == 'Indumentaria' ? 'selected' : '' }}>Indumentaria y Accesorios</option>
                                        <option value="Calzado" {{ old('rubro') == 'Calzado' ? 'selected' : '' }}>Zapatería</option>
                                        <option value="Tecnologia" {{ old('rubro') == 'Tecnologia' ? 'selected' : '' }}>Tecnología / Computación</option>
                                        <option value="Hogar" {{ old('rubro') == 'Hogar' ? 'selected' : '' }}>Hogar / Decoración / Muebles</option>
                                        <option value="Libreria" {{ old('rubro') == 'Libreria' ? 'selected' : '' }}>Librería / Artística</option>
                                        <option value="Jugueteria" {{ old('rubro') == 'Jugueteria' ? 'selected' : '' }}>Juguetería</option>
                                        <option value="Ferreteria" {{ old('rubro') == 'Ferreteria' ? 'selected' : '' }}>Ferretería</option>
                                        <option value="Kiosco" {{ old('rubro') == 'Kiosco' ? 'selected' : '' }}>Kiosco / Drugstore</option>
                                    </optgroup>

                                    <optgroup label="Salud y Bienestar">
                                        <option value="Farmacia" {{ old('rubro') == 'Farmacia' ? 'selected' : '' }}>Farmacia</option>
                                        <option value="Optica" {{ old('rubro') == 'Optica' ? 'selected' : '' }}>Óptica</option>
                                        <option value="Gimnasio" {{ old('rubro') == 'Gimnasio' ? 'selected' : '' }}>Gimnasio / Fitness</option>
                                        <option value="Peluqueria" {{ old('rubro') == 'Peluqueria' ? 'selected' : '' }}>Peluquería / Barbería</option>
                                        <option value="Estetica" {{ old('rubro') == 'Estetica' ? 'selected' : '' }}>Belleza / Estética</option>
                                    </optgroup>

                                    <optgroup label="Servicios y Profesionales">
                                        <option value="Mecanico" {{ old('rubro') == 'Mecanico' ? 'selected' : '' }}>Taller Mecánico / Repuestos</option>
                                        <option value="Mascotas" {{ old('rubro') == 'Mascotas' ? 'selected' : '' }}>Veterinaria / Pet Shop</option>
                                        <option value="Lavanderia" {{ old('rubro') == 'Lavanderia' ? 'selected' : '' }}>Lavandería / Tintorería</option>
                                        <option value="ReparacionesHogar" {{ old('rubro') == 'ReparacionesHogar' ? 'selected' : '' }}>Reparaciones del Hogar (Plomería, etc.)</option>
                                        <option value="ServiciosProfesionales" {{ old('rubro') == 'ServiciosProfesionales' ? 'selected' : '' }}>Servicios Profesionales (Abogado, Contador)</option>
                                    </optgroup>

                                    <optgroup label="Ocio y Otros">
                                        <option value="Hoteleria" {{ old('rubro') == 'Hoteleria' ? 'selected' : '' }}>Hotelería / Turismo</option>
                                        <option value="Entretenimiento" {{ old('rubro') == 'Entretenimiento' ? 'selected' : '' }}>Entretenimiento</option>
                                        <option value="Otro" {{ old('rubro') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <label for="direccion" class="input-label">{{ __('Dirección *') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <x-text-input id="direccion" class="w-full input-field" type="text" name="direccion" :value="old('direccion')" required />
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <label for="descripcion" class="input-label">{{ __('Descripción (qué hace tu comercio)') }}</label>
                        <div class="input-field-container">
                            <textarea id="descripcion" class="w-full input-field" name="descripcion" rows="3" placeholder="Ej: Ofrecemos la mejor comida casera...">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>

                    <h4 class="text-lg font-semibold text-gray-800 mt-8">Horarios y Contacto</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">

                        <div class="input-wrapper">
                            <label for="horarios_atencion" class="input-label">{{ __('Horarios de Atención') }}</label>
                            <div class="input-field-container">
                                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                <x-text-input id="horarios_atencion" class="w-full input-field" type="text" name="horarios_atencion" :value="old('horarios_atencion')" placeholder="Ej: Lunes a Viernes de 9 a 18 hs" />
                            </div>
                        </div>

                        <div class="input-wrapper">
                            <label for="dias_no_laborales" class="input-label">{{ __('Días no laborales') }}</label>
                            <div class="input-field-container">
                                <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                <x-text-input id="dias_no_laborales" class="w-full input-field" type="text" name="dias_no_laborales" :value="old('dias_no_laborales')" placeholder="Ej: Sábados y Domingos" />
                            </div>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <label for="telefono" class="input-label">{{ __('Teléfono') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.63C11.24 16.088 9.917 14.76 8.163 12.998l1.293-.97c.362-.271.527-.734.417-1.173L8.756 6.463c-.125-.501-.575-.852-1.091-.852H6.375A2.25 2.25 0 0 0 4.125 7.875v.375Z" /></svg>
                            <x-text-input id="telefono" class="w-full input-field" type="text" name="telefono" :value="old('telefono')" placeholder="Ej: 11-5555-1234" />
                        </div>
                    </div>


                    <h4 class="text-lg font-semibold text-gray-800 mt-8">Servicios y Pagos</h4>

                    <div class="input-wrapper">
                        <label for="formas_pago" class="input-label">{{ __('Formas de pago') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h6m3-3.75l-3 3m0 0l-3-3m3 3v-6m6 3h.008v.008H18v-.008Z" /></svg>
                            <x-text-input id="formas_pago" class="w-full input-field" type="text" name="formas_pago" :value="old('formas_pago')" placeholder="Ej: Efectivo, Mercado Pago, Tarjeta de Crédito" />
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <label for="servicios_adicionales" class="input-label">{{ __('Otros servicios que ofrezca') }}</label>
                        <div class="input-field-container">
                            <textarea id="servicios_adicionales" class="w-full input-field" name="servicios_adicionales" rows="2" placeholder="Ej: Delivery, Asesoramiento, Estacionamiento Bicis">{{ old('servicios_adicionales') }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 mt-2">
                        <div class="checkbox-wrapper">
                            <input id="ingreso_discapacitados" name="ingreso_discapacitados" type="checkbox" class="checkbox-input" value="1" {{ old('ingreso_discapacitados') ? 'checked' : '' }}>
                            <label for="ingreso_discapacitados" class="checkbox-label">¿Tiene ingreso para discapacitados?</label>
                        </div>
                        <div class="checkbox-wrapper">
                            <input id="estacionamiento" name="estacionamiento" type="checkbox" class="checkbox-input" value="1" {{ old('estacionamiento') ? 'checked' : '' }}>
                            <label for="estacionamiento" class="checkbox-label">¿Tiene estacionamiento para clientes?</label>
                        </div>
                    </div>

                    <h4 class="text-lg font-semibold text-gray-800 mt-8">Presencia Online</h4>

                    <div class="input-wrapper">
                        <label for="sitio_web" class="input-label">{{ __('Sitio Web') }}</label>
                        <div class="input-field-container">
                            <svg class="input-icon w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c-4.832 0-8.716-3.914-8.716-8.747M12 21c4.832 0 8.716-3.914 8.716-8.747m-8.716 8.747v-7.5M12 12.253v-7.5M12 12.253a2.25 2.25 0 0 0-2.25 2.25M12 12.253a2.25 2.25 0 0 1 2.25 2.25M12 12.253a2.25 2.25 0 0 1-2.25-2.25M12 12.253a2.25 2.25 0 0 0 2.25-2.25M3.284 5.253a9.004 9.004 0 0 1 17.432 0M3.284 18.747a9.004 9.004 0 0 0 17.432 0" /></svg>
                            <x-text-input id="sitio_web" class="w-full input-field" type="url" name="sitio_web" :value="old('sitio_web')" placeholder="https://www.tucomercio.com" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6">
                        <div class="input-wrapper">
                            <label for="red_instagram" class="input-label">{{ __('Instagram') }}</label>
                            <div class="input-field-container">
                                <svg class="input-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" /></svg>
                                <x-text-input id="red_instagram" class="w-full input-field" type="text" name="red_instagram" :value="old('red_instagram')" placeholder="@tucomercio" />
                            </div>
                        </div>
                        <div class="input-wrapper">
                            <label for="red_facebook" class="input-label">{{ __('Facebook') }}</label>
                            <div class="input-field-container">
                                <svg class="input-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-2.25 9h-1.5v3.75h1.5V15h-1.5v2.25h1.5v2.25h3.75v-2.25h1.5v-2.25h-1.5v-3.75h1.5v-2.25h-1.5V6.75h-1.5v2.25h-1.5v2.25Z" /></svg>
                                <x-text-input id="red_facebook" class="w-full input-field" type="text" name="red_facebook" :value="old('red_facebook')" placeholder="/tucomercio" />
                            </div>
                        </div>
                        <div class="input-wrapper">
                            <label for="red_whatsapp" class="input-label">{{ __('WhatsApp') }}</label>
                            <div class="input-field-container">
                                <svg class="input-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.63C11.24 16.088 9.917 14.76 8.163 12.998l1.293-.97c.362-.271.527-.734.417-1.173L8.756 6.463c-.125-.501-.575-.852-1.091-.852H6.375A2.25 2.25 0 0 0 4.125 7.875v.375Z" /></svg>
                                <x-text-input id="red_whatsapp" class="w-full input-field" type="text" name="red_whatsapp" :value="old('red_whatsapp')" placeholder="11 5555 1234" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 pt-6 border-t border-gray-200">
                        <button type="submit" class="ms-4 px-8 py-3 bg-[var(--primary-green)] text-white font-bold rounded-lg transition duration-300 ease-in-out hover:bg-green-600 focus:bg-green-700 active:bg-green-800">
                            {{ __('Registrar mi Comercio') }}
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</x-comerciante-layout>
