<?php

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('el alta de comercio trae la dirección con sugerencias y el mapa escucha la dirección elegida', function () {
    $comerciante = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($comerciante)
        ->get(route('comercio.create'))
        ->assertOk()
        ->assertSee('autocompletadoDireccion()', false)
        ->assertSee('id="direccion"', false)
        ->assertSee('Sugerencias de dirección')
        ->assertSee('@direccion-elegida.window="desdeDireccion($event.detail)"', false)
        ->assertSee('data-confirmar-ubicacion', false)
        ->assertSee('¿Es acá tu comercio?');
});

test('la edición conserva la dirección guardada y también tiene las sugerencias y la confirmación', function () {
    $dueno = User::factory()->create(['role' => 'comerciante']);
    $comercio = Comercio::create([
        'user_id' => $dueno->id,
        'nombre' => 'Panadería Don Julio',
        'direccion' => 'Av. Libertad 125',
        'rubro' => 'Panaderia',
    ]);

    $this->actingAs($dueno)
        ->get(route('comercio.edit', $comercio))
        ->assertOk()
        ->assertSee('value="Av. Libertad 125"', false)
        ->assertSee('autocompletadoDireccion()', false)
        ->assertSee('data-confirmar-ubicacion', false);
});

test('la dirección sigue siendo obligatoria y se guarda como siempre', function () {
    $dueno = User::factory()->create(['role' => 'comerciante']);
    $comercio = Comercio::create([
        'user_id' => $dueno->id,
        'nombre' => 'Café',
        'direccion' => 'Calle 1',
        'rubro' => 'Cafe',
    ]);

    $this->actingAs($dueno)
        ->patch(route('comercio.update', $comercio), ['nombre' => 'Café', 'direccion' => '', 'rubro' => 'Cafe'])
        ->assertSessionHasErrors('direccion');

    $this->actingAs($dueno)
        ->patch(route('comercio.update', $comercio), ['nombre' => 'Café', 'direccion' => 'Av. Libertad 125, Leandro N. Alem', 'rubro' => 'Cafe']);
    expect($comercio->fresh()->direccion)->toBe('Av. Libertad 125, Leandro N. Alem');
});
