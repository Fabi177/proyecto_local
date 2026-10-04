<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\TareasController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ComercioController; // <-- Importación
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------------------
// --- RUTAS PÚBLICAS (Para Clientes y Visitantes) ---
// -------------------------------------------------------------------

Route::get('/', function () {
    return view('welcome');
})->name('home');

// La búsqueda y el listado de comercios viven en /dashboard. Esta dirección solo queda para que
// los enlaces viejos sigan funcionando: redirige conservando lo buscado (?search=...&rubro[]=...).
Route::get('/comercios', fn (Request $request) => redirect()->route('dashboard', $request->query()));

// Sugerencias en vivo para los buscadores (devuelve JSON).
// IMPORTANTE: debe ir ANTES de /comercios/{comercio}, si no "sugerencias" se toma como el id de un comercio.
Route::get('/comercios/sugerencias', [ComercioController::class, 'sugerencias'])
    ->middleware('throttle:60,1')
    ->name('comercios.sugerencias');

// (R)EAD: Muestra el perfil público de UN comercio
// (Debe ir después de las rutas protegidas específicas de comercio)
Route::get('/comercios/{comercio}', [ComercioController::class, 'show'])->name('comercio.show');


// -------------------------------------------------------------------
// --- RUTAS DE AUTENTICACIÓN Y PANEL ---
// -------------------------------------------------------------------

// /dashboard es público: visitantes y clientes ven el buscador y todos los comercios;
// el comerciante ve su panel y el administrador es llevado al suyo (lógica en el controlador).
Route::get('/dashboard', [ComercioController::class, 'dashboard'])->name('dashboard');

require __DIR__.'/auth.php';


// -------------------------------------------------------------------
// --- RUTAS PROTEGIDAS (Requieren Login) ---
// -------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- CALIFICACIONES Y COMENTARIOS (leer es público; escribir exige login y cuenta de cliente) ---
    Route::post('/comercios/{comercio}/resenas', [ResenaController::class, 'store'])
        ->whereNumber('comercio')
        ->middleware('throttle:20,1')
        ->name('resenas.store');
    Route::patch('/resenas/{resena}', [ResenaController::class, 'update'])
        ->whereNumber('resena')
        ->middleware('throttle:20,1')
        ->name('resenas.update');
    Route::delete('/resenas/{resena}', [ResenaController::class, 'destroy'])
        ->whereNumber('resena')
        ->name('resenas.destroy');

    // --- RUTAS DE GESTIÓN DE COMERCIO (Para Comerciantes) ---

    // (C)REATE: Muestra el formulario para CREAR
    Route::get('/comercio/registro', [ComercioController::class, 'create'])->name('comercio.create');
    // (C)REATE: GUARDA
    Route::post('/comercio', [ComercioController::class, 'store'])->name('comercio.store');

    // Un comerciante puede tener varios comercios: editar, actualizar y eliminar
    // trabajan sobre UN comercio puntual ({comercio} = id).

    // (U)PDATE: Muestra el formulario para EDITAR
    Route::get('/comercio/{comercio}/editar', [ComercioController::class, 'edit'])->whereNumber('comercio')->name('comercio.edit');
    // (U)PDATE: ACTUALIZA
    Route::patch('/comercio/{comercio}', [ComercioController::class, 'update'])->whereNumber('comercio')->name('comercio.update');

    // (D)ELETE: ELIMINA
    Route::delete('/comercio/{comercio}', [ComercioController::class, 'destroy'])->whereNumber('comercio')->name('comercio.destroy');

    // Habilitar / deshabilitar: un comercio deshabilitado deja de mostrarse al público (no se borra nada).
    Route::patch('/comercio/{comercio}/estado', [ComercioController::class, 'cambiarEstado'])->whereNumber('comercio')->name('comercio.estado');

    // Compatibilidad: la URL vieja (cuando había un solo comercio) ahora lleva al panel.
    Route::get('/comercio/editar', fn () => redirect()->route('dashboard'));
});


// -------------------------------------------------------------------
// --- OTRAS RUTAS (Resources, Admin) ---
// -------------------------------------------------------------------

Route::resource('notas', NotasController::class);
Route::resource('tareas', TareasController::class);
Route::resource('usuarios', UsuariosController::class);

// --- PANEL DE ADMINISTRACIÓN (solo administradores: 'auth' + 'admin') ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/comercios', [AdminController::class, 'comercios'])->name('comercios');
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
});

