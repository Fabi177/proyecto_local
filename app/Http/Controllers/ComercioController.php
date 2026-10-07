<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use App\Models\Localidad;
use App\Models\User;
use App\Support\Rubros;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComercioController extends Controller
{
    /**
     * /dashboard: lo puede ver cualquiera.
     *
     * - Administrador: se lo lleva a su panel.
     * - Comerciante: ve su panel (la vista decide, no necesita el listado).
     * - Visitante o cliente: ve el buscador, el filtro por rubro y todos los comercios.
     */
    public function dashboard(Request $request): View|RedirectResponse
    {
        $usuario = $request->user();

        if ($usuario && $usuario->esAdmin()) {
            // Se conserva el mensaje (status) de la redirección anterior.
            session()->reflash();

            return redirect()->route('admin.index');
        }

        $filtros = $this->filtrosDeBusqueda($request);
        $esComerciante = $usuario && $usuario->role === 'comerciante';

        // Localidad elegida en el buscador. Si el id no existe (enlace viejo o a mano), se ignora
        // en vez de mostrar una lista vacía sin explicación.
        $localidadElegida = isset($filtros['localidad']) ? Localidad::find($filtros['localidad']) : null;
        if (! $localidadElegida) {
            unset($filtros['localidad']);
        }

        return view('dashboard', [
            'comercios' => $esComerciante ? null : $this->consultaComercios($filtros)->paginate(12)->withQueryString(),
            'filters' => $filtros,
            'categorias' => Rubros::categorias(),
            'localidadElegida' => $localidadElegida,
        ]);
    }

    /**
     * Toma del pedido el texto buscado, los rubros y la localidad elegidos, ya limpios.
     * Solo devuelve las claves que tienen algo ('search', 'rubro' y/o 'localidad' con su id).
     */
    private function filtrosDeBusqueda(Request $request): array
    {
        $search = $request->input('search');
        $search = is_string($search) ? trim($search) : '';

        $rubros = collect(Arr::wrap($request->input('rubro')))
            ->filter(fn ($rubro) => is_string($rubro) && trim($rubro) !== '')
            ->map(fn ($rubro) => Rubros::canonica($rubro))
            ->unique()
            ->values()
            ->all();

        $filtros = array_filter(
            ['search' => $search, 'rubro' => $rubros],
            fn ($valor) => $valor !== '' && $valor !== []
        );

        // La localidad viaja como su id (número entero positivo); cualquier otra cosa se descarta.
        $localidad = $request->input('localidad');
        if (is_string($localidad) && ctype_digit($localidad) && (int) $localidad > 0) {
            $filtros['localidad'] = (int) $localidad;
        }

        return $filtros;
    }

    /**
     * Consulta de comercios con los filtros aplicados (más nuevos primero),
     * con el promedio y la cantidad de reseñas para mostrar las estrellas.
     */
    private function consultaComercios(array $filtros)
    {
        $query = Comercio::latest()
            ->with('localidad')
            ->where('habilitado', true)
            ->withAvg('resenas', 'calificacion')
            ->withCount('resenas');

        // Búsqueda general: nombre, descripción o rubro (por su clave o por su nombre: "pizz" encuentra Pizzería).
        $query->when($filtros['search'] ?? null, function ($query, $searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $searchTerm = strtolower(trim($searchTerm));

                $q->whereRaw('LOWER(TRIM(nombre)) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(TRIM(descripcion)) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(TRIM(rubro)) LIKE ?', ["%{$searchTerm}%"])
                  ->orCoincideRubro($searchTerm);
            });
        });

        // Filtro por rubro (uno o varios): el comercio tiene que estar en alguno de los elegidos.
        $query->when($filtros['rubro'] ?? null, fn ($query, $rubros) => $query->conAlgunRubro($rubros));

        // Filtro por localidad (id): solo los comercios de esa ciudad.
        $query->when($filtros['localidad'] ?? null, function ($query, $localidadId) {
            $query->where('localidad_id', $localidadId);
        });

        return $query;
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
            ->where('habilitado', true)
            ->where(function ($q) use ($contiene, $termino) {
                $q->whereRaw("LOWER(TRIM(nombre)) LIKE ? ESCAPE '!'", [$contiene])
                  ->orWhereRaw("LOWER(TRIM(descripcion)) LIKE ? ESCAPE '!'", [$contiene])
                  ->orWhereRaw("LOWER(TRIM(rubro)) LIKE ? ESCAPE '!'", [$contiene])
                  ->orCoincideRubro($termino);
            })
            ->orderByRaw("CASE WHEN LOWER(TRIM(nombre)) LIKE ? ESCAPE '!' THEN 0 ELSE 1 END", [$empieza])
            ->orderBy('nombre')
            ->limit(8)
            ->get(['id', 'nombre', 'rubro', 'rubros', 'direccion', 'logo']);

        return response()->json([
            'sugerencias' => $comercios->map(fn (Comercio $comercio) => [
                'id' => $comercio->id,
                'nombre' => $comercio->nombre,
                'rubro' => $comercio->rubrosResumen(),
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
        return view('comercios.create', ['localidades' => $this->listaDeLocalidades()]);
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
            'localidad_id' => ['required', 'integer', 'exists:localidades,id'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitud'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitud'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'rubros' => ['required', 'array', 'min:1'],
            'rubros.*' => ['string', Rule::in(Rubros::claves())],
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
        ], array_merge($this->mensajesLogo(), $this->mensajesHorarios(), $this->mensajesLocalidad(), $this->mensajesRubros()));

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
        $usuario = Auth::user();

        // Un comercio deshabilitado solo lo ven su dueño y el administrador.
        abort_unless(
            $comercio->habilitado || ($usuario && ($usuario->esAdmin() || $comercio->user_id === $usuario->id)),
            404
        );

        return view('comercios.show', array_merge(
            [
                'comercio' => $comercio,
                'deshabilitado' => ! $comercio->habilitado,
                // Calificaciones y comentarios: se leen sin sesión; solo se escribe con sesión de cliente.
                'resenas' => $comercio->resenas()->with('user:id,name')->latest()->paginate(10)->fragment('resenas'),
                'promedio' => (float) $comercio->resenas()->avg('calificacion'),
                'totalResenas' => $comercio->resenas()->count(),
                'miResena' => $usuario?->esCliente()
                    ? $comercio->resenas()->where('user_id', $usuario->id)->first()
                    : null,
            ],
            $this->datosVolverABusqueda(),
            // El buscador y el botón "Volver a los resultados" son para visitantes y clientes.
            // El comerciante y el administrador, en cambio, vuelven a su propio panel.
            ['mostrarBuscador' => ! in_array($usuario?->role, ['comerciante', 'admin'], true)],
            $this->datosVolverAlPanel($usuario)
        ));
    }

    /**
     * Botón "Volver al panel" del perfil público: solo para el comerciante (su panel)
     * y el administrador (su panel de administración). Visitantes y clientes no lo ven.
     *
     * @return array{panelUrl: string|null, panelTexto: string|null}
     */
    private function datosVolverAlPanel(?User $usuario): array
    {
        if ($usuario?->esAdmin()) {
            return ['panelUrl' => route('admin.comercios'), 'panelTexto' => 'Volver al panel de administración'];
        }

        if ($usuario?->role === 'comerciante') {
            return ['panelUrl' => route('dashboard'), 'panelTexto' => 'Volver a mi panel'];
        }

        return ['panelUrl' => null, 'panelTexto' => null];
    }

    /**
     * Arma el enlace para "Volver a los resultados".
     *
     * Si el cliente llegó al perfil desde el dashboard, se vuelve a esa misma búsqueda
     * (mismo texto, rubros y página). Si no, se vuelve al dashboard sin filtros.
     * Solo se toman los filtros conocidos de la URL anterior, nunca la URL completa,
     * así que no se puede usar para redirigir a otro sitio.
     *
     * @return array{volverUrl: string, terminoBuscado: string|null}
     */
    private function datosVolverABusqueda(): array
    {
        $previa = parse_url(url()->previous()) ?: [];
        $dashboard = parse_url(route('dashboard'));

        // ¿La página anterior fue el dashboard (en este mismo sitio)?
        $vieneDelDashboard = ($previa['host'] ?? null) === ($dashboard['host'] ?? null)
            && rtrim($previa['path'] ?? '', '/') === rtrim($dashboard['path'] ?? '', '/');

        $filtros = [];
        if ($vieneDelDashboard) {
            parse_str($previa['query'] ?? '', $consulta);

            foreach ($consulta as $clave => $valor) {
                if (in_array($clave, ['search', 'page'], true) && is_string($valor) && trim($valor) !== '') {
                    $filtros[$clave] = $valor;
                } elseif ($clave === 'localidad' && is_string($valor) && ctype_digit($valor) && (int) $valor > 0) {
                    $filtros['localidad'] = $valor;
                } elseif ($clave === 'rubro') {
                    $rubros = array_values(array_filter(
                        Arr::wrap($valor),
                        fn ($rubro) => is_string($rubro) && trim($rubro) !== ''
                    ));

                    if ($rubros !== []) {
                        $filtros['rubro'] = count($rubros) === 1 ? $rubros[0] : $rubros;
                    }
                }
            }
        }

        $rubro = $filtros['rubro'] ?? null;

        return [
            'volverUrl' => route('dashboard', $filtros),
            'terminoBuscado' => $filtros['search'] ?? (is_array($rubro) ? implode(', ', $rubro) : $rubro),
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
            'comercio' => $comercio,
            'localidades' => $this->listaDeLocalidades(),
            // Adónde vuelve quien edita: el comerciante a su panel y el administrador a su listado de comercios.
            'panelUrl' => route($this->rutaDePanel()),
            'panelTexto' => Auth::user()->esAdmin() ? 'Volver al panel de administración' : 'Volver a mi panel',
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
            // "sometimes": si el pedido no trae la localidad se conserva la que ya tiene el comercio;
            // si la trae, tiene que ser válida (nunca vacía).
            'localidad_id' => ['sometimes', 'required', 'integer', 'exists:localidades,id'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitud'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitud'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'rubros' => ['required', 'array', 'min:1'],
            'rubros.*' => ['string', Rule::in(Rubros::claves())],
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
        ], array_merge($this->mensajesLogo(), $this->mensajesHorarios(), $this->mensajesLocalidad(), $this->mensajesRubros()));

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
        return redirect()->route($this->rutaDePanel())->with('status', '¡Tu comercio ha sido actualizado con éxito!');
    }

    // -------------------------------------------------------------------
    // --- MÉTODO DE ELIMINACIÓN ---
    // -------------------------------------------------------------------

    /**
     * Elimina el comercio de la base de datos.
     */
    public function destroy(Request $request, Comercio $comercio): RedirectResponse
    {
        // Eliminar para siempre es solo del administrador; el comerciante deshabilita (ver cambiarEstado).
        abort_unless(Auth::user()->esAdmin(), 404);

        // Eliminar el archivo del logo (si tenía) y después el comercio
        $this->borrarLogo($comercio->logo);
        $comercio->delete();

        // Redirigir al dashboard con un mensaje de éxito
        return redirect()->route($this->rutaDePanel())->with('status', 'Tu comercio ha sido eliminado correctamente.');
    }

    /**
     * Habilita o deshabilita el comercio (si estaba habilitado lo deshabilita y viceversa).
     * Deshabilitado: no aparece en el buscador, las sugerencias ni el perfil público,
     * pero el dueño lo sigue viendo en su panel y puede volver a habilitarlo.
     */
    public function cambiarEstado(Comercio $comercio): RedirectResponse
    {
        $this->autorizarComercio($comercio);

        $comercio->habilitado = ! $comercio->habilitado;
        $comercio->save();

        return redirect()->route($this->rutaDePanel())->with('status', $comercio->habilitado
            ? 'Tu comercio fue habilitado: ya se muestra al público.'
            : 'Tu comercio fue deshabilitado: ya no se muestra al público. Podés habilitarlo cuando quieras.');
    }

    // -------------------------------------------------------------------
    // --- AUTORIZACIÓN ---
    // -------------------------------------------------------------------

    /**
     * Ruta del "panel" de quien está editando: el administrador vuelve a su listado de
     * comercios (/admin/comercios) y el comerciante a su dashboard.
     */
    private function rutaDePanel(): string
    {
        return Auth::user()->esAdmin() ? 'admin.comercios' : 'dashboard';
    }

    /**
     * Cada comerciante solo puede editar o eliminar SUS comercios; el administrador puede con todos.
     * Si el comercio es de otro usuario respondemos 404 (no revelamos que existe).
     */
    private function autorizarComercio(Comercio $comercio): void
    {
        abort_unless($comercio->user_id === Auth::id() || Auth::user()->esAdmin(), 404);
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

    /**
     * Mensajes de error en español para la validación de los rubros.
     */
    private function mensajesRubros(): array
    {
        return [
            'rubros.required' => 'Elegí al menos un rubro para tu comercio.',
            'rubros.array' => 'Los rubros elegidos no son válidos. Volvé a elegirlos.',
            'rubros.min' => 'Elegí al menos un rubro para tu comercio.',
            'rubros.*.in' => 'Uno de los rubros elegidos no es válido.',
            'rubros.*.string' => 'Uno de los rubros elegidos no es válido.',
        ];
    }

    // -------------------------------------------------------------------
    // --- AUXILIARES DE LOCALIDAD ---
    // -------------------------------------------------------------------

    /**
     * Localidades para el desplegable de los formularios de alta y edición (por nombre).
     */
    private function listaDeLocalidades()
    {
        return Localidad::orderBy('nombre')->get(['id', 'nombre', 'codigo_postal']);
    }

    /**
     * Mensajes de error en español para la validación de la localidad.
     */
    private function mensajesLocalidad(): array
    {
        return [
            'localidad_id.required' => 'Elegí la localidad de tu comercio.',
            'localidad_id.integer' => 'La localidad elegida no es válida.',
            'localidad_id.exists' => 'La localidad elegida no es válida.',
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
