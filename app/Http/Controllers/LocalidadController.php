<?php

namespace App\Http\Controllers;

use App\Models\Localidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocalidadController extends Controller
{
    /**
     * Sugerencias en vivo del selector de ciudad / código postal (autocompletado).
     *
     * - Solo se sugieren localidades que tienen al menos un comercio habilitado, así nunca
     *   se llega a una lista vacía.
     * - Si lo escrito son solo números se busca por código postal (empieza con): 3315.
     *   Si no, se busca por nombre (contiene), sin importar mayúsculas ni tildes: "obera" => Oberá.
     * - Las que EMPIEZAN con el texto salen primero; después las que tienen más comercios.
     * - Sin texto devuelve las de más comercios (para mostrar opciones al hacer clic en el campo).
     * - Máximo 8 resultados y solo datos públicos y mínimos.
     */
    public function sugerencias(Request $request): JsonResponse
    {
        $crudo = $request->query('q');
        $termino = is_string($crudo) ? Localidad::normalizar(mb_substr($crudo, 0, 60)) : '';

        $query = Localidad::query()
            ->whereHas('comercios', fn ($q) => $q->where('habilitado', true))
            ->withCount(['comercios' => fn ($q) => $q->where('habilitado', true)]);

        if ($termino !== '') {
            // Los comodines de LIKE (% y _) se toman como texto común. Se usa "!" como carácter de
            // escape (y no la barra invertida) para que funcione igual en MySQL y en SQLite.
            $escapado = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $termino);

            if (ctype_digit($termino)) {
                $query->where('codigo_postal', 'like', "{$escapado}%")
                    ->orderBy('codigo_postal');
            } else {
                $query->whereRaw("nombre_busqueda LIKE ? ESCAPE '!'", ["%{$escapado}%"])
                    ->orderByRaw("CASE WHEN nombre_busqueda LIKE ? ESCAPE '!' THEN 0 ELSE 1 END", ["{$escapado}%"]);
            }
        }

        $localidades = $query
            ->orderByDesc('comercios_count')
            ->orderBy('nombre')
            ->limit(8)
            ->get();

        return response()->json([
            'sugerencias' => $localidades->map(fn (Localidad $localidad) => [
                'id' => $localidad->id,
                'nombre' => $localidad->nombre,
                'provincia' => $localidad->provincia,
                'codigo_postal' => $localidad->codigo_postal,
                'comercios' => (int) $localidad->comercios_count,
                'etiqueta' => $localidad->etiqueta,
            ])->values(),
        ]);
    }
}
