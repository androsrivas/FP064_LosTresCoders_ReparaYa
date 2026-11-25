<?php
declare(strict_types=1);

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    $specialities = \App\Models\Speciality::orderBy('name')->get();
    $communities = auth()->user()?->managementCompany?->communities()->orderBy('name')->get()
        ?? collect();

    return view('home', compact('specialities', 'communities'));
})->name('home');

// Sólo visitantes (un usuario autenticado es redirigido a su panel)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/registro', [RegisterController::class, 'create'])->name('register');
    Route::post('/registro', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('perfil')->name('profile.')->controller(ProfileController::class)->group(function (): void {
        Route::get('/', 'show')->name('show');
        Route::get('/editar', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
        Route::put('/password', 'updatePassword')->name('password');
    });

    // CRUD de incidencias (compartido por particular y gestora)
    Route::middleware('role:particular,gestora')->group(function (): void {
        Route::resource('incidencias', IncidentController::class)
            ->only(['index', 'store', 'show'])
            ->names('incidents')
            ->parameters(['incidencias' => 'incident']);
        
        Route::patch('/incidencias/{incident}/cancelar', [IncidentController::class, 'cancel'])
            ->name('incidents.cancel');
    });

    // CRUD de comunidades (solo gestora)
    Route::middleware('role:gestora')->group(function (): void {
        Route::prefix('gestora')->name('manager.')->group(function (): void {
            Route::get('/', DashboardController::class)->name('home');
        });

        Route::resource('comunidades', CommunityController::class)
            ->only(['store', 'update', 'destroy'])
            ->names('communities')
            ->parameters(['comunidades' => 'community']);
    });
    
    // Paneles por rol (provisionales: B y C añadirán sus rutas)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::view('/', 'dashboard')->name('home');

        // CRUD de usuarios (solo admin y particular; técnicos y gestoras tienen su propio CRUD)
        Route::resource('usuarios', UserController::class)
            ->except(['show'])
            ->names('users')
            ->parameters(['usuarios' => 'user']);

        Route::patch('/usuarios/{user}/restaurar', [UserController::class, 'restore'])
            ->withTrashed()
            ->name('users.restore');
    });
    
    Route::middleware('role:tecnico')->prefix('tecnico')->name('technician.')->group(function (): void {
        Route::view('/', 'dashboard')->name('home');
    });
    
    Route::middleware('role:gestora')->prefix('gestora')->name('manager.')->group(function (): void {
        Route::get('/', DashboardController::class)->name('home');
    });
    
    Route::middleware('role:particular')->prefix('cliente')->name('customer.')->group(function (): void {
        Route::get('/', DashboardController::class)->name('home');
    });
});
