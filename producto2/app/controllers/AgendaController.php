<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Incidencia;

final class AgendaController extends Controller
{
    public function __construct()
    {
        //Restricción de acceso a nivel de servidor: Solo rol 'tecnico'
        Auth::requireRole('tecnico');
    }

    /**
     * Muestra la agenda de incidencias asignadas al técnico autenticado
     */
    public function index(): void
    {
        $usuario = Auth::user();
        $tecnicoId = (int) ($usuario['id'] ?? 0);

        //Muestra las incidencias asociadas meidnate la consulta al modelo
        $incidencias = Incidencia::obtenerPorTecnico($tecnicoId);

        $this->view('tecnico/agenda', [
            'titulo'      => 'Mi Agenda de Intervenciones',
            'incidencias' => $incidencias
        ]);
    }

    /**
     * Transiciones permitidas para el técnico:
     * - De 'Asignada' -> 'En curso'
     * - De 'En curso' -> 'Finalizada'
     */
    public function cambiarEstado(): void
    {
        $this->requireCsrf('/tecnico/agenda');

        $incidenciaId = (int) $this->input('incidencia_id');
        $nuevoEstado  = (string) $this->input('nuevo_estado');
        $usuario      = Auth::user();
        $tecnicoId    = (int) ($usuario['id'] ?? 0);

        //Cargar datos actuales de la incidencia por su identificador
        $incidencia = Incidencia::obtenerPorId($incidenciaId);

        if (!$incidencia || (int) $incidencia['tecnico_id'] !== $tecnicoId) {
            Session::addFlash('error', 'No tienes permisos sobre esta incidencia.');
            $this->redirect('/tecnico/agenda');
        }

        $estadoActual = $incidencia['estado'];

        //Validación estricta de las transiciones del cambio de estado
        $transicionValida = false;
        if ($estadoActual === 'Asignada' && $nuevoEstado === 'En curso') {
            $transicionValida = true;
        } elseif ($estadoActual === 'En curso' && $nuevoEstado === 'Finalizada') {
            $transicionValida = true;
        }

        if (!$transicionValida) {
            Session::addFlash('error', "Transición no permitida de '{$estadoActual}' a '{$nuevoEstado}'.");
            $this->redirect('/tecnico/agenda');
        }

        //Actualizar estado en BBDD
        Incidencia::actualizarEstado($incidenciaId, $nuevoEstado);

        Session::addFlash('success', "Estado actualizado a '{$nuevoEstado}' correctamente.");
        $this->redirect('/tecnico/agenda');
    }
}