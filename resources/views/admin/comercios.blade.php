<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel de administración · Comercios</h2>
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
                    @include('admin.partials.menu', ['activa' => 'comercios'])

                    <form action="{{ route('admin.comercios') }}" method="GET" role="search" class="mt-6 flex flex-col sm:flex-row gap-3">
                        <input type="search" name="q" value="{{ $buscado }}" autocomplete="off"
                               aria-label="Buscar comercios"
                               placeholder="Buscar por nombre, rubro o dirección..."
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
                        <button type="submit" class="inline-flex justify-center items-center px-6 py-2 bg-gray-800 text-white font-bold rounded-lg shadow transition hover:bg-gray-700">Buscar</button>
                    </form>

                    <p class="mt-4 text-sm text-gray-600">
                        {{ $comercios->total() }} {{ $comercios->total() === 1 ? 'comercio' : 'comercios' }}
                        @if ($buscado !== '') para "{{ $buscado }}" — <a href="{{ route('admin.comercios') }}" class="font-semibold text-[var(--primary-green)] hover:underline">ver todos</a>@endif
                    </p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-gray-200 text-gray-500">
                                <tr>
                                    <th class="py-3 pr-4 font-semibold">Comercio</th>
                                    <th class="py-3 pr-4 font-semibold">Rubro</th>
                                    <th class="py-3 pr-4 font-semibold">Localidad</th>
                                    <th class="py-3 pr-4 font-semibold">Dueño</th>
                                    <th class="py-3 pr-4 font-semibold">Creado</th>
                                    <th class="py-3 font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($comercios as $comercio)
                                    <tr>
                                        <td class="py-3 pr-4 font-semibold text-gray-800">{{ $comercio->nombre }}
                                            @unless ($comercio->habilitado)
                                                <span class="ml-1 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Deshabilitado</span>
                                            @endunless
                                        </td>
                                        <td class="py-3 pr-4 text-gray-600">{{ $comercio->rubrosResumen() }}</td>
                                        <td class="py-3 pr-4 text-gray-600">{{ $comercio->localidad?->nombre ?? '—' }}</td>
                                        <td class="py-3 pr-4 text-gray-600">
                                            {{ $comercio->user?->name ?? 'Sin dueño' }}
                                            <span class="block text-xs text-gray-400">{{ $comercio->user?->email }}</span>
                                        </td>
                                        <td class="py-3 pr-4 text-gray-600 whitespace-nowrap">{{ $comercio->created_at?->format('d/m/Y') }}</td>
                                        <td class="py-3">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <a href="{{ route('comercio.show', ['comercio' => $comercio->id]) }}" class="font-semibold text-gray-700 hover:underline">Ver</a>
                                                <a href="{{ route('comercio.edit', $comercio) }}" class="font-semibold text-[var(--primary-green)] hover:underline">Editar</a>
                                                <form method="POST" action="{{ route('comercio.destroy', $comercio) }}"
                                                      onsubmit="return confirm('¿Seguro que querés eliminar este comercio? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-semibold text-red-600 hover:underline">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-gray-500">No se encontraron comercios.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $comercios->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
