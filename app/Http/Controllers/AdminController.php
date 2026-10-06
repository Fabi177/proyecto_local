<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Panel de administración. Todas sus rutas están protegidas con los middleware
 * 'auth' y 'admin' (ver routes/web.php).
 *
 * Editar y eliminar comercios se hace con las rutas de siempre (comercio.edit,
 * comercio.update, comercio.destroy): ComercioController::autorizarComercio()
 * deja pasar al administrador a cualquier comercio.
 */
class AdminController extends Controller
{
    /**
     * Resumen general.
     */
    public function index(): View
    {
        return view('admin.index', [
            'totales' => [
                'clientes' => User::where('role', 'usuario')->count(),
                'comerciantes' => User::where('role', 'comerciante')->count(),
                'administradores' => User::where('role', 'admin')->count(),
                'comercios' => Comercio::count(),
            ],
            'ultimosComercios' => Comercio::with('user:id,name,email')->latest()->limit(5)->get(),
        ]);
    }

    /**
     * Listado de TODOS los comercios (con buscador y paginación).
     */
    public function comercios(Request $request): View
    {
        $buscado = $this->textoBuscado($request);

        $comercios = Comercio::with(['user:id,name,email', 'localidad:id,nombre'])
            ->when($buscado !== '', function ($consulta) use ($buscado) {
                $like = $this->patronLike($buscado);
                $consulta->where(function ($q) use ($like) {
                    $q->whereRaw("LOWER(nombre) LIKE ? ESCAPE '!'", [$like])
                      ->orWhereRaw("LOWER(rubro) LIKE ? ESCAPE '!'", [$like])
                      ->orWhereRaw("LOWER(direccion) LIKE ? ESCAPE '!'", [$like]);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.comercios', compact('comercios', 'buscado'));
    }

    /**
     * Listado de usuarios (solo lectura), con la cantidad de comercios de cada uno.
     */
    public function usuarios(Request $request): View
    {
        $buscado = $this->textoBuscado($request);

        $usuarios = User::withCount('comercios')
            ->when($buscado !== '', function ($consulta) use ($buscado) {
                $like = $this->patronLike($buscado);
                $consulta->where(function ($q) use ($like) {
                    $q->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like])
                      ->orWhereRaw("LOWER(email) LIKE ? ESCAPE '!'", [$like]);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios', compact('usuarios', 'buscado'));
    }

    /** Texto del buscador (?q=) limpio y con un largo máximo. */
    private function textoBuscado(Request $request): string
    {
        $crudo = $request->query('q');

        return is_string($crudo) ? mb_substr(trim($crudo), 0, 100) : '';
    }

    /** Arma el patrón "%texto%" tratando % y _ como texto común ("!" es el carácter de escape). */
    private function patronLike(string $texto): string
    {
        $escapado = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($texto));

        return "%{$escapado}%";
    }
}
