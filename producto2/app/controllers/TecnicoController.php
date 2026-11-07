<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Incidencia;
use App\Core\Session;
use App\Models\HistorialEstado;

class TecnicoController extends Controller
{
    public function __construct()
    {
        //Restricción de seguridad (solo usuarios con rol de técnico)
        Auth::requireRole('tecnico');
    }
/**
 * Cambia el estado de una incidencia asignada a 'En curso'
 */
public function iniciarIntervencion(): void
{
    $this->requireCsrf('/tecnico/incidencias');

    $incidenciaId = (int) $this->input('incidencia_id');
    $user         = Auth::user();

    $incidencia = Incidencia::obtenerPorId($incidenciaId);
    if (!$incidencia) {
        Session::addFlash('error', 'La incidencia no existe.');
        $this->redirect('/tecnico/incidencias');
    }

    // Control de propiedad: un técnico solo puede modificar sus propias incidencias
        if ((int) $incidencia['tecnico_id'] !== (int) $user['id']) {
            Session::addFlash('error', 'No tienes permiso para modificar esta incidencia.');
            $this->redirect('/tecnico/agenda');
        }
        
    // Regla de transición: Solo se puede iniciar si está 'Asignada'
    if ($incidencia['estado'] !== 'Asignada') {
        Session::addFlash('error', "No se puede iniciar una intervención en estado '{$incidencia['estado']}'.");
        $this->redirect('/tecnico/incidencias');
    }

    // Actualización de estado en BBDD
    Incidencia::actualizarEstado($incidenciaId, 'En curso');

    // Auditoría en el historial
    HistorialEstado::crear(
        $incidenciaId,
        'Asignada',
        'En curso',
        (int) $user['id'],
        'El técnico ha iniciado la intervención'
    );

    Session::addFlash('success', 'La incidencia ha pasado a estado En curso.');
    $this->redirect('/tecnico/incidencias');
}

/**
 * Finaliza la intervención de una incidencia
 */
public function finalizarIntervencion(): void
{
    $this->requireCsrf('/tecnico/incidencias');

    $incidenciaId = (int) $this->input('incidencia_id');
    $comentario   = trim((string) $this->input('comentario', 'Intervención finalizada con éxito'));
    $user         = Auth::user();

    $incidencia = Incidencia::obtenerPorId($incidenciaId);
    if (!$incidencia) {
        Session::addFlash('error', 'La incidencia no existe.');
        $this->redirect('/tecnico/incidencias');
    }

    // Control de propiedad: un técnico solo puede modificar sus propias incidencias
        if ((int) $incidencia['tecnico_id'] !== (int) $user['id']) {
            Session::addFlash('error', 'No tienes permiso para modificar esta incidencia.');
            $this->redirect('/tecnico/agenda');
        }

    // Regla de transición: Solo se puede finalizar si está 'En curso'
    if ($incidencia['estado'] !== 'En curso') {
        Session::addFlash('error', "No se puede finalizar una incidencia en estado '{$incidencia['estado']}'.");
        $this->redirect('/tecnico/incidencias');
    }

    // Actualización de estado en BBDD
    Incidencia::actualizarEstado($incidenciaId, 'Finalizada');

    // Auditoría en el historial
    HistorialEstado::crear(
        $incidenciaId,
        'En curso',
        'Finalizada',
        (int) $user['id'],
        $comentario
    );

    Session::addFlash('success', 'La incidencia ha sido marcada como Finalizada.');
    $this->redirect('/tecnico/incidencias');
}

/**
 * Muestra la agenda/listado de incidencias asignadas al técnico
 */
public function agenda(): void
{
    $user = Auth::user();
    
    // Obtenemos únicamente las incidencias del técnico autenticado
    $incidencias = Incidencia::obtenerPorTecnico((int) $user['id']);

    $this->view('tecnico/agenda', [
        'incidencias' => $incidencias
    ]);
}

/**
 * Muestra el detalle de una incidencia asignada al técnico
 */
public function detalle(int $id): void
{
    $user       = Auth::user();
    $incidencia = Incidencia::obtenerPorId($id);

    //Validación de que existe la incidencia
    if (!$incidencia || (int) $incidencia['tecnico_id'] !== (int) $user['id']) {
        Session::addFlash('error', 'No tienes permiso para ver esta incidencia o no existe.');
        $this->redirect('/tecnico/agenda');
    }

    $historial = HistorialEstado::obtenerPorIncidencia($id);

    $this->view('tecnico/detalle', [
        'incidencia' => $incidencia,
        'historial'  => $historial
    ]);
}
}