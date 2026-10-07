<?php

use App\Models\Comercio;
use App\Models\Localidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function rubrosDueno(): User
{
    return User::factory()->create(['role' => 'comerciante']);
}

/** Localidad que el alta exige (viene cargada por la migración de localidades). */
function rubrosLocalidad(): int
{
    return Localidad::where('nombre', 'Leandro N. Alem')->value('id');
}

function rubrosComercio(array $datos = [], ?User $dueno = null): Comercio
{
    return Comercio::create(array_merge([
        'user_id' => ($dueno ?? rubrosDueno())->id,
        'nombre' => 'Comercio de prueba',
        'direccion' => 'Calle Falsa 123',
        'rubros' => ['Restaurante'],
    ], $datos));
}

/** Etiqueta <input> del checkbox de un rubro dentro del HTML de la página. */
function checkboxDeRubro(string $html, string $clave): ?string
{
    preg_match('/<input[^>]*value="' . preg_quote($clave, '/') . '"[^>]*>/s', $html, $coincidencia);

    return $coincidencia[0] ?? null;
}

// ---------------------------------------------------------------------------
// Guardado: el comerciante elige todos los rubros que quiera
// ---------------------------------------------------------------------------

test('al crear su comercio el comerciante puede elegir varios rubros', function () {
    $dueno = rubrosDueno();

    $this->actingAs($dueno)->post(route('comercio.store'), [
        'nombre' => 'Lo de Ana',
        'direccion' => 'Calle 1 123',
        'localidad_id' => rubrosLocalidad(),
        'rubros' => ['Restaurante', 'Pizzeria', 'Delivery'],
    ])->assertSessionHasNoErrors();

    $comercio = Comercio::where('nombre', 'Lo de Ana')->firstOrFail();

    expect($comercio->rubros)->toBe(['Restaurante', 'Pizzeria', 'Delivery'])
        ->and($comercio->rubro)->toBe('Restaurante'); // el principal es el primero
});

test('puede elegir rubros de categorías distintas', function () {
    $this->actingAs(rubrosDueno())->post(route('comercio.store'), [
        'nombre' => 'Todo Auto',
        'direccion' => 'Ruta 14 km 1',
        'localidad_id' => rubrosLocalidad(),
        'rubros' => ['Gomeria', 'Lubricentro', 'LavaderoAutos', 'EstacionServicio', 'Kiosco'],
    ])->assertSessionHasNoErrors();

    expect(Comercio::firstWhere('nombre', 'Todo Auto')->rubros)
        ->toBe(['Gomeria', 'Lubricentro', 'LavaderoAutos', 'EstacionServicio', 'Kiosco']);
});

test('puede elegir todos los rubros del catálogo juntos', function () {
    $todos = App\Support\Rubros::claves();

    $this->actingAs(rubrosDueno())->post(route('comercio.store'), [
        'nombre' => 'Hace de todo',
        'direccion' => 'Calle 1 123',
        'localidad_id' => rubrosLocalidad(),
        'rubros' => $todos,
    ])->assertSessionHasNoErrors();

    expect(Comercio::firstWhere('nombre', 'Hace de todo')->rubros)->toHaveCount(count($todos));
});

test('un rubro repetido se guarda una sola vez', function () {
    $this->actingAs(rubrosDueno())->post(route('comercio.store'), [
        'nombre' => 'Lo de Ana',
        'direccion' => 'Calle 1 123',
        'localidad_id' => rubrosLocalidad(),
        'rubros' => ['Cafe', 'Cafe', 'BarPub'],
    ])->assertSessionHasNoErrors();

    expect(Comercio::firstWhere('nombre', 'Lo de Ana')->rubros)->toBe(['Cafe', 'BarPub']);
});

test('hay que elegir al menos un rubro', function () {
    $dueno = rubrosDueno();
    $base = ['nombre' => 'Lo de Ana', 'direccion' => 'Calle 1 123', 'localidad_id' => rubrosLocalidad()];

    $this->actingAs($dueno)->post(route('comercio.store'), $base)
        ->assertSessionHasErrors(['rubros' => 'Elegí al menos un rubro para tu comercio.']);

    $this->actingAs($dueno)->post(route('comercio.store'), $base + ['rubros' => []])
        ->assertSessionHasErrors(['rubros' => 'Elegí al menos un rubro para tu comercio.']);

    expect(Comercio::count())->toBe(0);
});

