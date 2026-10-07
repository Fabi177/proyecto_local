<?php

use App\Models\Comercio;
use App\Models\Localidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function localidadPorNombre(string $nombre): Localidad
{
    return Localidad::where('nombre', $nombre)->firstOrFail();
}

function comercioEnLocalidad(?string $localidad, array $extra = []): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);
    $habilitado = $extra['habilitado'] ?? true;
    unset($extra['habilitado']);

    $comercio = Comercio::create(array_merge([
        'user_id' => $dueno->id,
        'nombre' => 'Comercio ' . uniqid(),
        'direccion' => 'Calle 123',
        'rubro' => 'Restaurante',
        'localidad_id' => $localidad ? localidadPorNombre($localidad)->id : null,
    ], $extra));

    // "habilitado" no es asignable en masa (como en el resto de los tests): se fuerza aparte
    $comercio->forceFill(['habilitado' => $habilitado])->save();

    return $comercio->fresh();
}

function sugerenciasDeLocalidades(string $q = ''): array
{
    return test()->getJson(route('localidades.sugerencias', ['q' => $q]))
        ->assertOk()
        ->json('sugerencias');
}

// ---------------------------------------------------------------- Catálogo

test('el catálogo trae Leandro N. Alem con el código postal 3315', function () {
    expect(Schema::hasTable('localidades'))->toBeTrue()
        ->and(Schema::hasColumn('comercios', 'localidad_id'))->toBeTrue();

    $alem = localidadPorNombre('Leandro N. Alem');
    expect($alem->codigo_postal)->toBe('3315')
        ->and($alem->provincia)->toBe('Misiones')
        ->and($alem->etiqueta)->toBe('Leandro N. Alem (3315)');
});

test('volver a correr el seeder no duplica localidades', function () {
    $antes = Localidad::count();

    (new Database\Seeders\LocalidadSeeder)->run();

    expect(Localidad::count())->toBe($antes);
});

test('normalizar deja el texto en minúsculas, sin tildes y con espacios prolijos', function () {
    expect(Localidad::normalizar('  Oberá  '))->toBe('obera')
        ->and(Localidad::normalizar('Leandro   N.  Alem'))->toBe('leandro n. alem')
        ->and(Localidad::normalizar('Apóstoles'))->toBe('apostoles');
});

test('al guardar una localidad con el modelo se calcula nombre_busqueda', function () {
    $localidad = Localidad::create(['nombre' => 'Villa Ñandú', 'provincia' => 'Misiones', 'codigo_postal' => '3300']);

    expect($localidad->fresh()->nombre_busqueda)->toBe('villa nandu');
});

// ---------------------------------------------------------------- Sugerencias

test('solo se sugieren localidades que tienen comercios habilitados', function () {
    comercioEnLocalidad('Leandro N. Alem');
    comercioEnLocalidad('Oberá', ['habilitado' => false]);

    $nombres = collect(sugerenciasDeLocalidades())->pluck('nombre')->all();

    expect($nombres)->toBe(['Leandro N. Alem']);
});

test('se busca por nombre sin importar mayúsculas ni tildes', function () {
    comercioEnLocalidad('Oberá');
    comercioEnLocalidad('Leandro N. Alem');

    expect(collect(sugerenciasDeLocalidades('OBERA'))->pluck('nombre')->all())->toBe(['Oberá'])
        ->and(collect(sugerenciasDeLocalidades('Oberá'))->pluck('nombre')->all())->toBe(['Oberá'])
        ->and(collect(sugerenciasDeLocalidades('alem'))->pluck('nombre')->all())->toBe(['Leandro N. Alem']);
});

