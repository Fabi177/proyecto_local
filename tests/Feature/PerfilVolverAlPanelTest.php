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

test('el administrador ve "Volver al panel de administración" (listado de comercios) y no el volver de los clientes', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('comercio.show', comercioParaVolver()))
        ->assertOk()
        ->assertSee('Volver al panel de administración')
        ->assertSee('href="' . e(route('admin.comercios')) . '" class="perfil-volver"', false)
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

test('la edición del comercio tiene "Volver a mi panel" con el aviso de cambios sin guardar', function () {
    $comercio = comercioParaVolver();

    $this->actingAs($comercio->user)
        ->get(route('comercio.edit', $comercio))
        ->assertOk()
        ->assertSee('Volver a mi panel')
        ->assertSee('href="' . e(route('dashboard')) . '"', false)
        ->assertSee('data-aviso-cambios', false)
        ->assertSee('Tenés cambios sin guardar')
        ->assertSee('Guardar y volver')
        ->assertSee('Salir sin guardar')
        ->assertSee('Seguir editando');
});

test('la edición de un comercio ajeno sigue sin estar disponible', function () {
    $comercio = comercioParaVolver();
    $otro = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($otro)
        ->get(route('comercio.edit', $comercio))
        ->assertNotFound();
});

test('el admin que edita un comercio ajeno vuelve a /admin/comercios, no a /admin', function () {
    $comercio = comercioParaVolver();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('comercio.edit', $comercio))
        ->assertOk()
        ->assertSee('Volver al panel de administración')
        ->assertSee('href="' . e(route('admin.comercios')) . '"', false)
        ->assertDontSee('Volver a mi panel');
});

test('al guardar, el admin vuelve a /admin/comercios y el comerciante a su dashboard', function () {
    $comercio = comercioParaVolver();
    $datos = ['nombre' => 'Nuevo nombre', 'direccion' => 'Calle 1', 'rubro' => 'Cafe'];

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->patch(route('comercio.update', $comercio), $datos)
        ->assertRedirect(route('admin.comercios'));

    $this->actingAs($comercio->user)
        ->patch(route('comercio.update', $comercio), $datos)
        ->assertRedirect(route('dashboard'));
});

test('el aviso de cambios no compara fechas de archivos (daba falsos avisos al entrar y salir)', function () {
    $comercio = comercioParaVolver();

    $this->actingAs($comercio->user)
        ->get(route('comercio.edit', $comercio))
        ->assertOk()
        ->assertDontSee('lastModified', false);
});
