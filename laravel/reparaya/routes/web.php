<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncidentController;

Route::get('/', function () {
    return view('reparaya', [
        'project' => config('reparaya.name'),
        'group' => config('reparaya.group_code')
    ]);
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