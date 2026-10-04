<?php

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearUsuarioConRol(string $rol, array $extra = []): User
{
    return User::factory()->create(array_merge(['role' => $rol], $extra));
}

function crearComercioDeDueno(User $dueno, array $extra = []): Comercio
{
    return Comercio::create(array_merge([
        'user_id' => $dueno->id,
        'nombre' => 'Ferretería El Tornillo',
        'direccion' => 'Calle Falsa 123',
        'rubro' => 'Ferretería',
    ], $extra));
}

// ---------------------------------------------------------------------------
// Quién puede entrar al panel
// ---------------------------------------------------------------------------

test('sin iniciar sesión, el panel de administración lleva al login', function () {
    $this->get('/admin')->assertRedirect(route('login'));
    $this->get('/admin/comercios')->assertRedirect(route('login'));
    $this->get('/admin/usuarios')->assertRedirect(route('login'));
});

test('un cliente no puede entrar al panel de administración', function () {
    $this->actingAs(crearUsuarioConRol('usuario'));

    $this->get(route('admin.index'))->assertNotFound();
    $this->get(route('admin.comercios'))->assertNotFound();
    $this->get(route('admin.usuarios'))->assertNotFound();
});

test('un comerciante no puede entrar al panel de administración', function () {
    $this->actingAs(crearUsuarioConRol('comerciante'));

    $this->get(route('admin.index'))->assertNotFound();
    $this->get(route('admin.comercios'))->assertNotFound();
    $this->get(route('admin.usuarios'))->assertNotFound();
});

test('el administrador ve las tres pantallas del panel', function () {
    $this->actingAs(crearUsuarioConRol('admin'));

    $this->get(route('admin.index'))->assertOk()->assertSee('Panel de administración');
    $this->get(route('admin.comercios'))->assertOk();
    $this->get(route('admin.usuarios'))->assertOk();
});

test('el administrador que entra a /dashboard es llevado a su panel', function () {
    $this->actingAs(crearUsuarioConRol('admin'))
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.index'));
});

test('el enlace Administración aparece solo para el administrador', function () {
    $this->actingAs(crearUsuarioConRol('admin'))
        ->get(route('admin.index'))
        ->assertSee('Administración');

    $this->actingAs(crearUsuarioConRol('usuario'))
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('admin.index'), false);
});

test('desde la web nadie puede registrarse como administrador', function () {
    $this->post('/register', [
        'name' => 'Intruso',
        'email' => 'intruso@example.com',
        'password' => 'password-muy-largo-123',
        'password_confirmation' => 'password-muy-largo-123',
        'role' => 'admin',
    ])->assertSessionHasErrors('role');

    expect(User::where('email', 'intruso@example.com')->exists())->toBeFalse();
});

test('desde el perfil nadie puede cambiarse el rol', function () {
    $cliente = crearUsuarioConRol('usuario');

    $this->actingAs($cliente)->patch(route('profile.update'), [
        'name' => $cliente->name,
        'email' => $cliente->email,
        'role' => 'admin',
    ]);

    expect($cliente->fresh()->role)->toBe('usuario');
});

// ---------------------------------------------------------------------------
// Listados
// ---------------------------------------------------------------------------

test('el listado de comercios muestra los de todos los dueños', function () {
    crearComercioDeDueno(crearUsuarioConRol('comerciante'), ['nombre' => 'Panadería Uno']);
    crearComercioDeDueno(crearUsuarioConRol('comerciante'), ['nombre' => 'Librería Dos']);

    $this->actingAs(crearUsuarioConRol('admin'))
        ->get(route('admin.comercios'))
        ->assertOk()
        ->assertSee('Panadería Uno')
        ->assertSee('Librería Dos');
});

test('el buscador del listado de comercios filtra por nombre, rubro y dirección', function () {
    $dueno = crearUsuarioConRol('comerciante');
    crearComercioDeDueno($dueno, ['nombre' => 'Panadería Uno', 'rubro' => 'Panadería', 'direccion' => 'Calle A 1']);
    crearComercioDeDueno($dueno, ['nombre' => 'Librería Dos', 'rubro' => 'Libros', 'direccion' => 'Calle B 2']);

    $this->actingAs(crearUsuarioConRol('admin'));

    $this->get(route('admin.comercios', ['q' => 'panad']))
        ->assertSee('Panadería Uno')
        ->assertDontSee('Librería Dos');

    $this->get(route('admin.comercios', ['q' => 'libros']))
        ->assertSee('Librería Dos')
        ->assertDontSee('Panadería Uno');

    $this->get(route('admin.comercios', ['q' => 'calle a']))
        ->assertSee('Panadería Uno')
        ->assertDontSee('Librería Dos');
});

