<?php

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function comercioDelDashboard(string $nombre, string $rubro): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);

    return Comercio::create([
        'user_id' => $dueno->id,
        'nombre' => $nombre,
        'direccion' => 'Calle Falsa 123',
        'rubro' => $rubro,
    ]);
}

test('un visitante sin iniciar sesión puede ver el dashboard con todos los comercios', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');
    comercioDelDashboard('Farmacia Central', 'Farmacia');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Encuentra lo que necesitas')
        ->assertSee('autocompletadoComercios(', false)
        ->assertSee('Filtrar por rubro')
        ->assertSee('Café del Centro')
        ->assertSee('Farmacia Central');
});

test('el dashboard filtra por un solo rubro', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');
    comercioDelDashboard('Farmacia Central', 'Farmacia');

    $this->get(route('dashboard', ['rubro' => ['Cafe']]))
        ->assertOk()
        ->assertSee('Café del Centro')
        ->assertDontSee('Farmacia Central');
});

test('el dashboard filtra por varios rubros a la vez', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');
    comercioDelDashboard('Farmacia Central', 'Farmacia');
    comercioDelDashboard('Ferretería Don Luis', 'Ferreteria');

    $this->get(route('dashboard', ['rubro' => ['Cafe', 'Farmacia']]))
        ->assertOk()
        ->assertSee('Café del Centro')
        ->assertSee('Farmacia Central')
        ->assertDontSee('Ferretería Don Luis');
});

test('los enlaces viejos con un solo rubro (?rubro=Cafe) siguen funcionando', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');
    comercioDelDashboard('Farmacia Central', 'Farmacia');

    $this->get(route('dashboard', ['rubro' => 'Cafe']))
        ->assertOk()
        ->assertSee('Café del Centro')
        ->assertDontSee('Farmacia Central');
});

test('la búsqueda por texto y el filtro por rubro se combinan', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');
    comercioDelDashboard('Café de la Plaza', 'Cafe');
    comercioDelDashboard('Farmacia Central', 'Farmacia');

    $this->get(route('dashboard', ['search' => 'plaza', 'rubro' => ['Cafe', 'Farmacia']]))
        ->assertOk()
        ->assertSee('Café de la Plaza')
        ->assertDontSee('Café del Centro')
        ->assertDontSee('Farmacia Central');
});

test('si nada coincide se muestra el mensaje de sin resultados', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');

    $this->get(route('dashboard', ['rubro' => ['Hoteleria']]))
        ->assertOk()
        ->assertSee('Sin resultados')
        ->assertDontSee('Café del Centro');
});

test('un rubro raro o un texto con HTML no rompe el dashboard', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');

    $this->get(route('dashboard', ['rubro' => 'x', 'search' => ['a']]))->assertOk();
    $this->get(route('dashboard', ['search' => '<script>alert(1)</script>']))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('la paginación del dashboard conserva los rubros elegidos', function () {
    foreach (range(1, 13) as $n) {
        comercioDelDashboard("Café número {$n}", 'Cafe');
    }

    $this->get(route('dashboard', ['rubro' => ['Cafe', 'Farmacia']]))
        ->assertOk()
        ->assertSee('rubro%5B0%5D=Cafe', false)
        ->assertSee('rubro%5B1%5D=Farmacia', false);
});

test('el comerciante sigue viendo su panel en /dashboard', function () {
    $comerciante = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($comerciante)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Panel de Comerciante')
        ->assertDontSee('Filtrar por rubro');
});

test('el cliente logueado ve el mismo dashboard con saludo', function () {
    $cliente = User::factory()->create(['role' => 'usuario', 'name' => 'fabi']);
    comercioDelDashboard('Café del Centro', 'Cafe');

    $this->actingAs($cliente)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('¡Hola, fabi!')
        ->assertSee('Café del Centro')
        ->assertSee('Filtrar por rubro');
});

test('la portada manda al visitante al dashboard (buscador, rubros y ver todos)', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('action="' . e(route('dashboard')) . '"', false)
        ->assertSee('href="' . e(route('dashboard')) . '">Ver todos los comercios', false)
        ->assertSee(e(route('dashboard', ['rubro' => 'Restaurante'])), false);
});

test('el comerciante logueado sigue usando /comercios desde la portada', function () {
    $comerciante = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($comerciante)
        ->get('/')
        ->assertOk()
        ->assertSee('action="' . e(route('comercios.index')) . '"', false);
});

test('el listado /comercios acepta varios rubros', function () {
    comercioDelDashboard('Café del Centro', 'Cafe');
    comercioDelDashboard('Farmacia Central', 'Farmacia');
    comercioDelDashboard('Ferretería Don Luis', 'Ferreteria');

    $this->get(route('comercios.index', ['rubro' => ['Cafe', 'Farmacia']]))
        ->assertOk()
        ->assertSee('Café del Centro')
        ->assertSee('Farmacia Central')
        ->assertDontSee('Ferretería Don Luis');
});

test('desde el perfil, volver lleva de nuevo al dashboard con la misma búsqueda', function () {
    $comercio = comercioDelDashboard('Café del Centro', 'Cafe');
    $previa = route('dashboard', ['rubro' => ['Cafe', 'Farmacia'], 'page' => '2']);

    $this->actingAs(User::factory()->create(['role' => 'usuario']))
        ->withHeader('referer', $previa)
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('href="' . e($previa) . '" class="perfil-volver"', false);
});
