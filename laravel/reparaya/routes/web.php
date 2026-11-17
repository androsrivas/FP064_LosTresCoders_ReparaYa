<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('reparaya', [
        'project' => config('reparaya.name'),
        'group' => config('reparaya.group_code')
    ]);
});


/**
 * Rutas para la view Mi Perfil - CRUD de usuarios
 */
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
});



// TEMPORAL PARA PRUEBAS DE PERFIL (Eliminar antes de hacer el PR)
Route::get('/dev-login/{id}', function (int $id) {
    $user = User::findOrFail($id);
    Auth::login($user);

    return redirect()->route('profile.edit');
});