test('no se aceptan rubros que no existen en el catálogo', function () {
    $this->actingAs(rubrosDueno())->post(route('comercio.store'), [
        'nombre' => 'Lo de Ana',
        'direccion' => 'Calle 1 123',
        'localidad_id' => rubrosLocalidad(),
        'rubros' => ['Cafe', 'RubroInventado'],
    ])->assertSessionHasErrors(['rubros.1' => 'Uno de los rubros elegidos no es válido.']);

    expect(Comercio::count())->toBe(0);
});

test('un rubro mandado como texto suelto (no como lista) se rechaza', function () {
    $this->actingAs(rubrosDueno())->post(route('comercio.store'), [
        'nombre' => 'Lo de Ana',
        'direccion' => 'Calle 1 123',
        'localidad_id' => rubrosLocalidad(),
        'rubros' => 'Cafe',
    ])->assertSessionHasErrors('rubros');
});

test('al editar se cambian, suman y quitan rubros, y el principal se actualiza', function () {
    $dueno = rubrosDueno();
    $comercio = rubrosComercio(['rubros' => ['Restaurante', 'Pizzeria']], $dueno);

    $this->actingAs($dueno)->patch(route('comercio.update', $comercio), [
        'nombre' => $comercio->nombre,
        'direccion' => $comercio->direccion,
        'rubros' => ['Heladeria', 'Pizzeria', 'Delivery'],
    ])->assertSessionHasNoErrors();

    $comercio->refresh();

    expect($comercio->rubros)->toBe(['Heladeria', 'Pizzeria', 'Delivery'])
        ->and($comercio->rubro)->toBe('Heladeria');
});

test('al editar tampoco se puede dejar el comercio sin rubros', function () {
    $dueno = rubrosDueno();
    $comercio = rubrosComercio(['rubros' => ['Restaurante', 'Pizzeria']], $dueno);

    $this->actingAs($dueno)->patch(route('comercio.update', $comercio), [
        'nombre' => $comercio->nombre,
        'direccion' => $comercio->direccion,
    ])->assertSessionHasErrors('rubros');

    expect($comercio->refresh()->rubros)->toBe(['Restaurante', 'Pizzeria']);
});

test('el administrador también puede cambiar los rubros de un comercio ajeno', function () {
    $comercio = rubrosComercio();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->patch(route('comercio.update', $comercio), [
            'nombre' => $comercio->nombre,
            'direccion' => $comercio->direccion,
            'rubros' => ['Farmacia', 'Perfumeria'],
        ])->assertSessionHasNoErrors();

    expect($comercio->refresh()->rubros)->toBe(['Farmacia', 'Perfumeria']);
});

// ---------------------------------------------------------------------------
// El modelo mantiene "rubro" (principal) y "rubros" (lista) de acuerdo
// ---------------------------------------------------------------------------

test('un comercio creado solo con "rubro" tiene ese rubro como lista', function () {
    $comercio = Comercio::create([
        'user_id' => rubrosDueno()->id,
        'nombre' => 'Viejo estilo',
        'direccion' => 'Calle 1',
        'rubro' => 'Cafe',
    ]);

    expect($comercio->refresh()->rubros)->toBe(['Cafe']);
});

test('al crear con la lista, "rubro" queda con el primero', function () {
    $comercio = rubrosComercio(['rubros' => ['Gomeria', 'Lubricentro']]);

    expect($comercio->refresh()->rubro)->toBe('Gomeria');
});

test('si solo se cambia "rubro", la lista queda con ese único rubro', function () {
    $comercio = rubrosComercio(['rubros' => ['Gomeria', 'Lubricentro']]);

    $comercio->update(['rubro' => 'Farmacia']);

    expect($comercio->refresh()->rubros)->toBe(['Farmacia']);
});

test('las etiquetas, el texto y el resumen de rubros del comercio', function () {
    $comercio = rubrosComercio(['rubros' => ['Cafe', 'BarPub', 'Pasteleria', 'Heladeria']]);

    expect($comercio->rubros_etiquetas)->toBe(['Cafetería', 'Bar / Pub', 'Pastelería', 'Heladería'])
        ->and($comercio->rubros_texto)->toBe('Cafetería, Bar / Pub, Pastelería, Heladería')
        ->and($comercio->rubrosResumen())->toBe('Cafetería, Bar / Pub +2')
        ->and($comercio->rubrosResumen(4))->toBe('Cafetería, Bar / Pub, Pastelería, Heladería');
});