test('al escribir un código postal salta la localidad (3315 => Leandro N. Alem)', function () {
    comercioEnLocalidad('Leandro N. Alem');
    comercioEnLocalidad('Oberá');

    $sugerencias = sugerenciasDeLocalidades('3315');

    expect($sugerencias)->toHaveCount(1)
        ->and($sugerencias[0]['nombre'])->toBe('Leandro N. Alem')
        ->and($sugerencias[0]['codigo_postal'])->toBe('3315')
        ->and($sugerencias[0]['etiqueta'])->toBe('Leandro N. Alem (3315)')
        ->and($sugerencias[0]['comercios'])->toBe(1);
});

test('el código postal se busca por el comienzo (33 trae las que empiezan con 33)', function () {
    comercioEnLocalidad('Leandro N. Alem');
    comercioEnLocalidad('Oberá');

    expect(collect(sugerenciasDeLocalidades('336'))->pluck('nombre')->all())->toBe(['Oberá'])
        ->and(sugerenciasDeLocalidades('9999'))->toBe([]);
});

test('un código postal compartido sugiere todas las localidades que lo usan', function () {
    comercioEnLocalidad('San Vicente');
    comercioEnLocalidad('El Soberbio');

    $nombres = collect(sugerenciasDeLocalidades('3364'))->pluck('nombre')->all();

    expect($nombres)->toEqualCanonicalizing(['San Vicente', 'El Soberbio']);
});

test('las que empiezan con el texto salen primero', function () {
    comercioEnLocalidad('San José');
    comercioEnLocalidad('Santa Ana');
    comercioEnLocalidad('Posadas'); // contiene "sa" pero no empieza con él

    $nombres = collect(sugerenciasDeLocalidades('sa'))->pluck('nombre')->all();

    expect($nombres[count($nombres) - 1])->toBe('Posadas')
        ->and(array_slice($nombres, 0, 2))->toEqualCanonicalizing(['San José', 'Santa Ana']);
});

test('sin texto devuelve las de más comercios primero', function () {
    comercioEnLocalidad('Oberá');
    comercioEnLocalidad('Leandro N. Alem');
    comercioEnLocalidad('Leandro N. Alem');

    $sugerencias = sugerenciasDeLocalidades();

    expect($sugerencias[0]['nombre'])->toBe('Leandro N. Alem')
        ->and($sugerencias[0]['comercios'])->toBe(2)
        ->and($sugerencias[1]['nombre'])->toBe('Oberá');
});

test('los comodines de LIKE se toman como texto común', function () {
    comercioEnLocalidad('Leandro N. Alem');

    expect(sugerenciasDeLocalidades('%'))->toBe([])
        ->and(sugerenciasDeLocalidades('_'))->toBe([]);
});

test('devuelve como máximo 8 sugerencias y solo datos públicos', function () {
    foreach (Localidad::limit(12)->pluck('nombre') as $nombre) {
        comercioEnLocalidad($nombre);
    }

    $sugerencias = sugerenciasDeLocalidades();

    expect($sugerencias)->toHaveCount(8)
        ->and(array_keys($sugerencias[0]))->toEqualCanonicalizing(['id', 'nombre', 'provincia', 'codigo_postal', 'comercios', 'etiqueta']);
});

test('las sugerencias de localidades son públicas (no piden login)', function () {
    $this->getJson(route('localidades.sugerencias', ['q' => 'alem']))->assertOk();
});

// ---------------------------------------------------------------- Filtro del dashboard

test('el dashboard filtra los comercios por la localidad elegida', function () {
    comercioEnLocalidad('Leandro N. Alem', ['nombre' => 'Panadería de Alem']);
    comercioEnLocalidad('Oberá', ['nombre' => 'Panadería de Oberá']);
    $alem = localidadPorNombre('Leandro N. Alem');

    $this->get(route('dashboard', ['localidad' => $alem->id]))
        ->assertOk()
        ->assertSee('Panadería de Alem')
        ->assertDontSee('Panadería de Oberá');
});

test('sin localidad elegida se ven los comercios de todas', function () {
    comercioEnLocalidad('Leandro N. Alem', ['nombre' => 'Panadería de Alem']);
    comercioEnLocalidad('Oberá', ['nombre' => 'Panadería de Oberá']);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Panadería de Alem')
        ->assertSee('Panadería de Oberá');
});

