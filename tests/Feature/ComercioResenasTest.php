<?php

use App\Models\Comercio;
use App\Models\Resena;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function comercioParaResenas(array $extra = []): Comercio
{
    $dueno = User::factory()->create(['role' => 'comerciante']);

    return Comercio::create(array_merge([
        'user_id' => $dueno->id,
        'nombre' => 'Panadería Don Julio',
        'direccion' => 'Av. Siempreviva 742',
        'rubro' => 'Panaderia',
    ], $extra));
}

test('un visitante sin sesión ve el perfil, el promedio y los comentarios', function () {
    $comercio = comercioParaResenas();
    $ana = User::factory()->create(['role' => 'usuario', 'name' => 'Ana Pérez', 'email' => 'ana@secreto.test']);
    Resena::create(['comercio_id' => $comercio->id, 'user_id' => $ana->id, 'calificacion' => 4, 'comentario' => 'Muy buen pan']);

    $this->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('Muy buen pan')
        ->assertSee('Ana Pérez')
        ->assertSee('4,0')
        ->assertSee('creá tu cuenta gratis')
        ->assertDontSee('data-resena-form', false)
        ->assertDontSee('ana@secreto.test');
});

test('un visitante sin sesión no puede publicar una reseña', function () {
    $comercio = comercioParaResenas();

    $this->post(route('resenas.store', $comercio), ['calificacion' => 5])
        ->assertRedirect(route('login'));

    expect(Resena::count())->toBe(0);
});

test('el contacto (WhatsApp y redes) sigue siendo público', function () {
    $comercio = comercioParaResenas(['red_whatsapp' => '+54 9 3743 123456', 'red_instagram' => 'donjulio']);

    $this->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertSee('wa.me/5493743123456', false)
        ->assertSee('instagram.com/donjulio', false);
});

test('un cliente puede calificar y comentar', function () {
    $comercio = comercioParaResenas();
    $cliente = User::factory()->create(['role' => 'usuario']);

    $this->actingAs($cliente)
        ->post(route('resenas.store', $comercio), ['calificacion' => 5, 'comentario' => '  Excelente atención  '])
        ->assertRedirect(route('comercio.show', $comercio).'#resenas');

    $resena = Resena::first();
    expect($resena->user_id)->toBe($cliente->id)
        ->and($resena->comercio_id)->toBe($comercio->id)
        ->and($resena->calificacion)->toBe(5)
        ->and($resena->comentario)->toBe('Excelente atención');
});

test('cada cliente tiene una sola reseña por comercio y puede editarla', function () {
    $comercio = comercioParaResenas();
    $cliente = User::factory()->create(['role' => 'usuario']);

    $this->actingAs($cliente)->post(route('resenas.store', $comercio), ['calificacion' => 2, 'comentario' => 'Meh']);
    $this->actingAs($cliente)->post(route('resenas.store', $comercio), ['calificacion' => 5, 'comentario' => 'Mejoró mucho']);

    expect(Resena::count())->toBe(1)
        ->and(Resena::first()->calificacion)->toBe(5)
        ->and(Resena::first()->comentario)->toBe('Mejoró mucho');
});

test('el comentario es opcional y la calificación debe ir de 1 a 5', function () {
    $comercio = comercioParaResenas();
    $cliente = User::factory()->create(['role' => 'usuario']);

    $this->actingAs($cliente)->post(route('resenas.store', $comercio), ['calificacion' => 3])->assertSessionHasNoErrors();
    expect(Resena::first()->comentario)->toBeNull();

    foreach ([0, 6, 'abc', null] as $invalida) {
        $this->actingAs($cliente)
            ->post(route('resenas.store', $comercio), ['calificacion' => $invalida])
            ->assertSessionHasErrors('calificacion');
    }
});

test('un comerciante no puede calificar, ni siquiera su propio comercio', function () {
    $comercio = comercioParaResenas();
    $otroComerciante = User::factory()->create(['role' => 'comerciante']);

    $this->actingAs($comercio->user)->post(route('resenas.store', $comercio), ['calificacion' => 5])->assertForbidden();
    $this->actingAs($otroComerciante)->post(route('resenas.store', $comercio), ['calificacion' => 5])->assertForbidden();

    expect(Resena::count())->toBe(0);
});

test('el comerciante ve las reseñas pero no el formulario para escribir', function () {
    $comercio = comercioParaResenas();

    $this->actingAs($comercio->user)
        ->get(route('comercio.show', $comercio))
        ->assertOk()
        ->assertDontSee('data-resena-form', false)
        ->assertSee('Las calificaciones las dejan los clientes');
});

test('el autor puede borrar su reseña, otro cliente no, y el admin sí', function () {
    $comercio = comercioParaResenas();
    $autor = User::factory()->create(['role' => 'usuario']);
    $otro = User::factory()->create(['role' => 'usuario']);
    $admin = User::factory()->create(['role' => 'admin']);

    $resena = Resena::create(['comercio_id' => $comercio->id, 'user_id' => $autor->id, 'calificacion' => 1, 'comentario' => 'Spam']);

    $this->actingAs($otro)->delete(route('resenas.destroy', $resena))->assertForbidden();
    expect(Resena::count())->toBe(1);

    $this->actingAs($admin)->delete(route('resenas.destroy', $resena))->assertRedirect();
    expect(Resena::count())->toBe(0);

    $propia = Resena::create(['comercio_id' => $comercio->id, 'user_id' => $autor->id, 'calificacion' => 4]);
    $this->actingAs($autor)->delete(route('resenas.destroy', $propia))->assertRedirect();
    expect(Resena::count())->toBe(0);
});

test('un comentario con HTML se muestra como texto, no se ejecuta', function () {
    $comercio = comercioParaResenas();
    $cliente = User::factory()->create(['role' => 'usuario']);
    Resena::create(['comercio_id' => $comercio->id, 'user_id' => $cliente->id, 'calificacion' => 3, 'comentario' => '<script>alert(1)</script>']);

    $this->get(route('comercio.show', $comercio))
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

test('la portada muestra el buscador público y los rubros', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('name="search"', false)
        ->assertSee(json_encode(route('comercios.sugerencias')), false)
        ->assertSee('rubro=Restaurante', false)
        ->assertSee('Todo en un solo lugar');
});