test('un rubro escrito a mano (que no está en el catálogo) se sigue mostrando', function () {
    $comercio = Comercio::create([
        'user_id' => rubrosDueno()->id,
        'nombre' => 'Dato viejo',
        'direccion' => 'Calle 1',
        'rubro' => 'Panadería artesanal',
    ]);

    expect($comercio->refresh()->rubros_etiquetas)->toBe(['Panadería artesanal']);
});

// ---------------------------------------------------------------------------
// Formularios del comerciante
// ---------------------------------------------------------------------------

test('el formulario de alta ofrece las categorías y sus rubros con checkboxes de varios', function () {
    $html = $this->actingAs(rubrosDueno())->get(route('comercio.create'))
        ->assertOk()
        ->assertSee('selectorRubros(', false)
        ->assertSee('name="rubros[]"', false)
        ->assertSee('Rubros / Categorías')
        ->assertSee('Automotores')
        ->assertSee('Gomería')
        ->assertSee('Estación de servicio')
        ->assertDontSee('Selecciona un rubro...')
        ->assertDontSee('<select id="rubro"', false)
        ->getContent();

    // Un checkbox por cada rubro del catálogo.
    expect(substr_count($html, 'name="rubros[]"'))->toBe(count(App\Support\Rubros::claves()));
});

test('el formulario de alta muestra las categorías de a una: los rubros de cada una van en su panel oculto', function () {
    $this->actingAs(rubrosDueno())->get(route('comercio.create'))
        ->assertSee("alternar('Automotores')", false)
        ->assertSee("x-show=\"abierta === 'Automotores'\"", false)
        ->assertSee("x-show=\"abierta === 'Salud'\"", false)
        ->assertSee('🚗', false)
        ->assertSee('🍔', false);
});

test('el formulario de alta viene sin ningún rubro marcado', function () {
    $html = $this->actingAs(rubrosDueno())->get(route('comercio.create'))->getContent();

    expect(substr_count($html, 'name="rubros[]"'))->toBeGreaterThan(100);

    foreach (['Restaurante', 'Gomeria', 'Otro'] as $clave) {
        expect(checkboxDeRubro($html, $clave))->not->toContain('checked');
    }
});

test('el formulario de edición viene con marcados los rubros que ya tiene el comercio', function () {
    $dueno = rubrosDueno();
    $comercio = rubrosComercio(['rubros' => ['Cafe', 'Pizzeria']], $dueno);

    $html = $this->actingAs($dueno)->get(route('comercio.edit', $comercio))->assertOk()->getContent();

    expect(checkboxDeRubro($html, 'Cafe'))->toContain('checked')
        ->and(checkboxDeRubro($html, 'Pizzeria'))->toContain('checked')
        ->and(checkboxDeRubro($html, 'Restaurante'))->not->toContain('checked')
        ->and(checkboxDeRubro($html, 'Gomeria'))->not->toContain('checked');
});

test('si el formulario vuelve con errores se conservan los rubros que había marcado', function () {
    $dueno = rubrosDueno();

    $this->actingAs($dueno)->from(route('comercio.create'))->post(route('comercio.store'), [
        'nombre' => '', // falta el nombre: el formulario vuelve con error
        'direccion' => 'Calle 1 123',
        'rubros' => ['Gomeria', 'Lubricentro'],
    ])->assertSessionHasErrors('nombre');

    $html = $this->get(route('comercio.create'))->getContent();

    expect(checkboxDeRubro($html, 'Gomeria'))->toContain('checked')
        ->and(checkboxDeRubro($html, 'Lubricentro'))->toContain('checked')
        ->and(checkboxDeRubro($html, 'Cafe'))->not->toContain('checked');
});

test('un comercio viejo con el rubro escrito a mano abre la edición sin romperse', function () {
    $dueno = rubrosDueno();
    $comercio = Comercio::create([
        'user_id' => $dueno->id,
        'nombre' => 'Dato viejo',
        'direccion' => 'Calle 1',
        'rubro' => 'Panadería artesanal',
    ]);

    $this->actingAs($dueno)->get(route('comercio.edit', $comercio))->assertOk();
});

