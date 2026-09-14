<x-layout>
    <h1 class="text-3xl font-bold text-blue-900">Nueva tarea</h1>

    <form method="POST" action="{{ route('tareas.store') }}" class="w-96 mt-5">
        @csrf

        <div class="flex flex-col">
            <label for="descripcion">Descripcion:</label>
            <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}">
            @error('descripcion')
                <p class="text-red-800 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col mt-3">
            <label for="prioridad">Prioridad:</label>
            <input type="text" name="prioridad" id="prioridad" value="{{ old('prioridad') }}">
            @error('prioridad')
                <p class="text-red-800 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col mt-3">
            <label for="fecha_inicio">Fecha inicio:</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}">
            @error('fecha_incio')
                <p class="text-red-800 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col mt-3">
            <label for="fecha_fin">Fecha fin:</label>
            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}">
            @error('fecha_fin')
                <p class="text-red-800 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col mt-3">
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Guardar</button>
        </div>
    </form>
</x-layout>
