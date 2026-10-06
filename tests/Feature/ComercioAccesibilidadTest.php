<?php

use App\Models\Comercio;
use App\Models\Localidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function nuevoComercio(User $dueno, array $extra = []): Comercio
{
    return Comercio::create(array_merge([
        'user_id' => $dueno->id,
        'nombre' => 'Panadería Don Julio',
        'direccion' => 'Av. Siempreviva 742',
        'rubro' => 'Panadería',
    ], $extra));
}

function comerciante(): User
{
    return User::factory()->create(['role' => 'comerciante']);
}

test('la tabla comercios tiene la columna rampa_acceso', function () {
    expect(Schema::hasColumn('comercios', 'rampa_acceso'))->toBeTrue();
});

test('el perfil muestra las etiquetas afirmativas cuando el comercio tiene todo', function () {
    $comercio = nuevoComercio(comerciante(), [
        'ingreso_discapacitados' => true,
        'rampa_acceso' => true,
        'estacionamiento' => true,
    ]);

    $this->actingAs(User::factory()->create())->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Apto movilidad reducida')
        ->assertSee('Rampa de acceso')
        ->assertSee('Estacionamiento exclusivo')
        ->assertDontSee('Sin acceso adaptado')
        ->assertDontSee('Entrada con escalones')
        ->assertDontSee('Sin estacionamiento');
});

test('el perfil muestra las etiquetas negativas cuando el comercio no tiene nada', function () {
    $comercio = nuevoComercio(comerciante());

    $this->actingAs(User::factory()->create())->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Sin acceso adaptado')
        ->assertSee('Entrada con escalones')
        ->assertSee('Sin estacionamiento')
        ->assertDontSee('Apto movilidad reducida')
        ->assertDontSee('Rampa de acceso')
        ->assertDontSee('Estacionamiento exclusivo');
});

test('el perfil combina etiquetas en el caso mixto', function () {
    $comercio = nuevoComercio(comerciante(), [
        'ingreso_discapacitados' => true,
        'rampa_acceso' => false,
        'estacionamiento' => true,
    ]);

    $this->actingAs(User::factory()->create())->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Apto movilidad reducida')
        ->assertSee('Entrada con escalones')
        ->assertSee('Estacionamiento exclusivo');
});

test('el perfil ya no muestra los textos viejos', function () {
    $comercio = nuevoComercio(comerciante());

    $this->actingAs(User::factory()->create())->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertDontSee('Sin Acceso Discapacitados')
        ->assertDontSee('Acceso Discapacitados')
        ->assertDontSee('Estacionamiento Propio');
});

test('el perfil muestra un ítem por cada entrada de la lista', function () {
    $comercio = nuevoComercio(comerciante());

    $html = $this->actingAs(User::factory()->create())->get(route('comercio.show', $comercio))->getContent();

    expect(substr_count($html, '<li class="inline-flex items-center gap-1.5'))
        ->toBe(count(Comercio::ACCESIBILIDAD));
});

test('los formularios de alta y edición muestran las tres preguntas', function () {
    $dueno = comerciante();
    $comercio = nuevoComercio($dueno);

    foreach ([route('comercio.create'), route('comercio.edit', $comercio)] as $url) {
        $respuesta = $this->actingAs($dueno)->get($url)->assertOk();
        foreach (Comercio::ACCESIBILIDAD as $campo => $item) {
            $respuesta->assertSee('name="' . $campo . '"', false)->assertSee($item['pregunta']);
        }
    }
});

test('el formulario de edición viene tildado según lo guardado', function () {
    $dueno = comerciante();
    $comercio = nuevoComercio($dueno, ['rampa_acceso' => true]);

    $html = $this->actingAs($dueno)->get(route('comercio.edit', $comercio))->getContent();

    expect($html)->toMatch('/id="rampa_acceso"[^>]*checked/s')
        ->and($html)->not->toMatch('/id="estacionamiento"[^>]*checked/s');
});

test('al crear un comercio se guardan los tres checkboxes', function () {
    $dueno = comerciante();

    $this->actingAs($dueno)->post(route('comercio.store'), [
        'nombre' => 'Ferretería Sur',
        'direccion' => 'Calle 1 123',
        'localidad_id' => Localidad::where('nombre', 'Leandro N. Alem')->value('id'),
        'rubro' => 'Ferretería',
        'ingreso_discapacitados' => '1',
        'rampa_acceso' => '1',
    ])->assertSessionHasNoErrors();

    $comercio = Comercio::where('nombre', 'Ferretería Sur')->firstOrFail();
    expect($comercio->ingreso_discapacitados)->toBeTrue()
        ->and($comercio->rampa_acceso)->toBeTrue()
        ->and($comercio->estacionamiento)->toBeFalse();
});

test('al editar se pueden destildar y tildar los checkboxes', function () {
    $dueno = comerciante();
    $comercio = nuevoComercio($dueno, [
        'ingreso_discapacitados' => true,
        'rampa_acceso' => true,
        'estacionamiento' => false,
    ]);

    $this->actingAs($dueno)->patch(route('comercio.update', $comercio), [
        'nombre' => $comercio->nombre,
        'direccion' => $comercio->direccion,
        'rubro' => $comercio->rubro,
        'estacionamiento' => '1',
    ])->assertSessionHasNoErrors();

    $comercio->refresh();
    expect($comercio->ingreso_discapacitados)->toBeFalse()
        ->and($comercio->rampa_acceso)->toBeFalse()
        ->and($comercio->estacionamiento)->toBeTrue();
});
