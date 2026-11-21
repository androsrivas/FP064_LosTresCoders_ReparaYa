<?php
declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SpecialityController;
use App\Http\Controllers\Admin\TechnicianController;

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

    // CRUD de incidencias (compartido por particular y gestora)
    Route::middleware('role:particular,gestora')->group(function (): void {
        Route::resource('incidencias', IncidentController::class)
            ->only(['index', 'store', 'show'])
            ->names('incidents')
            ->parameters(['incidencias' => 'incident']);
        
        Route::patch('/incidencias/{incident}/cancelar', [IncidentController::class, 'cancel'])
            ->name('incidents.cancel');
    });
    
    // Paneles por rol (provisionales: B y C añadirán sus rutas)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::view('/', 'dashboard')->name('home');

        Route::resource('especialidades', SpecialityController::class)
            ->names('specialities')
            ->parameters(['especialidades' => 'speciality'])
            ->except(['show']);

        Route::resource('tecnicos', TechnicianController::class)
            ->names('technicians')
            ->parameters(['tecnicos' => 'technician'])
            ->except(['show']);
            
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
