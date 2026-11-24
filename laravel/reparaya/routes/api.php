<?php
declare(strict_types=1);

use App\Http\Controllers\Api\ServicioZonaController;
use Illuminate\Support\Facades\Route;

Route::get('/servicios/zonas', [ServicioZonaController::class, 'index'])
    ->name('api.servicios.zonas');