test('el texto buscado en el listado se escapa', function () {
    $this->actingAs(crearUsuarioConRol('admin'))
        ->get(route('admin.comercios', ['q' => '<script>alert(1)</script>']))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('el listado de usuarios muestra rol y cantidad de comercios', function () {
    $comerciante = crearUsuarioConRol('comerciante', ['name' => 'Marta Comerciante', 'email' => 'marta@example.com']);
    crearComercioDeDueno($comerciante);
    crearUsuarioConRol('usuario', ['name' => 'Carlos Cliente', 'email' => 'carlos@example.com']);

    $this->actingAs(crearUsuarioConRol('admin'))
        ->get(route('admin.usuarios'))
        ->assertOk()
        ->assertSee('Marta Comerciante')
        ->assertSee('marta@example.com')
        ->assertSee('Comerciante')
        ->assertSee('Carlos Cliente');
});

test('el buscador del listado de usuarios filtra por nombre o correo', function () {
    crearUsuarioConRol('usuario', ['name' => 'Carlos Cliente', 'email' => 'carlos@example.com']);
    crearUsuarioConRol('usuario', ['name' => 'Laura Lopez', 'email' => 'laura@example.com']);

    $this->actingAs(crearUsuarioConRol('admin'));

    $this->get(route('admin.usuarios', ['q' => 'carlos']))
        ->assertSee('Carlos Cliente')
        ->assertDontSee('Laura Lopez');

    $this->get(route('admin.usuarios', ['q' => 'laura@']))
        ->assertSee('Laura Lopez')
        ->assertDontSee('Carlos Cliente');
});

// ---------------------------------------------------------------------------
// Gestión de comercios ajenos
// ---------------------------------------------------------------------------

test('el administrador puede abrir la edición de un comercio ajeno', function () {
    $comercio = crearComercioDeDueno(crearUsuarioConRol('comerciante'));

    $this->actingAs(crearUsuarioConRol('admin'))
        ->get(route('comercio.edit', $comercio))
        ->assertOk();
});

test('el administrador puede eliminar un comercio ajeno', function () {
    $comercio = crearComercioDeDueno(crearUsuarioConRol('comerciante'));

    $this->actingAs(crearUsuarioConRol('admin'))
        ->delete(route('comercio.destroy', $comercio))
        ->assertRedirect(route('admin.comercios'));

    $this->assertModelMissing($comercio);
});

test('un comerciante sigue sin poder tocar los comercios de otro', function () {
    $comercio = crearComercioDeDueno(crearUsuarioConRol('comerciante'));
    $otro = crearUsuarioConRol('comerciante');

    $this->actingAs($otro);

    $this->get(route('comercio.edit', $comercio))->assertNotFound();
    $this->delete(route('comercio.destroy', $comercio))->assertNotFound();

    $this->assertModelExists($comercio);
});

test('un comerciante sigue pudiendo eliminar su propio comercio', function () {
    $dueno = crearUsuarioConRol('comerciante');
    $comercio = crearComercioDeDueno($dueno);

    $this->actingAs($dueno)
        ->delete(route('comercio.destroy', $comercio))
        ->assertRedirect(route('dashboard'));

    $this->assertModelMissing($comercio);
});

// ---------------------------------------------------------------------------
// Comando para crear administradores
// ---------------------------------------------------------------------------

test('el comando admin:promover convierte una cuenta existente en administrador', function () {
    $usuario = crearUsuarioConRol('usuario', ['email' => 'jefe@example.com']);

    $this->artisan('admin:promover', ['email' => 'JEFE@example.com'])->assertExitCode(0);

    expect($usuario->fresh()->role)->toBe('admin');
});

test('el comando admin:promover avisa si la cuenta no existe', function () {
    $this->artisan('admin:promover', ['email' => 'nadie@example.com'])->assertExitCode(1);
});

test('el comando admin:promover --quitar le saca el rol de administrador', function () {
    $admin = crearUsuarioConRol('admin', ['email' => 'jefe@example.com']);

    $this->artisan('admin:promover', ['email' => 'jefe@example.com', '--quitar' => true])->assertExitCode(0);

    expect($admin->fresh()->role)->toBe('usuario');
});
