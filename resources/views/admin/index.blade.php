<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel de administración</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 md:p-8">
                    @include('admin.partials.menu', ['activa' => 'index'])

                    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="rounded-lg bg-green-50 p-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $totales['comercios'] }}</div>
                            <div class="text-sm text-gray-600">Comercios</div>
                        </div>
                        <div class="rounded-lg bg-blue-50 p-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $totales['comerciantes'] }}</div>
                            <div class="text-sm text-gray-600">Comerciantes</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $totales['clientes'] }}</div>
                            <div class="text-sm text-gray-600">Clientes</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $totales['administradores'] }}</div>
                            <div class="text-sm text-gray-600">Administradores</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 md:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-xl font-bold text-gray-800">Últimos comercios registrados</h3>
                        <a href="{{ route('admin.comercios') }}" class="text-sm font-semibold text-[var(--primary-green)] hover:underline">Ver todos</a>
                    </div>

                    @forelse ($ultimosComercios as $comercio)
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-4">
                            <div class="min-w-0">
                                <a href="{{ route('comercio.show', ['comercio' => $comercio->id]) }}" class="font-semibold text-gray-800 hover:underline break-words">{{ $comercio->nombre }}</a>
                                <div class="text-sm text-gray-500 break-words">{{ $comercio->rubrosResumen() }} · {{ $comercio->user?->name ?? 'Sin dueño' }}</div>
                            </div>
                            <div class="text-sm text-gray-500">{{ $comercio->created_at?->format('d/m/Y') }}</div>
                        </div>
                    @empty
                        <p class="mt-4 text-gray-600">Todavía no hay comercios registrados.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