// ---------------------------------------------------------------------------
// Filtro del dashboard: dos niveles
// ---------------------------------------------------------------------------

test('el filtro del dashboard muestra primero las categorías con su ícono', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Filtrar por rubro')
        ->assertSee('Rubro principal')
        ->assertSee('Tocá una categoría para ver sus rubros')
        ->assertSee('🍔', false)->assertSee('Gastronomía')
        ->assertSee('🚗', false)->assertSee('Automotores')
        ->assertSee('🏥', false)->assertSee('Salud')
        ->assertSee('⛽', false)->assertSee('Combustibles')
        ->assertSee('🏛️', false)->assertSee('Instituciones');
});

test('los rubros de cada categoría están en su propio panel, que se abre al tocarla', function () {
    $html = $this->get(route('dashboard'))->getContent();

    expect($html)->toContain("alternar('Automotores')")
        ->toContain("x-show=\"abierta === 'Automotores'\"")
        ->toContain('Taller mecánico')
        ->toContain('Lubricentro')
        ->toContain('Chapa y pintura');
});

test('en el filtro los checkboxes no tienen name (el formulario arma sus propios campos rubro[])', function () {
    $html = $this->get(route('dashboard'))->getContent();

    expect($html)->not->toContain('name="rubros[]"')
        ->and($html)->toContain('name="rubro[]"'); // el campo oculto que arma el formulario
});

test('el filtro marca desde el servidor los rubros elegidos en la URL', function () {
    $html = $this->get(route('dashboard', ['rubro' => ['Gomeria', 'Cafe']]))->getContent();

    expect(checkboxDeRubro($html, 'Gomeria'))->toContain('checked')
        ->and(checkboxDeRubro($html, 'Cafe'))->toContain('checked')
        ->and(checkboxDeRubro($html, 'Lubricentro'))->not->toContain('checked');
});

test('el filtro encuentra un comercio por CUALQUIERA de sus rubros, no solo el principal', function () {
    $dueno = rubrosDueno();
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Restaurante', 'Pizzeria', 'Delivery']], $dueno);
    rubrosComercio(['nombre' => 'Gomería Sur', 'rubros' => ['Gomeria']], $dueno);

    foreach (['Restaurante', 'Pizzeria', 'Delivery'] as $rubro) {
        $this->get(route('dashboard', ['rubro' => [$rubro]]))
            ->assertSee('Lo de Ana')
            ->assertDontSee('Gomería Sur');
    }

    $this->get(route('dashboard', ['rubro' => ['Gomeria']]))
        ->assertSee('Gomería Sur')
        ->assertDontSee('Lo de Ana');
});

test('elegir varios rubros muestra los comercios de cualquiera de ellos', function () {
    $dueno = rubrosDueno();
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Pizzeria', 'Delivery']], $dueno);
    rubrosComercio(['nombre' => 'Gomería Sur', 'rubros' => ['Gomeria']], $dueno);
    rubrosComercio(['nombre' => 'Farmacia Central', 'rubros' => ['Farmacia']], $dueno);

    $this->get(route('dashboard', ['rubro' => ['Delivery', 'Gomeria']]))
        ->assertSee('Lo de Ana')
        ->assertSee('Gomería Sur')
        ->assertDontSee('Farmacia Central');
});

test('el filtro no distingue mayúsculas ni tildes en la URL', function () {
    rubrosComercio(['nombre' => 'Óptica Visión', 'rubros' => ['Optica']]);
    rubrosComercio(['nombre' => 'Gomería Sur', 'rubros' => ['Gomeria']]);

    $this->get(route('dashboard', ['rubro' => 'optica']))
        ->assertSee('Óptica Visión')
        ->assertDontSee('Gomería Sur');
});

test('un comercio deshabilitado no aparece en el filtro aunque tenga el rubro', function () {
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Pizzeria']])->forceFill(['habilitado' => false])->save();

    $this->get(route('dashboard', ['rubro' => ['Pizzeria']]))
        ->assertOk()
        ->assertDontSee('Lo de Ana');
});

