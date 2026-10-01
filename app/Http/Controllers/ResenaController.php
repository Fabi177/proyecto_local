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
 * - Pueden borrar una reseña su autor y el administrador.
 */
class ResenaController extends Controller
{
    public function store(Request $request, Comercio $comercio): RedirectResponse
    {
        abort_unless($request->user()->esCliente(), 403, 'Solo los clientes pueden calificar comercios.');

        $datos = $request->validate([
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ], [
            'calificacion.required' => 'Elegí de 1 a 5 estrellas.',
            'calificacion.integer' => 'Elegí de 1 a 5 estrellas.',
            'calificacion.between' => 'Elegí de 1 a 5 estrellas.',
            'comentario.max' => 'El comentario puede tener hasta 1000 caracteres.',
        ]);

        $comentario = trim((string) ($datos['comentario'] ?? ''));

        $resena = Resena::updateOrCreate(
            ['comercio_id' => $comercio->id, 'user_id' => $request->user()->id],
            ['calificacion' => $datos['calificacion'], 'comentario' => $comentario !== '' ? $comentario : null],
        );

        return redirect()
            ->route('comercio.show', $comercio)
            ->with('status_resena', $resena->wasRecentlyCreated ? '¡Gracias por tu calificación!' : 'Actualizamos tu calificación.');
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
}
