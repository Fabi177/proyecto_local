<x-layout>
    <div class="m-5">
        <div class="flex justify-between">
            <h1 class="text-3xl font-bold text-blue-900">Tarea: {{ $tarea->descripcion }}</h1>
        </div>

        <ul class="mt-4">
            <li>Prioridad: {{ $tarea->prioridad }}</li>
            <li>Fecha de inicio: {{ $tarea->fecha_inicio }}</li>
            <li>Fecha de fin: {{ $tarea->fecha_fin }}</li>
        </ul>

        <div class="flex gap-x-10 mt-5">
            <a href="{{ route('tareas.edit', $tarea->id) }}" class="block bg-green-700 text-white rounded px-4 py-2 hover:bg-green-800">Editar tarea</a>
            <form method="POST" action="{{ route('tareas.destroy', $tarea->id) }}" id="form-eliminar-tarea">
                @csrf
                @method('DELETE')

                <button class="block bg-red-700 text-white rounded px-4 py-2 hover:bg-red-800" id="btn-eliminar-tarea">
                    Eliminar tarea
                </button>
            </form>
        </div>
    </div>

    <script>
        const btnEliminarTarea = document.querySelector("#btn-eliminar-tarea");

        btnEliminarTarea.addEventListener("click", function(e) {
            e.preventDefault();

            let confirmacion = confirm("¿Esta seguro que desea eliminar la tarea?");
            if(confirmacion) {
                const formEliminarTarea = document.querySelector("#form-eliminar-tarea");
                formEliminarTarea.submit();
            }
        });
    </script>
</x-layout>