test('la localidad se combina con el texto buscado', function () {
    comercioEnLocalidad('Leandro N. Alem', ['nombre' => 'Panadería de Alem']);
    comercioEnLocalidad('Leandro N. Alem', ['nombre' => 'Ferretería de Alem']);
    comercioEnLocalidad('Oberá', ['nombre' => 'Panadería de Oberá']);
    $alem = localidadPorNombre('Leandro N. Alem');

    $this->get(route('dashboard', ['localidad' => $alem->id, 'search' => 'panadería']))
        ->assertOk()
        ->assertSee('Panadería de Alem')
        ->assertDontSee('Ferretería de Alem')
        ->assertDontSee('Panadería de Oberá');
});

test('los comercios deshabilitados no aparecen aunque sean de la localidad elegida', function () {
    comercioEnLocalidad('Leandro N. Alem', ['nombre' => 'Local Oculto', 'habilitado' => false]);
    $alem = localidadPorNombre('Leandro N. Alem');

    $this->get(route('dashboard', ['localidad' => $alem->id]))
        ->assertOk()
        ->assertDontSee('Local Oculto');
});

test('una localidad inexistente o mal escrita se ignora y se ven todos los comercios', function () {
    comercioEnLocalidad('Leandro N. Alem', ['nombre' => 'Panadería de Alem']);

    foreach (['999999', 'abc', '-1', '0', '1;DROP TABLE comercios'] as $valor) {
        $this->get(route('dashboard', ['localidad' => $valor]))
            ->assertOk()
            ->assertSee('Panadería de Alem');
    }

    $this->get('/dashboard?localidad[]=1')->assertOk()->assertSee('Panadería de Alem');
});

test('el buscador muestra la ciudad elegida y el título de resultados', function () {
    comercioEnLocalidad('Leandro N. Alem');
    $alem = localidadPorNombre('Leandro N. Alem');

    $this->get(route('dashboard', ['localidad' => $alem->id]))
        ->assertOk()
        ->assertSee('Leandro N. Alem (3315)', false)
        ->assertSee('en Leandro N. Alem');
});

test('la tarjeta del comercio muestra su localidad', function () {
    comercioEnLocalidad('Leandro N. Alem', ['direccion' => 'Av. San Martín 100']);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Av. San Martín 100, Leandro N. Alem');
});

test('la portada ya no trae el selector de ciudad ni el texto "L. N. Alem"', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('id="hero-loc-q"', false)
        ->assertDontSee('Ciudad o código postal')
        ->assertDontSee('rubros de L. N. Alem')
        ->assertSee('Busca comercios, servicios y rubros.')
        ->assertSee('imagenes/fondo-azul.jpg', false);
});

test('el panel público trae el selector de ciudad o código postal', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ciudad o código postal');
});

test('"Volver a los resultados" conserva la ciudad elegida', function () {
    $comercio = comercioEnLocalidad('Leandro N. Alem');
    $alem = localidadPorNombre('Leandro N. Alem');
    $urlDashboard = route('dashboard', ['localidad' => $alem->id]);

    $this->from($urlDashboard)
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('localidad=' . $alem->id, false);
});

test('el perfil público muestra la localidad con su código postal', function () {
    $comercio = comercioEnLocalidad('Leandro N. Alem', ['direccion' => 'Av. San Martín 100']);

    $this->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Av. San Martín 100, Leandro N. Alem (3315)');
});

// ---------------------------------------------------------------- Alta y edición

function datosDeComercio(array $extra = []): array
{
    return array_merge([
        'nombre' => 'Ferretería Sur',
        'direccion' => 'Calle 1 123',
        'rubros' => ['Ferreteria'],
    ], $extra);
}

test('al crear un comercio hay que elegir la localidad', function () {
    $dueno = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($dueno)->post(route('comercio.store'), datosDeComercio())
        ->assertSessionHasErrors(['localidad_id' => 'Elegí la localidad de tu comercio.']);

    expect(Comercio::count())->toBe(0);
});

