<?php

use App\Http\Controllers\NotasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TareasController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ComercioController; // <-- Importación
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------------------
// --- RUTAS PÚBLICAS (Para Clientes y Visitantes) ---
// -------------------------------------------------------------------

Route::get('/', function () {
    return view('welcome');
})->name('home');

// (R)EAD: Muestra la página de BÚSQUEDA y RESULTADOS de todos los comercios
Route::get('/comercios', [ComercioController::class, 'index'])->name('comercios.index');

// (R)EAD: Muestra el perfil público de UN comercio
// (Debe ir después de las rutas protegidas específicas de comercio)
Route::get('/comercios/{comercio}', [ComercioController::class, 'show'])->name('comercio.show');


// -------------------------------------------------------------------
// --- RUTAS DE AUTENTICACIÓN Y PANEL ---
// -------------------------------------------------------------------

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';


// -------------------------------------------------------------------
// --- RUTAS PROTEGIDAS (Requieren Login) ---
// -------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- RUTAS DE GESTIÓN DE COMERCIO (Para Comerciantes) ---

    // (C)REATE: Muestra el formulario para CREAR
    Route::get('/comercio/registro', [ComercioController::class, 'create'])->name('comercio.create');
    // (C)REATE: GUARDA
    Route::post('/comercio', [ComercioController::class, 'store'])->name('comercio.store');

    // (U)PDATE: Muestra el formulario para EDITAR
    Route::get('/comercio/editar', [ComercioController::class, 'edit'])->name('comercio.edit');
    // (U)PDATE: ACTUALIZA
    Route::patch('/comercio', [ComercioController::class, 'update'])->name('comercio.update');

    // (D)ELETE: ELIMINA
    Route::delete('/comercio', [ComercioController::class, 'destroy'])->name('comercio.destroy');
});


// -------------------------------------------------------------------
// --- OTRAS RUTAS (Resources, Admin) ---
// -------------------------------------------------------------------

Route::resource('notas', NotasController::class);
Route::resource('tareas', TareasController::class);
Route::resource('usuarios', UsuariosController::class);

Route::prefix('admin')->group(function () {
    Route::get('/usuarios', function () {
        dd('Listado completo de usuarios');
    })->name('admin.usuarios.index');
});

