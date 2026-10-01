<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use App\Models\Resena;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Calificaciones y comentarios de los comercios.
 *
 * - Leer las reseñas es público (se muestran en el perfil del comercio).
 * - Escribir exige sesión iniciada Y cuenta de cliente (ver rutas: middleware 'auth').
 * - Cada cliente tiene una sola reseña por comercio; si vuelve a enviar, se edita.
 * - Solo el autor puede editar su reseña (estrellas y texto) desde el botón "Editar" de la lista.
 * - Pueden borrar una reseña su autor y el administrador.
 */
class ResenaController extends Controller
{
    public function store(Request $request, Comercio $comercio): RedirectResponse
    {
        abort_unless($request->user()->esCliente(), 403, 'Solo los clientes pueden calificar comercios.');

        $datos = $request->validate($this->reglas(), $this->mensajes());

        $comentario = trim((string) ($datos['comentario'] ?? ''));

        $resena = Resena::updateOrCreate(
            ['comercio_id' => $comercio->id, 'user_id' => $request->user()->id],
            ['calificacion' => $datos['calificacion'], 'comentario' => $comentario !== '' ? $comentario : null],
        );

        return redirect()
            ->route('comercio.show', $comercio)
            ->with('status_resena', $resena->wasRecentlyCreated ? '¡Gracias por su comentario!' : 'Actualizamos tu calificación.');
    }

    /**
     * Editar la reseña propia (estrellas y texto). El administrador NO edita reseñas ajenas:
     * solo puede eliminarlas.
     */
    public function update(Request $request, Resena $resena): RedirectResponse
    {
        abort_unless($resena->user_id === $request->user()->id, 403);

        // Los errores van a una "bolsa" aparte (editarResena) para no mezclarlos con el formulario de arriba.
        $datos = $request->validateWithBag('editarResena', $this->reglas(), $this->mensajes());

        $comentario = trim((string) ($datos['comentario'] ?? ''));

        $resena->update([
            'calificacion' => $datos['calificacion'],
            'comentario' => $comentario !== '' ? $comentario : null,
        ]);

        // Con #resenas la persona se queda en la sección de reseñas, donde estaba editando.
        return redirect()
            ->to(route('comercio.show', $resena->comercio_id).'#resenas')
            ->with('status_resena', 'Actualizamos tu calificación.');
    }

    public function destroy(Request $request, Resena $resena): RedirectResponse
    {
        $usuario = $request->user();

        abort_unless($usuario->esAdmin() || $resena->user_id === $usuario->id, 403);

        $comercioId = $resena->comercio_id;
        $resena->delete();

        return redirect()
            ->route('comercio.show', $comercioId)
            ->with('status_resena', 'La reseña se eliminó.');
    }

    /** Reglas compartidas por crear (store) y editar (update). */
    private function reglas(): array
    {
        return [
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'calificacion.required' => 'Elegí de 1 a 5 estrellas.',
            'calificacion.integer' => 'Elegí de 1 a 5 estrellas.',
            'calificacion.between' => 'Elegí de 1 a 5 estrellas.',
            'comentario.max' => 'El comentario puede tener hasta 1000 caracteres.',
        ];
    }
}
