<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;
use App\Models\Incidencia;
use App\Models\Tecnico;

class CalendarioController extends Controller
{
    public function __construct()
    {
        // Accesible tanto para administradores como para técnicos
        Auth::requireRole(Roles::ADMIN, Roles::TECNICO);
    }

    /**
     * Muestra la vista principal del calendario y los avisos laterales.
     * Ruta: GET /admin/calendario o GET /calendario
     */
    public function index(): void
    {
        $user = Auth::user();
        $rol  = Auth::role();

        $pendientes = [];

        if ($rol === Roles::ADMIN) {
            // El administrador ve todos los avisos activos en el lateral
            $pendientes = Incidencia::obtenerAvisos();
        } else {
            // El técnico solo ve sus avisos activos asignados
            $usuarioId = (int) ($user['id'] ?? $user['usuario_id'] ?? 0);
            $tecnico   = Tecnico::findByUsuarioId($usuarioId);

            if ($tecnico) {
                $pendientes = Incidencia::obtenerAvisos((int) $tecnico['id']);
            }
        }

        // Carga la vista admin/agenda pasando la variable $pendientes exigida por el template
        $this->view('admin/agenda', [
            'titulo'     => 'Agenda de Servicios',
            'pendientes' => $pendientes,
            'rol'        => $rol
        ]);
    }

    /**
     * Devuelve los eventos en formato JSON consumidos por FullCalendar en la vista agenda.php.
     * Ruta: GET /calendario/eventos
     */
    public function eventos(): void
    {
        $user = Auth::user();
        $rol  = Auth::role();

        if ($rol === Roles::ADMIN) {
            $incidencias = Incidencia::obtenerTodas('ASC');
        } else {
            $usuarioId   = (int) ($user['id'] ?? $user['usuario_id'] ?? 0);
            $tecnico     = Tecnico::findByUsuarioId($usuarioId);
            $incidencias = $tecnico ? Incidencia::obtenerPorTecnico((int) $tecnico['id']) : [];
        }

        // Formatea las incidencias a los campos esperados por FullCalendar 5
        $eventos = array_map(function (array $incidencia): array {
            $esUrgente = strtolower($incidencia['tipo_urgencia'] ?? '') === 'urgente';

            return [
                'id'              => $incidencia['id'],
                'title'           => '#' . $incidencia['localizador'] . ' - ' . ($incidencia['nombre_especialidad'] ?? 'Servicio'),
                'start'           => $incidencia['fecha_servicio'],
                'backgroundColor' => $esUrgente ? '#dc3545' : '#ffc107',
                'borderColor'     => $esUrgente ? '#dc3545' : '#ffc107',
                'textColor'       => $esUrgente ? '#ffffff' : '#000000',
                'url'             => url('/incidencia/detalle/' . $incidencia['id'])
            ];
        }, $incidencias);

        $this->json($eventos);
    }
}