test('un rubro inventado en la URL no rompe nada y no muestra comercios', function () {
    rubrosComercio(['nombre' => 'Lo de Ana']);

    $this->get(route('dashboard', ['rubro' => ["<script>alert(1)</script>", "'; DROP TABLE comercios;--"]]))
        ->assertOk()
        ->assertDontSee('Lo de Ana')
        ->assertDontSee('<script>alert(1)</script>', false);

    expect(Comercio::count())->toBe(1);
});

test('las tarjetas muestran los dos primeros rubros y cuántos más tiene el comercio', function () {
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Cafe', 'BarPub', 'Pasteleria', 'Heladeria']]);

    $this->get(route('dashboard'))
        ->assertSee('Cafetería')
        ->assertSee('Bar / Pub')
        ->assertSee('+2')
        ->assertSee('title="Pastelería, Heladería"', false);
});

test('el volver desde el perfil conserva todos los rubros elegidos en el filtro', function () {
    $comercio = rubrosComercio(['rubros' => ['Gomeria', 'Lubricentro']]);
    $previa = route('dashboard', ['rubro' => ['Gomeria', 'Lubricentro']]);

    $this->from($previa)->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee(e(route('dashboard', ['rubro' => ['Gomeria', 'Lubricentro']])), false);
});

// ---------------------------------------------------------------------------
// Búsqueda por texto: también encuentra por el nombre del rubro
// ---------------------------------------------------------------------------

test('buscar por texto encuentra comercios por el nombre de cualquiera de sus rubros', function () {
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Restaurante', 'Pizzeria']]);
    rubrosComercio(['nombre' => 'Gomería Sur', 'rubros' => ['Gomeria']]);

    $this->get(route('dashboard', ['search' => 'pizz']))
        ->assertSee('Lo de Ana')
        ->assertDontSee('Gomería Sur');

    $this->get(route('dashboard', ['search' => 'Pizzería']))
        ->assertSee('Lo de Ana')
        ->assertDontSee('Gomería Sur');
});

test('buscar por texto y filtrar por rubro se combinan', function () {
    $dueno = rubrosDueno();
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Pizzeria']], $dueno);
    rubrosComercio(['nombre' => 'Lo de Beto', 'rubros' => ['Heladeria']], $dueno);

    $this->get(route('dashboard', ['search' => 'lo de', 'rubro' => ['Pizzeria']]))
        ->assertSee('Lo de Ana')
        ->assertDontSee('Lo de Beto');
});

test('las sugerencias también encuentran por el nombre de los rubros y muestran un resumen', function () {
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Cafe', 'BarPub', 'Pasteleria']]);

    $respuesta = $this->getJson(route('comercios.sugerencias', ['q' => 'paste']))->assertOk();

    expect($respuesta->json('sugerencias'))->toHaveCount(1)
        ->and($respuesta->json('sugerencias.0.nombre'))->toBe('Lo de Ana')
        ->and($respuesta->json('sugerencias.0.rubro'))->toBe('Cafetería, Bar / Pub +1');
});

