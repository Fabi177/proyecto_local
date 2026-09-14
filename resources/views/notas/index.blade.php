<x-layout>
    <div class="m-5">
        <div class="flex justify-between">
            <h1 class="text-3xl font-bold text-blue-900">Listado de notas</h1>
            <a href="{{ route('notas.create') }}" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Crear Nota</a>
        </div>

        <ul class="mt-4">
            @foreach ($notas as $nota)
                <x-link-listado>{{ $nota->texto }}</x-link-listado>
            @endforeach
        </ul>
    </div>
</x-layout>
