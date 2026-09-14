<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
// NO SE NECESITA 'RouteServiceProvider', ASÍ QUE LO QUITAMOS

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. AÑADIMOS 'role' A LA VALIDACIÓN
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:usuario,comerciante'], // <-- CAMBIO 1 (se mantiene)
        ]);

        // 2. AÑADIMOS 'role' A LA CREACIÓN DEL USUARIO
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // <-- CAMBIO 2 (se mantiene)
        ]);

        event(new Registered($user));

        Auth::login($user);

        // 3. AÑADIMOS LA LÓGICA DE REDIRECCIÓN
        if ($user->role === 'comerciante') {
            // Si es comerciante, lo redirigimos a la ruta 'comercio.create'
            return redirect()->route('comercio.create');
        }

        // Si es un usuario normal, usamos LA LÍNEA ORIGINAL DE TU ARCHIVO
        return redirect(route('dashboard', absolute: false)); // <-- CAMBIO 3 (Corregido)
    }
}
