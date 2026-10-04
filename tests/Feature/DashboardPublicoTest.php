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

test('el comerciante y el admin no ven el buscador en la portada, solo "Mi Panel"', function () {
    foreach (['comerciante', 'admin'] as $rol) {
        $this->actingAs(User::factory()->create(['role' => $rol]))
            ->get('/')
            ->assertOk()
            ->assertSee('Mi Panel')
            ->assertDontSee('id="hero-q"', false)
            ->assertDontSee('Ver todos los comercios');
    }
});

test('el cliente logueado ve el buscador en la portada y va al dashboard', function () {
    $this->actingAs(User::factory()->create(['role' => 'usuario']))
        ->get('/')
        ->assertOk()
        ->assertSee('id="hero-q"', false)
        ->assertSee('action="' . e(route('dashboard')) . '"', false);
});

test('la dirección vieja /comercios redirige al dashboard conservando lo buscado', function () {
    $this->get('/comercios')->assertRedirect(route('dashboard'));

    $this->get('/comercios?search=pan&rubro[]=Cafe&rubro[]=Farmacia')
        ->assertRedirect(route('dashboard', ['search' => 'pan', 'rubro' => ['Cafe', 'Farmacia']]));
});

test('la dirección vieja /comercios no muestra el listado al comerciante: lo lleva a su panel', function () {
    $comerciante = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($comerciante)
        ->followingRedirects()
        ->get('/comercios')
        ->assertOk()
        ->assertSee('Panel de Comerciante')
        ->assertDontSee('Filtrar por rubro');
});

test('el buscador del perfil público envía al dashboard', function () {
    $comercio = comercioDelDashboard('Café del Centro', 'Cafe');

    $this->actingAs(User::factory()->create(['role' => 'usuario']))
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('action="' . e(route('dashboard')) . '" method="GET" role="search"', false);
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
