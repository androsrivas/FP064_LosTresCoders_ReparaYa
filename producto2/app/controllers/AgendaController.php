<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;
use App\Core\Session;
use App\Models\HistorialEstado;
use App\Models\Incidencia;
use App\Models\Tecnico;
use App\Services\EstadoIncidencia;

class AgendaController extends Controller
{
    public function __construct()
    {
        // Restricción de seguridad: Solo accesible para técnicos
        Auth::requireRole(Roles::TECNICO);
    }

    /**
     * Muestra la agenda de incidencias asignadas al técnico autenticado.
     * Ruta: GET /tecnico/agenda o GET /agenda
     */
    public function index(): void
    {
        $tecnico = $this->obtenerTecnicoAutenticado();
        
        // Obtener incidencias asociadas al id del técnico
        $incidencias = Incidencia::obtenerPorTecnico((int) $tecnico['id']);

        $this->view('admin/agenda', [
            'titulo'      => 'Mi Agenda de Incidencias',
            'incidencias' => $incidencias,
            'tecnico'     => $tecnico
        ]);
    }

    /**
     * Muestra la vista detallada de las incidencias del técnico o redirige según la ruta.
     * Ruta: GET /tecnico/incidencias
     */
    public function incidencias(): void
    {
        $this->index();
    }

    /**
     * Muestra el detalle completo de una incidencia concreta de la agenda.
     */
    public function show(int $id): void
    {
        $tecnico = $this->obtenerTecnicoAutenticado();
        $incidencia = Incidencia::obtenerPorId($id);

        // Verificar que la incidencia existe y pertenece al técnico autenticado
        if (!$incidencia || (int) ($incidencia['tecnico_id'] ?? 0) !== (int) $tecnico['id']) {
            $this->flash('danger', 'Incidencia no encontrada o no tienes permisos para consultarla.');
            $this->redirect('/tecnico/agenda');
            return;
        }

        $historial = HistorialEstado::obtenerPorIncidencia($id);

        $this->view('incidencias/_contenido_detalle', [
            'titulo'     => 'Detalle de Incidencia #' . $incidencia['localizador'],
            'incidencia' => $incidencia,
            'historial'  => $historial
        ]);
    }

    /**
     * Cambia el estado de una incidencia asignada.
     * Transiciones permitidas: 'Asignada' -> 'En curso' y 'En curso' -> 'Finalizada'.
     * Ruta: POST /tecnico/incidencia/estado
     */
    public function cambiarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/tecnico/agenda');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $nuevoEstado  = trim($_POST['nuevo_estado'] ?? '');
        $comentario   = trim($_POST['comentario'] ?? '');

        $tecnico = $this->obtenerTecnicoAutenticado();
        $incidencia = Incidencia::obtenerPorId($incidenciaId);

        // Regla de negocio: No se puede modificar una incidencia de otro técnico
        if (!$incidencia || (int) ($incidencia['tecnico_id'] ?? 0) !== (int) $tecnico['id']) {
            $this->flash('danger', 'No puedes modificar incidencias pertenecientes a otros técnicos.');
            $this->redirect('/tecnico/agenda');
            return;
        }

        $estadoActual = (string) $incidencia['estado'];

        // Regla de negocio: Validar avance mediante la máquina de estados
        if (!EstadoIncidencia::puedeAvanzarTecnico($estadoActual, $nuevoEstado)) {
            $this->flash('danger', "Transición de estado no permitida de '{$estadoActual}' a '{$nuevoEstado}'.");
            $this->redirect('/tecnico/agenda');
            return;
        }

        // 1. Actualizar el estado en la base de datos
        Incidencia::actualizarEstado($incidenciaId, $nuevoEstado);

        // 2. Registrar la traza en el historial de estados
        $usuarioId = Auth::id() ?? (int) Session::get('usuario_id', 0);
        $textoComentario = $comentario !== '' 
            ? $comentario 
            : "Estado actualizado a '{$nuevoEstado}' por el técnico.";

        HistorialEstado::registrar(
            $incidenciaId,
            $estadoActual,
            $nuevoEstado,
            $usuarioId,
            $textoComentario
        );

        $this->flash('success', "Estado de la incidencia actualizado a '{$nuevoEstado}'.");
        $this->redirect('/tecnico/agenda');
    }

    /**
     * Helper privado para resolver la entidad Tecnico vinculada al usuario logueado.
     */
    private function obtenerTecnicoAutenticado(): array
    {
        $user = Auth::user();
        $usuarioId = (int) ($user['id'] ?? $user['usuario_id'] ?? Session::get('usuario_id', 0));

        $tecnico = Tecnico::findByUsuarioId($usuarioId);

        if (!$tecnico) {
            $this->flash('danger', 'No se ha encontrado el perfil de técnico asociado a este usuario.');
            $this->redirect('/login');
        }

        return $tecnico;
    }
}