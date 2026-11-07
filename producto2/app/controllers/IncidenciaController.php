<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Incidencia;
use App\Models\HistorialEstado;

class IncidenciaController extends Controller
{
    public function __construct()
    {
        // Requerir autenticació per a qualsevol acció sobre les incidències
        Auth::requireLogin();
    }

    /**
     * Mostra el detall d'una incidència amb el seu historial
     */
    public function detalle(int $id): void
    {
        $incidencia = Incidencia::obtenerPorId($id);
        $historial  = HistorialEstado::obtenerPorIncidencia($id);

        $this->view('admin/detalle', [
            'incidencia' => $incidencia,
            'historial'  => $historial
        ]);
    }
}