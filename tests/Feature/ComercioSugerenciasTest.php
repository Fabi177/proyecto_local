<?php

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearComercioParaSugerencias(array $extra = []): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);

    return Comercio::create(array_merge([
        'user_id' => $dueno->id,
        'nombre' => 'Panadería Don Julio',
        'direccion' => 'Av. Siempreviva 742',
        'rubro' => 'Panadería',
    ], $extra));
}

function pedirSugerencias(string $q)
{
    return test()->getJson(route('comercios.sugerencias', ['q' => $q]));
}

test('cualquier visitante recibe sugerencias por nombre, sin iniciar sesión', function () {
    $comercio = crearComercioParaSugerencias();

    pedirSugerencias('don julio')
        ->assertOk()
        ->assertJsonCount(1, 'sugerencias')
        ->assertJsonPath('sugerencias.0.id', $comercio->id)
        ->assertJsonPath('sugerencias.0.nombre', 'Panadería Don Julio')
        ->assertJsonPath('sugerencias.0.url', route('comercio.show', ['comercio' => $comercio->id]));
});

test('también busca por rubro y por descripción', function () {
    crearComercioParaSugerencias(['nombre' => 'Lo de Ana', 'rubro' => 'Ferretería', 'descripcion' => 'Herramientas y pinturas']);

    pedirSugerencias('ferret')->assertJsonCount(1, 'sugerencias');
    pedirSugerencias('pinturas')->assertJsonCount(1, 'sugerencias');
});

test('no distingue mayúsculas de minúsculas', function () {
    crearComercioParaSugerencias();

    pedirSugerencias('DON JULIO')->assertJsonCount(1, 'sugerencias');
});

test('con menos de 2 letras, vacío o sin texto no devuelve sugerencias', function () {
    crearComercioParaSugerencias();

    pedirSugerencias('p')->assertOk()->assertJsonCount(0, 'sugerencias');
    pedirSugerencias('   ')->assertOk()->assertJsonCount(0, 'sugerencias');
    test()->getJson(route('comercios.sugerencias'))->assertOk()->assertJsonCount(0, 'sugerencias');
});

test('devuelve como máximo 8 sugerencias', function () {
    foreach (range(1, 10) as $numero) {
        crearComercioParaSugerencias(['nombre' => "Kiosco {$numero}"]);
    }

    pedirSugerencias('kiosco')->assertJsonCount(8, 'sugerencias');
});

test('los que empiezan con lo escrito salen primero', function () {
    crearComercioParaSugerencias(['nombre' => 'Casa del Pan']);
    crearComercioParaSugerencias(['nombre' => 'Pan Dorado']);

    pedirSugerencias('pan')
        ->assertJsonPath('sugerencias.0.nombre', 'Pan Dorado')
        ->assertJsonPath('sugerencias.1.nombre', 'Casa del Pan');
});

test('los comodines % y _ se toman como texto común', function () {
    crearComercioParaSugerencias();

    pedirSugerencias('%%')->assertOk()->assertJsonCount(0, 'sugerencias');
    pedirSugerencias('__')->assertOk()->assertJsonCount(0, 'sugerencias');
});

test('solo devuelve los datos públicos mínimos de cada comercio', function () {
    crearComercioParaSugerencias(['telefono' => '123456', 'descripcion' => 'Pan casero']);

    $sugerencia = pedirSugerencias('julio')->json('sugerencias.0');

    expect(array_keys($sugerencia))->toEqual(['id', 'nombre', 'rubro', 'direccion', 'logo', 'url']);
});

test('un texto con HTML no rompe nada', function () {
    crearComercioParaSugerencias();

    pedirSugerencias('<script>alert(1)</script>')->assertOk()->assertJsonCount(0, 'sugerencias');
});

test('la página de resultados tiene el autocompletado', function () {
    $this->get(route('comercios.index'))
        ->assertOk()
        ->assertSee('autocompletadoComercios(', false)
        ->assertSee('x-bind="entrada"', false);
});

test('el panel del cliente tiene el autocompletado', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);

    $this->actingAs($cliente)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('autocompletadoComercios(', false);
});

test('el perfil público tiene el autocompletado para el cliente pero no para el comerciante', function () {
    $comercio = crearComercioParaSugerencias();
    $cliente = User::factory()->create(['role' => 'usuario']);
    $comerciante = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($cliente)
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('autocompletadoComercios(', false);

    $this->actingAs($comerciante)
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertDontSee('autocompletadoComercios(', false);
});
