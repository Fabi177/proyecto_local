<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel de administración · Usuarios</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 md:p-8">
                    @include('admin.partials.menu', ['activa' => 'usuarios'])

                    <form action="{{ route('admin.usuarios') }}" method="GET" role="search" class="mt-6 flex flex-col sm:flex-row gap-3">
                        <input type="search" name="q" value="{{ $buscado }}" autocomplete="off"
                               aria-label="Buscar usuarios"
                               placeholder="Buscar por nombre o correo..."
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--primary-green)] focus:ring-[var(--primary-green)]">
                        <button type="submit" class="inline-flex justify-center items-center px-6 py-2 bg-gray-800 text-white font-bold rounded-lg shadow transition hover:bg-gray-700">Buscar</button>
                    </form>

                    <p class="mt-4 text-sm text-gray-600">
                        {{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'usuario' : 'usuarios' }}
                        @if ($buscado !== '') para "{{ $buscado }}" — <a href="{{ route('admin.usuarios') }}" class="font-semibold text-[var(--primary-green)] hover:underline">ver todos</a>@endif
                    </p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-gray-200 text-gray-500">
                                <tr>
                                    <th class="py-3 pr-4 font-semibold">Nombre</th>
                                    <th class="py-3 pr-4 font-semibold">Correo</th>
                                    <th class="py-3 pr-4 font-semibold">Rol</th>
                                    <th class="py-3 pr-4 font-semibold">Comercios</th>
                                    <th class="py-3 font-semibold">Registro</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($usuarios as $usuario)
                                    <tr>
                                        <td class="py-3 pr-4 font-semibold text-gray-800">{{ $usuario->name }}</td>
                                        <td class="py-3 pr-4 text-gray-600 break-all">{{ $usuario->email }}</td>
                                        <td class="py-3 pr-4 text-gray-600">
                                            {{ ['admin' => 'Administrador', 'comerciante' => 'Comerciante', 'usuario' => 'Cliente'][$usuario->role] ?? $usuario->role }}
                                        </td>
                                        <td class="py-3 pr-4 text-gray-600">{{ $usuario->comercios_count }}</td>
                                        <td class="py-3 text-gray-600 whitespace-nowrap">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-gray-500">No se encontraron usuarios.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $usuarios->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
