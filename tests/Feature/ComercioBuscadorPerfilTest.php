<?php

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function comercioDePrueba(): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);

    return Comercio::create([
        'user_id' => $dueno->id,
        'nombre' => 'Panadería Don Julio',
        'direccion' => 'Av. Siempreviva 742',
        'rubro' => 'Panadería',
    ]);
}

function verPerfilComo(User $usuario, Comercio $comercio, ?string $vieneDe = null)
{
    $prueba = test()->actingAs($usuario);
    if ($vieneDe) {
        $prueba = $prueba->withHeader('referer', $vieneDe);
    }

    return $prueba->get(route('comercio.show', $comercio));
}

test('el cliente ve el buscador y el botón volver en el perfil', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);

    verPerfilComo($cliente, comercioDePrueba())
        ->assertOk()
        ->assertSee('role="search"', false)
        ->assertSee('name="search"', false)
        ->assertSee('Volver a los resultados');
});

test('el buscador del perfil arranca vacío y con el texto de ayuda del panel', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);

    verPerfilComo($cliente, comercioDePrueba(), route('comercios.index', ['search' => 'pan']))
        ->assertSee('placeholder="Buscar Restaurantes, Ferreterías, Servicios..."', false)
        ->assertDontSee('name="search" value=', false);
});

test('el buscador del perfil envía la búsqueda al listado de comercios', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);

    verPerfilComo($cliente, comercioDePrueba())
        ->assertSee('action="' . e(route('comercios.index')) . '"', false);
});

test('sin página anterior, volver lleva al buscador sin filtros', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);

    verPerfilComo($cliente, comercioDePrueba())
        ->assertSee('href="' . e(route('comercios.index')) . '" class="perfil-volver"', false);
});

test('volver conserva la búsqueda y la página, pero el buscador queda vacío', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);
    $destino = route('comercios.index', ['search' => 'pan', 'page' => '2']);

    verPerfilComo($cliente, comercioDePrueba(), $destino)
        ->assertSee('href="' . e($destino) . '" class="perfil-volver"', false)
        ->assertDontSee('value="pan"', false);
});

test('volver conserva el rubro elegido, pero el buscador queda vacío', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);
    $destino = route('comercios.index', ['rubro' => 'Farmacia']);

    verPerfilComo($cliente, comercioDePrueba(), $destino)
        ->assertSee('href="' . e($destino) . '" class="perfil-volver"', false)
        ->assertDontSee('value="Farmacia"', false);
});

test('si viene de otro perfil, volver lleva al buscador sin filtros', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);
    $otro = comercioDePrueba();

    verPerfilComo($cliente, comercioDePrueba(), route('comercio.show', $otro) . '?search=pan')
        ->assertSee('href="' . e(route('comercios.index')) . '" class="perfil-volver"', false)
        ->assertDontSee('value="pan"', false);
});

test('una página anterior de otro sitio no se usa para volver', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);

    verPerfilComo($cliente, comercioDePrueba(), 'https://sitio-malicioso.example/comercios?search=pan')
        ->assertSee('href="' . e(route('comercios.index')) . '" class="perfil-volver"', false)
        ->assertDontSee('sitio-malicioso');
});

test('solo se toman los filtros conocidos de la búsqueda anterior', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);
    $previa = route('comercios.index') . '?search=pan&redirect=https://otro.example&x[]=1';

    verPerfilComo($cliente, comercioDePrueba(), $previa)
        ->assertSee('href="' . e(route('comercios.index', ['search' => 'pan'])) . '" class="perfil-volver"', false)
        ->assertDontSee('otro.example');
});

test('el texto buscado se escapa para evitar inyección de HTML', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);
    $previa = route('comercios.index', ['search' => '"><script>alert(1)</script>']);

    verPerfilComo($cliente, comercioDePrueba(), $previa)
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('el comerciante no ve el buscador ni el botón volver', function () {
    $comerciante = User::factory()->create(['role' => 'comerciante']);

    verPerfilComo($comerciante, comercioDePrueba())
        ->assertOk()
        ->assertDontSee('role="search"', false)
        ->assertDontSee('Volver a los resultados');
});

test('el listado de comercios sigue funcionando con la búsqueda del perfil', function () {
    $cliente = User::factory()->create(['role' => 'usuario']);
    $comercio = comercioDePrueba();

    $this->actingAs($cliente)
        ->get(route('comercios.index', ['search' => 'don julio']))
        ->assertOk()
        ->assertSee('Panadería Don Julio');
});
