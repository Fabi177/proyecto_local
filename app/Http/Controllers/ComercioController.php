<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * Sugerencias en vivo para los buscadores (autocompletado).
     *
     * Devuelve como máximo 8 comercios cuyo nombre, descripción o rubro contienen lo que
     * la persona va escribiendo (mismos campos que el buscador de index()). Los que
     * EMPIEZAN con el texto salen primero. Solo se devuelven datos públicos y mínimos.
     * Con menos de 2 letras no se consulta la base de datos.
     */
    public function sugerencias(Request $request): JsonResponse
    {
        $crudo = $request->query('q');
        $termino = is_string($crudo) ? (preg_replace('/\s+/u', ' ', $crudo) ?? '') : '';
        $termino = mb_substr(mb_strtolower(trim($termino)), 0, 100);

        if (mb_strlen($termino) < 2) {
            return response()->json(['sugerencias' => []]);
        }

        // Los comodines de LIKE (% y _) se toman como texto común. Se usa "!" como carácter de
        // escape (y no la barra invertida) para que funcione igual en MySQL y en SQLite.
        $escapado = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $termino);
        $contiene = "%{$escapado}%";
        $empieza = "{$escapado}%";

        $comercios = Comercio::query()
            ->where(function ($q) use ($contiene) {
                $q->whereRaw("LOWER(TRIM(nombre)) LIKE ? ESCAPE '!'", [$contiene])
                  ->orWhereRaw("LOWER(TRIM(descripcion)) LIKE ? ESCAPE '!'", [$contiene])
                  ->orWhereRaw("LOWER(TRIM(rubro)) LIKE ? ESCAPE '!'", [$contiene]);
            })
            ->orderByRaw("CASE WHEN LOWER(TRIM(nombre)) LIKE ? ESCAPE '!' THEN 0 ELSE 1 END", [$empieza])
            ->orderBy('nombre')
            ->limit(8)
            ->get(['id', 'nombre', 'rubro', 'direccion', 'logo']);

        return response()->json([
            'sugerencias' => $comercios->map(fn (Comercio $comercio) => [
                'id' => $comercio->id,
                'nombre' => $comercio->nombre,
                'rubro' => $comercio->rubro,
                'direccion' => $comercio->direccion,
                'logo' => $comercio->logo_url,
                'url' => route('comercio.show', ['comercio' => $comercio->id]),
            ])->values(),
        ]);
    }

    /**
     * Muestra el formulario de registro del comercio.
     */
    public function create(): View|RedirectResponse
    {
        // Un comerciante puede registrar todos los comercios que quiera.
        return view('comercios.create');
    }

    /**
     * Guarda el nuevo comercio en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. VALIDACIÓN DE DATOS
        $this->decodificarJsonHorarios($request);
        $validatedData = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitud'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitud'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'rubro' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'horarios_atencion' => ['nullable', 'string', 'max:1500'],
            'dias_no_laborales' => ['nullable', 'string', 'max:3000'],
            'formas_pago' => ['nullable', 'string', 'max:500'],
            // Datos estructurados de los selectores de horarios y de fechas de cierre
            'horarios_config' => ['nullable', 'array', 'size:7'],
            'horarios_config.*.open' => ['required', 'boolean'],
            'horarios_config.*.t' => ['present', 'array', 'max:2'],
            'horarios_config.*.t.*' => ['array', 'size:2'],
            'horarios_config.*.t.*.*' => ['required', 'date_format:H:i'],
            'dias_cierre' => ['nullable', 'array', 'max:50'],
            'dias_cierre.*.a' => ['required', 'date_format:Y-m-d'],
            'dias_cierre.*.b' => ['nullable', 'date_format:Y-m-d'],
            'dias_cierre.*.w' => ['nullable', 'string', 'max:40'],
            'servicios_adicionales' => ['nullable', 'string'],
            'sitio_web' => ['nullable', 'string', 'url', 'max:255'],
            'red_instagram' => ['nullable', 'string', 'max:100'],
            'red_facebook' => ['nullable', 'string', 'max:100'],
            'red_whatsapp' => ['nullable', 'string', 'max:50'],
        ], array_merge($this->mensajesLogo(), $this->mensajesHorarios()));

        // 2. PROCESAR LOS CHECKBOXES
        foreach (array_keys(Comercio::ACCESIBILIDAD) as $campo) {
            $validatedData[$campo] = $request->has($campo);
        }
        $validatedData['cierra_feriados'] = $request->has('cierra_feriados');
        $validatedData = $this->normalizarHorarios($validatedData);

        // 2.1 PROCESAR EL LOGO (si se subió uno)
        unset($validatedData['logo']);
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // 3. GUARDAR EL COMERCIO
        $request->user()->comercios()->create($validatedData);

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
        return view('comercios.show', array_merge(
            ['comercio' => $comercio],
            $this->datosVolverABusqueda(),
            // El buscador y el botón "Volver" son para el cliente, no para el comerciante.
            ['mostrarBuscador' => Auth::user()?->role !== 'comerciante']
        ));
    }

    /**
     * Arma el enlace para "Volver a los resultados".
     *
     * Si el cliente llegó al perfil desde el buscador (/comercios), se vuelve a esa
     * misma búsqueda (mismo texto, rubro y página). Si no, se vuelve al buscador
     * sin filtros. Solo se toman los filtros conocidos de la URL anterior, nunca
     * la URL completa, así que no se puede usar para redirigir a otro sitio.
     *
     * @return array{volverUrl: string, terminoBuscado: string|null}
     */
    private function datosVolverABusqueda(): array
    {
        $previa = parse_url(url()->previous()) ?: [];
        $buscador = parse_url(route('comercios.index'));

        $vieneDelBuscador = ($previa['host'] ?? null) === ($buscador['host'] ?? null)
            && rtrim($previa['path'] ?? '', '/') === rtrim($buscador['path'] ?? '', '/');

        $filtros = [];
        if ($vieneDelBuscador) {
            parse_str($previa['query'] ?? '', $consulta);
            $filtros = array_filter(
                array_intersect_key($consulta, array_flip(['search', 'rubro', 'page'])),
                fn ($valor) => is_string($valor) && trim($valor) !== ''
            );
        }

        return [
            'volverUrl' => route('comercios.index', $filtros),
            'terminoBuscado' => $filtros['search'] ?? ($filtros['rubro'] ?? null),
        ];
    }


    // -------------------------------------------------------------------
    // --- MÉTODOS DE MODIFICACIÓN ---
    // -------------------------------------------------------------------

    /**
     * Muestra el formulario para editar el comercio existente.
     */
    public function edit(Comercio $comercio): View
    {
        $this->autorizarComercio($comercio);

        return view('comercios.edit', [
            'comercio' => $comercio
        ]);
    }

    /**
     * Actualiza el comercio en la base de datos.
     */
    public function update(Request $request, Comercio $comercio): RedirectResponse
    {
        $this->autorizarComercio($comercio);

        // 1. VALIDACIÓN DE DATOS
        $this->decodificarJsonHorarios($request);
        $validatedData = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitud'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitud'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'rubro' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'horarios_atencion' => ['nullable', 'string', 'max:1500'],
            'dias_no_laborales' => ['nullable', 'string', 'max:3000'],
            'formas_pago' => ['nullable', 'string', 'max:500'],
            // Datos estructurados de los selectores de horarios y de fechas de cierre
            'horarios_config' => ['nullable', 'array', 'size:7'],
            'horarios_config.*.open' => ['required', 'boolean'],
            'horarios_config.*.t' => ['present', 'array', 'max:2'],
            'horarios_config.*.t.*' => ['array', 'size:2'],
            'horarios_config.*.t.*.*' => ['required', 'date_format:H:i'],
            'dias_cierre' => ['nullable', 'array', 'max:50'],
            'dias_cierre.*.a' => ['required', 'date_format:Y-m-d'],
            'dias_cierre.*.b' => ['nullable', 'date_format:Y-m-d'],
            'dias_cierre.*.w' => ['nullable', 'string', 'max:40'],
            'servicios_adicionales' => ['nullable', 'string'],
            'sitio_web' => ['nullable', 'string', 'url', 'max:255'],
            'red_instagram' => ['nullable', 'string', 'max:100'],
            'red_facebook' => ['nullable', 'string', 'max:100'],
            'red_whatsapp' => ['nullable', 'string', 'max:50'],
        ], array_merge($this->mensajesLogo(), $this->mensajesHorarios()));

        // 2. PROCESAR LOS CHECKBOXES
        foreach (array_keys(Comercio::ACCESIBILIDAD) as $campo) {
            $validatedData[$campo] = $request->has($campo);
        }
        $validatedData['cierra_feriados'] = $request->has('cierra_feriados');
        $validatedData = $this->normalizarHorarios($validatedData);

        // 2.1 PROCESAR EL LOGO: reemplazar, quitar o dejar el que ya tenía
        unset($validatedData['logo']);
        if ($request->hasFile('logo')) {
            $this->borrarLogo($comercio->logo);
            $validatedData['logo'] = $request->file('logo')->store('logos', 'public');
        } elseif ($request->boolean('quitar_logo')) {
            $this->borrarLogo($comercio->logo);
            $validatedData['logo'] = null;
        }

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
    public function destroy(Request $request, Comercio $comercio): RedirectResponse
    {
        $this->autorizarComercio($comercio);

        // Eliminar el archivo del logo (si tenía) y después el comercio
        $this->borrarLogo($comercio->logo);
        $comercio->delete();

        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('status', 'Tu comercio ha sido eliminado correctamente.');
    }

    // -------------------------------------------------------------------
    // --- AUTORIZACIÓN ---
    // -------------------------------------------------------------------

    /**
     * Cada comerciante solo puede editar o eliminar SUS comercios.
     * Si el comercio es de otro usuario respondemos 404 (no revelamos que existe).
     */
    private function autorizarComercio(Comercio $comercio): void
    {
        abort_unless($comercio->user_id === Auth::id(), 404);
    }

    // -------------------------------------------------------------------
    // --- AUXILIARES DEL LOGO ---
    // -------------------------------------------------------------------

    /**
     * Borra del disco "public" el archivo del logo, si existe.
     */
    private function borrarLogo(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk('public')->delete($ruta);
        }
    }

    /**
     * Mensajes de error en español para la validación del logo.
     */
    private function mensajesLogo(): array
    {
        return [
            'logo.image' => 'El logo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser un archivo JPG, PNG o WEBP.',
            'logo.max' => 'El logo no puede pesar más de 2 MB.',
            'logo.uploaded' => 'No se pudo subir el logo. Verificá que no pese más de 2 MB.',
        ];
    }

    // -------------------------------------------------------------------
    // --- AUXILIARES DE HORARIOS Y FECHAS DE CIERRE ---
    // -------------------------------------------------------------------

    /**
     * Los selectores de horarios y de cierres envían su contenido como un texto JSON en un
     * campo oculto. Acá lo convertimos en array para poder validarlo con las reglas de arriba.
     * Si el JSON viene roto, se deja un valor inválido a propósito para que la validación falle.
     */
    private function decodificarJsonHorarios(Request $request): void
    {
        foreach (['horarios_config', 'dias_cierre'] as $campo) {
            $crudo = $request->input($campo);

            if (is_string($crudo) && $crudo !== '') {
                $decodificado = json_decode($crudo, true);
                $request->merge([$campo => is_array($decodificado) ? $decodificado : 'invalido']);
            }
        }
    }

    /**
     * Deja los datos estructurados prolijos antes de guardarlos:
     *  - horarios: "open" como booleano y turnos reindexados;
     *  - cierres: si "hasta" es anterior a "desde" se intercambian, y si son iguales se descarta "hasta".
     */
    private function normalizarHorarios(array $datos): array
    {
        if (isset($datos['horarios_config'])) {
            $datos['horarios_config'] = array_values(array_map(fn ($dia) => [
                'open' => filter_var($dia['open'], FILTER_VALIDATE_BOOLEAN),
                't' => array_values(array_map(fn ($turno) => [$turno[0], $turno[1]], $dia['t'] ?? [])),
            ], $datos['horarios_config']));
        }

        if (isset($datos['dias_cierre'])) {
            $datos['dias_cierre'] = array_values(array_map(function ($cierre) {
                $desde = $cierre['a'];
                $hasta = $cierre['b'] ?? null;

                if ($hasta !== null && $hasta < $desde) {
                    [$desde, $hasta] = [$hasta, $desde];
                }
                if ($hasta === $desde) {
                    $hasta = null;
                }

                return ['a' => $desde, 'b' => $hasta, 'w' => $cierre['w'] ?? null];
            }, $datos['dias_cierre']));
        }

        return $datos;
    }

    /**
     * Mensajes de error en español para la validación de horarios y fechas de cierre.
     */
    private function mensajesHorarios(): array
    {
        return [
            'horarios_config.array' => 'Los horarios de atención no son válidos. Volvé a cargarlos.',
            'horarios_config.size' => 'Los horarios de atención deben incluir los 7 días de la semana.',
            'horarios_config.*.t.*.*.required' => 'Completá la hora de apertura y de cierre de cada turno.',
            'horarios_config.*.t.*.*.date_format' => 'Una de las horas de atención no tiene un formato válido.',
            'dias_cierre.array' => 'Las fechas de cierre no son válidas. Volvé a cargarlas.',
            'dias_cierre.max' => 'Podés cargar hasta 50 fechas de cierre.',
            'dias_cierre.*.a.required' => 'Una de las fechas de cierre no tiene fecha de inicio.',
            'dias_cierre.*.a.date_format' => 'Una de las fechas de cierre no es válida.',
            'dias_cierre.*.b.date_format' => 'Una de las fechas de cierre no es válida.',
            'dias_cierre.*.w.max' => 'El motivo de un cierre no puede superar los 40 caracteres.',
        ];
    }
}
