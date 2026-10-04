<?php

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function comercioParaDeshabilitar(bool $habilitado = true, string $nombre = 'Café del Centro'): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);

    $comercio = Comercio::create([
        'user_id' => $dueno->id,
        'nombre' => $nombre,
        'direccion' => 'Calle 1',
        'rubro' => 'Cafe',
    ]);
    $comercio->forceFill(['habilitado' => $habilitado])->save();

    return $comercio->fresh();
}

test('el dueño deshabilita y vuelve a habilitar su comercio', function () {
    $comercio = comercioParaDeshabilitar();

    $this->actingAs($comercio->user)
        ->patch(route('comercio.estado', $comercio))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status');
    expect($comercio->fresh()->habilitado)->toBeFalse();

    $this->actingAs($comercio->user)->patch(route('comercio.estado', $comercio));
    expect($comercio->fresh()->habilitado)->toBeTrue();
});

test('un comercio deshabilitado no aparece en el listado ni en las sugerencias', function () {
    comercioParaDeshabilitar(false, 'Café Oculto');
    comercioParaDeshabilitar(true, 'Café Visible');

    $this->get(route('dashboard'))->assertOk()->assertSee('Café Visible')->assertDontSee('Café Oculto');
    $this->getJson(route('comercios.sugerencias', ['q' => 'café']))->assertOk()->assertJsonCount(1, 'sugerencias');
});

test('el perfil de un comercio deshabilitado solo lo ven su dueño y el admin', function () {
    $comercio = comercioParaDeshabilitar(false);
    $cliente = User::factory()->create(['role' => 'usuario']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->get(route('comercio.show', $comercio))->assertNotFound();
    $this->actingAs($cliente)->get(route('comercio.show', $comercio))->assertNotFound();
    $this->actingAs($comercio->user)->get(route('comercio.show', $comercio))->assertOk()->assertSee('Comercio deshabilitado');
    $this->actingAs($admin)->get(route('comercio.show', $comercio))->assertOk()->assertSee('Comercio deshabilitado');
});

test('un comercio habilitado no muestra el aviso de deshabilitado', function () {
    $this->get(route('comercio.show', comercioParaDeshabilitar()))
        ->assertOk()
        ->assertDontSee('Comercio deshabilitado');
});

test('no se pueden dejar reseñas en un comercio deshabilitado', function () {
    $comercio = comercioParaDeshabilitar(false);

    $this->actingAs(User::factory()->create(['role' => 'usuario']))
        ->post(route('resenas.store', $comercio), ['calificacion' => 5])
        ->assertNotFound();
});

test('la edición ofrece Deshabilitar o Habilitar y ya no ofrece eliminar', function () {
    $activo = comercioParaDeshabilitar();
    $this->actingAs($activo->user)->get(route('comercio.edit', $activo))
        ->assertOk()
        ->assertSee('Deshabilitar comercio')
        ->assertDontSee('Eliminar este Comercio Permanentemente')
        ->assertDontSee('Zona de Peligro');

    $oculto = comercioParaDeshabilitar(false);
    $this->actingAs($oculto->user)->get(route('comercio.edit', $oculto))
        ->assertOk()
        ->assertSee('Habilitar comercio')
        ->assertDontSee('Deshabilitar comercio');
});

test('el comerciante ve la etiqueta Deshabilitado en su panel', function () {
    $comercio = comercioParaDeshabilitar(false);

    $this->actingAs($comercio->user)->get(route('dashboard'))->assertOk()->assertSee('Deshabilitado');
});

test('otro comerciante no puede cambiar el estado y el visitante va al login', function () {
    $comercio = comercioParaDeshabilitar();

    $this->actingAs(User::factory()->create(['role' => 'comerciante']))
        ->patch(route('comercio.estado', $comercio))->assertNotFound();
    expect($comercio->fresh()->habilitado)->toBeTrue();

    auth()->logout();
    $this->patch(route('comercio.estado', $comercio))->assertRedirect(route('login'));
});

test('el admin puede deshabilitar y vuelve a /admin/comercios', function () {
    $comercio = comercioParaDeshabilitar();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->patch(route('comercio.estado', $comercio))
        ->assertRedirect(route('admin.comercios'));
    expect($comercio->fresh()->habilitado)->toBeFalse();
});
