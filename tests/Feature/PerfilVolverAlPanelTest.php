<?php

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function comercioParaVolver(): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);

    return Comercio::create([
        'user_id' => $dueno->id,
        'nombre' => 'Panadería Don Julio',
        'direccion' => 'Av. Siempreviva 742',
        'rubro' => 'Panaderia',
    ]);
}

test('el comerciante ve "Volver a mi panel" en el perfil público y lleva a su dashboard', function () {
    $comercio = comercioParaVolver();

    $this->actingAs($comercio->user)
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Volver a mi panel')
        ->assertSee('href="' . e(route('dashboard')) . '" class="perfil-volver"', false)
        ->assertDontSee('Volver a los resultados')
        ->assertDontSee('role="search"', false);
});

test('el administrador ve "Volver al panel de administración" y no el volver de los clientes', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('comercio.show', comercioParaVolver()))
        ->assertOk()
        ->assertSee('Volver al panel de administración')
        ->assertSee('href="' . e(route('admin.index')) . '" class="perfil-volver"', false)
        ->assertDontSee('Volver a los resultados')
        ->assertDontSee('role="search"', false);
});

test('el cliente y el visitante no ven el botón de volver al panel', function () {
    $comercio = comercioParaVolver();

    $this->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertDontSee('Volver a mi panel')
        ->assertDontSee('Volver al panel de administración');

    $this->actingAs(User::factory()->create(['role' => 'usuario']))
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Volver a los resultados')
        ->assertDontSee('Volver a mi panel')
        ->assertDontSee('Volver al panel de administración');
});
