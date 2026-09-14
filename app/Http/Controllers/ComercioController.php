<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComercioController extends Controller
{
    /**
     * Muestra la lista pública de comercios (con filtros).
     *
     * Se ha mejorado el filtro 'search' para incluir el campo 'rubro'.
     */
    public function index(Request $request): View
    {
        // 1. Iniciar la consulta con los más nuevos primero
        $query = Comercio::latest();

        // 2. Aplicar filtro de búsqueda general (Nombre O Descripción O RUBRO)
        $query->when($request->input('search'), function ($query, $searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                // Limpiamos el término de búsqueda (espacios y minúsculas)
                $searchTerm = strtolower(trim($searchTerm));

                // Comparamos la columna (limpiada con TRIM y LOWER) con el término
                // AHORA INCLUYE EL RUBRO EN LA BÚSQUEDA GENERAL
                $q->whereRaw('LOWER(TRIM(nombre)) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(TRIM(descripcion)) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(TRIM(rubro)) LIKE ?', ["%{$searchTerm}%"]); // <<< CAMBIO CLAVE
            });
        });

        // 3. Aplicar filtro por rubro (Categoría) - (Condición AND)
        // Este filtro se mantiene para búsquedas exactas (ej: si se hace clic en un tag/enlace de categoría)
        $query->when($request->input('rubro'), function ($query, $rubro) {
            // Limpiamos el término del rubro (espacios y minúsculas)
            $rubroTerm = strtolower(trim($rubro));

            // Comparamos la columna (limpiada con TRIM y LOWER) con el término
            $query->whereRaw('LOWER(TRIM(rubro)) = ?', [$rubroTerm]);
        });

        // 4. Ejecutar la consulta y paginar
        $comercios = $query->paginate(12);

        // 5. Enviar los comercios y los filtros a la vista
        return view('comercios.index', [
            'comercios' => $comercios,
            'filters' => $request->only(['search', 'rubro'])
        ]);
    }

    /**
     * Muestra el formulario de registro del comercio.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::user()->comercio) {
            return redirect()->route('comercio.edit')->with('status', 'Ya tienes un comercio registrado. Aquí puedes editarlo.');
        }
        return view('comercios.create');
    }

    /**
     * Guarda el nuevo comercio en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. VALIDACIÓN DE DATOS
        $validatedData = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'rubro' => ['required', 'string', 'max:100'],
            'horarios_atencion' => ['nullable', 'string', 'max:255'],
            'dias_no_laborales' => ['nullable', 'string', 'max:255'],
            'formas_pago' => ['nullable', 'string', 'max:255'],
            'servicios_adicionales' => ['nullable', 'string'],
            'sitio_web' => ['nullable', 'string', 'url', 'max:255'],
            'red_instagram' => ['nullable', 'string', 'max:100'],
            'red_facebook' => ['nullable', 'string', 'max:100'],
            'red_whatsapp' => ['nullable', 'string', 'max:50'],
        ]);

        // 2. PROCESAR LOS CHECKBOXES
        $validatedData['ingreso_discapacitados'] = $request->has('ingreso_discapacitados');
        $validatedData['estacionamiento'] = $request->has('estacionamiento');

        // 3. GUARDAR EL COMERCIO
        $request->user()->comercio()->create($validatedData);

        // 4. REDIRIGIR AL USUARIO
        return redirect()->route('dashboard')->with('status', '¡Tu comercio ha sido registrado con éxito!');
    }

    // -------------------------------------------------------------------
    // --- MÉTODO DE VISTA PÚBLICA ---
    // -------------------------------------------------------------------

    /**
     * Muestra el perfil público del comercio.
     */
    public function show(Comercio $comercio): View
    {
        return view('comercios.show', [
            'comercio' => $comercio
        ]);
    }


    // -------------------------------------------------------------------
    // --- MÉTODOS DE MODIFICACIÓN ---
    // -------------------------------------------------------------------

    /**
     * Muestra el formulario para editar el comercio existente.
     */
    public function edit(): View|RedirectResponse
    {
        $comercio = Auth::user()->comercio;

        if (!$comercio) {
            return redirect()->route('comercio.create')->with('status', 'Primero debes registrar tu comercio.');
        }

        return view('comercios.edit', [
            'comercio' => $comercio
        ]);
    }

    /**
     * Actualiza el comercio en la base de datos.
     */
    public function update(Request $request): RedirectResponse
    {
        $comercio = $request->user()->comercio;

        if (!$comercio) {
            return redirect()->route('comercio.create')->with('status', 'Primero debes registrar tu comercio.');
        }

        // 1. VALIDACIÓN DE DATOS
        $validatedData = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'rubro' => ['required', 'string', 'max:100'],
            'horarios_atencion' => ['nullable', 'string', 'max:255'],
            'dias_no_laborales' => ['nullable', 'string', 'max:255'],
            'formas_pago' => ['nullable', 'string', 'max:255'],
            'servicios_adicionales' => ['nullable', 'string'],
            'sitio_web' => ['nullable', 'string', 'url', 'max:255'],
            'red_instagram' => ['nullable', 'string', 'max:100'],
            'red_facebook' => ['nullable', 'string', 'max:100'],
            'red_whatsapp' => ['nullable', 'string', 'max:50'],
        ]);

        // 2. PROCESAR LOS CHECKBOXES
        $validatedData['ingreso_discapacitados'] = $request->has('ingreso_discapacitados');
        $validatedData['estacionamiento'] = $request->has('estacionamiento');

        // 3. ACTUALIZAR EL COMERCIO
        $comercio->update($validatedData);

        // 4. REDIRIGIR AL USUARIO
        return redirect()->route('dashboard')->with('status', '¡Tu comercio ha sido actualizado con éxito!');
    }

    // -------------------------------------------------------------------
    // --- MÉTODO DE ELIMINACIÓN ---
    // -------------------------------------------------------------------

    /**
     * Elimina el comercio de la base de datos.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $comercio = $request->user()->comercio;

        if (!$comercio) {
            return redirect()->route('dashboard')->with('status', 'No tienes ningún comercio para eliminar.');
        }

        // Eliminar el comercio
        $comercio->delete();

        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('status', 'Tu comercio ha sido eliminado correctamente.');
    }
}