test('el listado del administrador también busca por el nombre de los rubros', function () {
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Restaurante', 'Pizzeria']]);
    rubrosComercio(['nombre' => 'Gomería Sur', 'rubros' => ['Gomeria']]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.comercios', ['q' => 'pizzeria']))
        ->assertOk()
        ->assertSee('Lo de Ana')
        ->assertDontSee('Gomería Sur');
});

// ---------------------------------------------------------------------------
// Perfil público y paneles: se ven todos los rubros
// ---------------------------------------------------------------------------

test('el perfil público muestra todos los rubros del comercio', function () {
    $comercio = rubrosComercio(['rubros' => ['Cafe', 'BarPub', 'Pasteleria', 'Heladeria']]);

    $this->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Cafetería')
        ->assertSee('Bar / Pub')
        ->assertSee('Pastelería')
        ->assertSee('Heladería');
});

test('el panel del comerciante muestra el resumen de rubros de cada comercio', function () {
    $dueno = rubrosDueno();
    rubrosComercio(['nombre' => 'Lo de Ana', 'rubros' => ['Cafe', 'BarPub', 'Pasteleria']], $dueno);

    $this->actingAs($dueno)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Cafetería, Bar / Pub +1');
});

// ---------------------------------------------------------------------------
// Migración: los comercios que ya existían pasan a la lista de rubros
// ---------------------------------------------------------------------------

test('la migración convierte los rubros viejos a la lista nueva', function () {
    $migracion = require database_path('migrations/2026_10_06_000000_add_rubros_to_comercios_table.php');
    $migracion->down(); // como estaba antes: sin la columna "rubros"

    $dueno = rubrosDueno();
    $viejos = [
        'Cafe', 'Panaderia', 'Supermercado', 'Carniceria', 'Peluqueria', 'Mecanico', 'Lavanderia', 'Libreria',
        'Hogar', 'Mascotas', 'Tecnologia', 'ServiciosProfesionales', 'Hoteleria',
        'Restaurante', 'Farmacia', 'Otro', 'Panadería artesanal', '',
    ];

    foreach ($viejos as $rubro) {
        DB::table('comercios')->insert([
            'user_id' => $dueno->id, 'nombre' => "Viejo {$rubro}", 'direccion' => 'Calle 1', 'rubro' => $rubro,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $migracion->up();

    $rubrosDe = fn (string $rubro) => json_decode(DB::table('comercios')->where('nombre', "Viejo {$rubro}")->value('rubros'), true);

    expect($rubrosDe('Cafe'))->toBe(['Cafe', 'BarPub'])
        ->and($rubrosDe('Panaderia'))->toBe(['Panaderia', 'Pasteleria'])
        ->and($rubrosDe('Supermercado'))->toBe(['Supermercado', 'Almacen'])
        ->and($rubrosDe('Carniceria'))->toBe(['Carniceria', 'Pescaderia'])
        ->and($rubrosDe('Peluqueria'))->toBe(['Peluqueria', 'Barberia'])
        ->and($rubrosDe('Mecanico'))->toBe(['Mecanico', 'RepuestosAuto'])
        ->and($rubrosDe('Lavanderia'))->toBe(['Lavanderia', 'Tintoreria'])
        ->and($rubrosDe('Libreria'))->toBe(['Libreria', 'Artistica'])
        ->and($rubrosDe('Hogar'))->toBe(['Hogar', 'Decoracion', 'Muebles'])
        ->and($rubrosDe('Mascotas'))->toBe(['Veterinaria', 'PetShop'])
        ->and($rubrosDe('Tecnologia'))->toBe(['Computacion', 'VentaTecnologia'])
        ->and($rubrosDe('ServiciosProfesionales'))->toBe(['Abogado', 'Contador'])
        ->and($rubrosDe('Hoteleria'))->toBe(['Hotel'])
        // los que ya existen igual en el catálogo nuevo no cambian
        ->and($rubrosDe('Restaurante'))->toBe(['Restaurante'])
        ->and($rubrosDe('Farmacia'))->toBe(['Farmacia'])
        ->and($rubrosDe('Otro'))->toBe(['Otro'])
        // un rubro escrito a mano se conserva tal cual
        ->and($rubrosDe('Panadería artesanal'))->toBe(['Panadería artesanal'])
        // sin rubro: lista vacía, sin romper
        ->and($rubrosDe(''))->toBe([]);

    // "rubro" queda con el principal (el primero de la lista nueva)
    expect(DB::table('comercios')->where('nombre', 'Viejo Mascotas')->value('rubro'))->toBe('Veterinaria')
        ->and(DB::table('comercios')->where('nombre', 'Viejo Cafe')->value('rubro'))->toBe('Cafe');

    // Todas las claves nuevas que genera la migración existen en el catálogo.
    $generadas = collect($viejos)->reject(fn ($r) => $r === '' || $r === 'Panadería artesanal')
        ->flatMap(fn ($r) => $rubrosDe($r))->unique();

    expect($generadas->diff(App\Support\Rubros::claves())->all())->toBe([]);
});

test('después de migrar, los comercios convertidos se encuentran en el filtro nuevo', function () {
    $migracion = require database_path('migrations/2026_10_06_000000_add_rubros_to_comercios_table.php');
    $migracion->down();

    DB::table('comercios')->insert([
        'user_id' => rubrosDueno()->id, 'nombre' => 'Café Viejo', 'direccion' => 'Calle 1', 'rubro' => 'Cafe',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $migracion->up();

    $this->get(route('dashboard', ['rubro' => ['BarPub']]))->assertSee('Café Viejo');
    $this->get(route('dashboard', ['rubro' => ['Cafe']]))->assertSee('Café Viejo');
});