test('al crear un comercio la localidad tiene que existir', function () {
    $dueno = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($dueno)->post(route('comercio.store'), datosDeComercio(['localidad_id' => 999999]))
        ->assertSessionHasErrors(['localidad_id' => 'La localidad elegida no es válida.']);

    $this->actingAs($dueno)->post(route('comercio.store'), datosDeComercio(['localidad_id' => 'abc']))
        ->assertSessionHasErrors(['localidad_id' => 'La localidad elegida no es válida.']);

    expect(Comercio::count())->toBe(0);
});

test('al crear un comercio se guarda la localidad elegida', function () {
    $dueno = User::factory()->create(['role' => 'comerciante']);
    $obera = localidadPorNombre('Oberá');

    $this->actingAs($dueno)->post(route('comercio.store'), datosDeComercio(['localidad_id' => $obera->id]))
        ->assertSessionHasNoErrors();

    expect(Comercio::firstOrFail()->localidad_id)->toBe($obera->id);
});

test('el formulario de alta ofrece las localidades del catálogo', function () {
    $dueno = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($dueno)->get(route('comercio.create'))
        ->assertOk()
        ->assertSee('name="localidad_id"', false)
        ->assertSee('Leandro N. Alem (3315)')
        ->assertSee('Oberá (3360)');
});

test('el formulario de edición viene con la localidad guardada elegida', function () {
    $comercio = comercioEnLocalidad('Oberá');
    $obera = localidadPorNombre('Oberá');

    $html = $this->actingAs($comercio->user)->get(route('comercio.edit', $comercio))->getContent();

    expect($html)->toMatch('/<option value="' . $obera->id . '"\s+selected>/');
});

test('al editar se puede cambiar la localidad', function () {
    $comercio = comercioEnLocalidad('Oberá');
    $alem = localidadPorNombre('Leandro N. Alem');

    $this->actingAs($comercio->user)->patch(route('comercio.update', $comercio), datosDeComercio([
        'localidad_id' => $alem->id,
    ]))->assertSessionHasNoErrors();

    expect($comercio->fresh()->localidad_id)->toBe($alem->id);
});

test('al editar sin enviar la localidad se conserva la que tenía', function () {
    $comercio = comercioEnLocalidad('Oberá');
    $obera = localidadPorNombre('Oberá');

    $this->actingAs($comercio->user)->patch(route('comercio.update', $comercio), datosDeComercio())
        ->assertSessionHasNoErrors();

    expect($comercio->fresh()->localidad_id)->toBe($obera->id);
});

test('al editar no se puede dejar la localidad vacía ni inválida', function () {
    $comercio = comercioEnLocalidad('Oberá');
    $obera = localidadPorNombre('Oberá');

    $this->actingAs($comercio->user)->patch(route('comercio.update', $comercio), datosDeComercio(['localidad_id' => '']))
        ->assertSessionHasErrors(['localidad_id']);

    $this->actingAs($comercio->user)->patch(route('comercio.update', $comercio), datosDeComercio(['localidad_id' => 999999]))
        ->assertSessionHasErrors(['localidad_id' => 'La localidad elegida no es válida.']);

    expect($comercio->fresh()->localidad_id)->toBe($obera->id);
});

test('si se borra una localidad del catálogo los comercios quedan sin localidad', function () {
    $comercio = comercioEnLocalidad('Oberá');

    localidadPorNombre('Oberá')->delete();

    expect($comercio->fresh()->localidad_id)->toBeNull();
});

test('el administrador ve la localidad en el listado de comercios', function () {
    comercioEnLocalidad('Oberá', ['nombre' => 'Local de Oberá']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.comercios'))
        ->assertOk()
        ->assertSee('Local de Oberá')
        ->assertSee('Localidad')
        ->assertSee('Oberá');
});
