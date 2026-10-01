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
        ->assertRedirect(route('comercio.show', $comercio));

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

test('el autor puede editar su reseña (estrellas y texto); otro cliente y el admin no', function () {
    $comercio = comercioParaResenas();
    $autor = User::factory()->create(['role' => 'usuario']);
    $otro = User::factory()->create(['role' => 'usuario']);
    $admin = User::factory()->create(['role' => 'admin']);

    $resena = Resena::create(['comercio_id' => $comercio->id, 'user_id' => $autor->id, 'calificacion' => 2, 'comentario' => 'Meh']);

    $this->actingAs($otro)->patch(route('resenas.update', $resena), ['calificacion' => 5])->assertForbidden();
    $this->actingAs($admin)->patch(route('resenas.update', $resena), ['calificacion' => 5])->assertForbidden();
    expect($resena->fresh()->calificacion)->toBe(2);

    $this->actingAs($autor)
        ->patch(route('resenas.update', $resena), ['calificacion' => 5, 'comentario' => '  Mejoró mucho  '])
        ->assertRedirect()
        ->assertSessionHas('status_resena');

    expect($resena->fresh()->calificacion)->toBe(5)
        ->and($resena->fresh()->comentario)->toBe('Mejoró mucho')
        ->and(Resena::count())->toBe(1);

    $this->actingAs($autor)
        ->patch(route('resenas.update', $resena), ['calificacion' => 9])
        ->assertSessionHasErrorsIn('editarResena', ['calificacion']);
    expect($resena->fresh()->calificacion)->toBe(5);
});

test('en la lista, el autor ve "Editar" junto a "Eliminar"; los demás no ven "Editar"', function () {
    $comercio = comercioParaResenas();
    $autor = User::factory()->create(['role' => 'usuario']);
    $otro = User::factory()->create(['role' => 'usuario']);
    $admin = User::factory()->create(['role' => 'admin']);
    Resena::create(['comercio_id' => $comercio->id, 'user_id' => $autor->id, 'calificacion' => 4, 'comentario' => 'Buen servicio']);

    $this->actingAs($autor)->get(route('comercio.show', $comercio))
        ->assertSee('data-resena-boton-editar', false)
        ->assertSee('data-resena-editar', false)
        ->assertSee('Eliminar');

    foreach ([$otro, $admin] as $persona) {
        $this->actingAs($persona)->get(route('comercio.show', $comercio))
            ->assertDontSee('data-resena-boton-editar', false)
            ->assertDontSee('data-resena-editar', false);
    }

    // El admin sigue pudiendo eliminar.
    $this->actingAs($admin)->get(route('comercio.show', $comercio))->assertSee('Eliminar');
});

test('el formulario para calificar aparece solo si el cliente todavía no tiene reseña en ese comercio', function () {
    $comercio = comercioParaResenas();
    $cliente = User::factory()->create(['role' => 'usuario']);
    $otro = User::factory()->create(['role' => 'usuario']);

    // Sin reseña: ve el formulario vacío.
    $this->actingAs($cliente)->get(route('comercio.show', $comercio))
        ->assertSee('data-resena-form', false)
        ->assertSee('Dejá tu calificación')
        ->assertDontSee('data-resena-ya-califico', false);

    $resena = Resena::create(['comercio_id' => $comercio->id, 'user_id' => $cliente->id, 'calificacion' => 4, 'comentario' => 'Buen servicio']);

    // Con reseña: ve el mensaje y el botón Editar, no el formulario.
    $this->actingAs($cliente)->get(route('comercio.show', $comercio))
        ->assertSee('data-resena-ya-califico', false)
        ->assertSee('data-resena-boton-editar', false)
        ->assertDontSee('data-resena-form', false);

    // Otro cliente, que no reseñó este comercio, sí ve el formulario.
    $this->actingAs($otro)->get(route('comercio.show', $comercio))
        ->assertSee('data-resena-form', false)
        ->assertDontSee('data-resena-ya-califico', false);

    // Si borra su reseña, el formulario vuelve a aparecer.
    $this->actingAs($cliente)->delete(route('resenas.destroy', $resena));
    $this->actingAs($cliente)->get(route('comercio.show', $comercio))
        ->assertSee('data-resena-form', false)
        ->assertDontSee('data-resena-ya-califico', false);
});

test('la portada muestra el buscador público y los rubros', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('name="search"', false)
        ->assertSee(json_encode(route('comercios.sugerencias')), false)
        ->assertSee('rubro=Restaurante', false)
        ->assertSee('Todo en un solo lugar');
});

test('al calificar, el cliente vuelve al perfil con el aviso flotante y ya no ve el formulario para calificar', function () {
    $comercio = comercioParaResenas();
    $cliente = User::factory()->create(['role' => 'usuario']);

    $this->actingAs($cliente)
        ->followingRedirects()
        ->post(route('resenas.store', $comercio), ['calificacion' => 4, 'comentario' => 'Buen servicio'])
        ->assertOk()
        ->assertSee('¡Gracias por su comentario!')
        ->assertSee('data-aviso-resena', false)
        ->assertDontSee('Ver / editar mi reseña')
        ->assertSee('Panadería Don Julio')
        ->assertSee('data-resena-ya-califico', false)
        ->assertDontSee('data-resena-form', false)
        ->assertDontSee('Actualizar mi calificación');

    // Se puede repetir las veces que quiera: edita la misma reseña.
    $this->actingAs($cliente)->post(route('resenas.store', $comercio), ['calificacion' => 5, 'comentario' => 'Excelente']);
    expect(Resena::count())->toBe(1)->and(Resena::first()->calificacion)->toBe(5);
});

test('el perfil dice "sin WhatsApp" solo cuando el comercio no cargó WhatsApp', function () {
    $sinWhatsapp = comercioParaResenas(['telefono' => '3754 457625']);

    $this->get(route('comercio.show', $sinWhatsapp))
        ->assertOk()
        ->assertSee('Teléfono / sin WhatsApp')
        ->assertSee('3754 457625');

    $conWhatsapp = comercioParaResenas(['telefono' => '3754 457625', 'red_whatsapp' => '+54 9 3754 111111']);

    $this->get(route('comercio.show', $conWhatsapp))
        ->assertOk()
        ->assertSee('3754 457625')
        ->assertDontSee('sin WhatsApp');
});

test('los formularios del comerciante llaman al campo "Teléfono fijo / sin WhatsApp"', function () {
    $comercio = comercioParaResenas();

    $this->actingAs($comercio->user)
        ->get(route('comercio.edit', $comercio))
        ->assertOk()
        ->assertSee('Teléfono fijo / sin WhatsApp');

    $this->actingAs($comercio->user)
        ->get(route('comercio.create'))
        ->assertOk()
        ->assertSee('Teléfono fijo / sin WhatsApp');
});
