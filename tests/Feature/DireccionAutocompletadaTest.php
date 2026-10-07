<?php

use App\Models\Comercio;
use App\Models\Localidad;
use App\Models\User;
use Database\Seeders\LocalidadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(LocalidadSeeder::class));

function duenoConComercio(array $extra = []): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);

    return Comercio::create(array_merge([
        'user_id' => $dueno->id,
        'nombre' => 'Panadería Don Julio',
        'direccion' => 'Av. Libertad 125',
        'rubro' => 'Panaderia',
        'localidad_id' => Localidad::where('nombre', 'Leandro N. Alem')->value('id'),
        'latitud' => -27.596361,
        'longitud' => -55.321893,
    ], $extra));
}

test('el alta trae un solo selector de ubicación: buscador, mapa, dirección y localidad', function () {
    $this->actingAs(User::factory()->create(['role' => 'comerciante']))
        ->get(route('comercio.create'))
        ->assertOk()
        ->assertSee('Ubicación del comercio')
        ->assertSee('Elegir ubicación')
        ->assertSee('Todavía no elegiste la ubicación de tu comercio.')
        ->assertSee('Elegí la ubicación de tu comercio')
        ->assertSee('Sugerencias de dirección')
        ->assertSee('data-confirmar-ubicacion', false)
        ->assertSee('¿Es acá tu comercio?')
        ->assertSee('Confirmar ubicación')
        // los cuatro campos viajan ocultos con el mismo nombre de siempre
        ->assertSee('name="direccion"', false)
        ->assertSee('name="localidad_id"', false)
        ->assertSee('name="latitud"', false)
        ->assertSee('name="longitud"', false)
        // ya no hay un campo de dirección ni un desplegable de localidad sueltos
        ->assertDontSee('id="direccion"', false)
        ->assertDontSee('id="localidad_id"', false);
});

test('la ventana de ubicación no repite la dirección: se escribe en el buscador y se ubica con un botón', function () {
    $this->actingAs(User::factory()->create(['role' => 'comerciante']))
        ->get(route('comercio.create'))
        ->assertOk()
        // el botón junto al buscador
        ->assertSee('Ubicar en el mapa')
        ->assertSee('x-on:click="ubicar()"', false)
        // el campo "Dirección (calle y número)" de la ventana ya no existe
        ->assertDontSee('Dirección (calle y número)')
        ->assertDontSee('id="ubicacion-direccion"', false)
        // la localidad sigue pudiéndose elegir a mano y avisa cuando se completó sola
        ->assertSee('id="ubicacion-localidad"', false)
        ->assertSee('x-on:change="localidadManual()"', false)
        ->assertSee('textoLocalidad', false);
});

test('las consultas a Photon usan la forma documentada y no mandan "lang" (un idioma que el servidor no tenga puede dar error 400)', function () {
    $js = file_get_contents(resource_path('js/mapa-comercio.js'));

    expect($js)->toContain('${PHOTON}/api?${params}')
        ->and($js)->toContain('${PHOTON}/reverse?${params}')
        ->and($js)->not->toContain('/api/?')
        ->and($js)->not->toMatch('/lang\\s*:/');
});

test('el buscador de dirección no está atado a Leandro N. Alem', function () {
    $js = file_get_contents(resource_path('js/mapa-comercio.js'));

    expect($js)->not->toContain('CENTRO_ALEM')
        ->and($js)->toContain('CENTRO_MISIONES');
});

test('la edición muestra la dirección, la localidad con su código postal y el punto guardado', function () {
    $comercio = duenoConComercio();

    $this->actingAs($comercio->user)
        ->get(route('comercio.edit', $comercio))
        ->assertOk()
        ->assertSee('value="Av. Libertad 125"', false)
        ->assertSee('Leandro N. Alem (3315)')
        ->assertSee('Cambiar ubicación')
        ->assertSee('value="-27.59', false);
});

test('el formulario ofrece todas las localidades del catálogo dentro de la ventana', function () {
    $this->actingAs(User::factory()->create(['role' => 'comerciante']))
        ->get(route('comercio.create'))
        ->assertOk()
        ->assertSee('Oberá (3360)')
        ->assertSee('Leandro N. Alem (3315)');
});

test('la dirección y la localidad siguen siendo obligatorias y se guardan como siempre', function () {
    $comercio = duenoConComercio();
    $alem = Localidad::where('nombre', 'Leandro N. Alem')->value('id');
    $datos = ['nombre' => 'Café', 'rubros' => ['Cafe'], 'localidad_id' => $alem];

    $this->actingAs($comercio->user)
        ->patch(route('comercio.update', $comercio), $datos + ['direccion' => ''])
        ->assertSessionHasErrors('direccion');

    $this->actingAs($comercio->user)
        ->patch(route('comercio.update', $comercio), $datos + ['direccion' => 'Av. San Martín 300', 'latitud' => -27.6, 'longitud' => -55.3]);

    expect($comercio->fresh()->direccion)->toBe('Av. San Martín 300')
        ->and((float) $comercio->fresh()->latitud)->toBe(-27.6);
});
