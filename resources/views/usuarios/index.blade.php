<x-layout>
    <div class="m-5">
        @php
            $usuarios = ['Juan', 'Pedro', 'Daiana', 'Carolina'];
        @endphp

        <div class="flex justify-between">
            <h1 class="text-3xl font-bold text-blue-900">Listado de usuarios</h1>
            <a href="{{ route('usuarios.create') }}" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Crear Usuario</a>
        </div>

        <ul class="mt-4">
            @foreach ($usuarios as $usuario)
                <x-link-listado>{{ $usuario }}</x-link-listado>
            @endforeach
        </ul>
    </div>
</x-layout>
