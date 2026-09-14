<x-layout>
    <div class="m-5">
        <div class="flex justify-between">
            <h1 class="text-3xl font-bold text-blue-900">Mis tareas</h1>
            <a href="{{ route('tareas.create') }}" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Crear Tarea</a>
        </div>

        <ul class="mt-4 flex flex-col">
            @foreach ($tareas as $tarea)
                <a href="{{ route('tareas.show', $tarea->id) }}">{{ $tarea->descripcion }}</a>
            @endforeach
        </ul>
    </div>
</x-layout>
