@if (Auth::user()->comercio)

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="md:col-span-2">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 md:p-8">
                    <h3 class="text-2xl font-bold text-gray-800">Panel de {{ Auth::user()->comercio->nombre }}</h3>
                    <p class="mt-2 text-gray-600">
                        Gestiona la información de tu comercio, mira tus estadísticas y responde a tus clientes.
                    </p>

                    <div class="mt-6 flex flex-col sm:flex-row gap-4">

                        <a href="{{ route('comercio.edit') }}" class="inline-flex items-center justify-center px-6 py-3 bg-[var(--primary-green)] text-white font-bold rounded-lg shadow transition hover:bg-green-600">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            Editar mi Comercio
                        </a>

                        <a href="{{ route('comercio.show', ['comercio' => Auth::user()->comercio->id]) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-lg shadow-sm transition hover:bg-gray-200">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            Ver Perfil Público
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-1">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800">Estadísticas (30 días)</h4>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                            <span class="p-3 bg-blue-100 text-blue-600 rounded-full">
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /></svg>
                            </span>
                            <div class="ml-4">
                                <div class="text-3xl font-bold text-gray-900">1,204</div>
                                <div class="text-sm text-gray-500">Vistas al Perfil</div>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-green-50 rounded-lg">
                            <span class="p-3 bg-green-100 text-green-600 rounded-full">
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.63C11.24 16.088 9.917 14.76 8.163 12.998l1.293-.97c.362-.271.527-.734.417-1.173L8.756 6.463c-.125-.501-.575-.852-1.091-.852H6.375A2.25 2.25 0 0 0 4.125 7.875v.375Z" /></svg>
                            </span>
                            <div class="ml-4">
                                <div class="text-3xl font-bold text-gray-900">58</div>
                                <div class="text-sm text-gray-500">Clicks al Teléfono</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 md:p-10 flex flex-col items-center text-center">

            <span class="p-4 bg-gray-100 rounded-full">
                <svg class="w-12 h-12 text-[var(--comerciante-bg)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5m-3 0V21m0-11.422L2.25 10.5 12 5.25l9.75 5.25-9.75 5.25Zm0 0V21m0-4.5H9.75M12 9V3M12 9l3-1.5M12 9l-3-1.5M12 9l3 1.5M12 9l-3 1.5M12 9V3M12 9l3 1.5" />
                </svg>
            </span>

            <h3 class="text-3xl font-bold mt-4 text-gray-800">¡Bienvenido, Comerciante!</h3>
            <p class="mt-2 text-gray-600 max-w-lg">
                Tu cuenta de comerciante está lista. El último paso es registrar la información de tu negocio para que miles de clientes puedan encontrarte.
            </p>
            <div class="mt-8">
                <a href="{{ route('comercio.create') }}"
                   class="inline-flex items-center px-8 py-4 bg-[var(--primary-green)] text-white text-lg font-bold rounded-lg shadow-lg transition duration-300 ease-in-out hover:bg-green-600">
                    Registrar mi Comercio Ahora
                </a>
            </div>
        </div>
    </div>

@endif
