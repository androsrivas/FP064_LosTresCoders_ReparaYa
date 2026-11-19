<?php
declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncidentController;

Route::view('/', 'home')->name('home');

// Sólo visitantes (un usuario autenticado es redirigido a su panel)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/registro', [RegisterController::class, 'create'])->name('register');
    Route::post('/registro', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    
    // Paneles por rol (provisionales: B y C añadirán sus rutas)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::view('/', 'dashboard')->name('home');
    });
    
    Route::middleware('role:tecnico')->prefix('tecnico')->name('technician.')->group(function (): void {
        Route::view('/', 'dashboard')->name('home');
    });
    
    Route::middleware('role:gestora')->prefix('gestora')->name('manager.')->group(function (): void {
        Route::view('/', 'dashboard')->name('home');
    });
    
    Route::middleware('role:particular')->prefix('cliente')->name('customer.')->group(function (): void {
        Route::view('/', 'dashboard')->name('home');
    });
});

// Rutas del CRUD de Incidencias (Cliente / Gestor)
Route::prefix('incidencias')
    ->name('incidents.')
    ->middleware(['auth', 'role:particular,gestora'])
    ->controller(IncidentController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        // Route::get('/crear', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{incident}', 'show')->name('show');
        Route::patch('/{incident}/cancelar', 'cancel')->name('cancel');
    });




/**
 * Rutas para testear las diferentes partes del CRUD a medida que se vayan incorporando.
 * Descomentar la sección correspondiente cuando se quiera testear cada cosa
 */

// CRUD INCIDENCIAS CLIENTE
Route::get('/dev-login/{id}', function (int $id) {
    auth()->loginUsingId($id);
    return redirect()->route('incidents.index');
});