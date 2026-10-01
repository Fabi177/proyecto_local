<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'usuario',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new merchants register and go to create their business', function () {
    $response = $this->post('/register', [
        'name' => 'Test Merchant',
        'email' => 'merchant@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'comerciante',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('comercio.create'));
});

test('registration requires a valid role', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});