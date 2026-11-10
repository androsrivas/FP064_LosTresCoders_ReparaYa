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
use App\Services\IncidenciaService;

class AdminController extends Controller
{
    private IncidenciaService $service;

    public function __construct()
    {
        //Validación de rol: solo administradores
        Auth::requireRole(Roles::ADMIN);
        $this->service = new IncidenciaService();
    }

    /**
     * Muestra el panel principal de administración con el listado completo de incidencias.
     */
    public function incidencias(): void
    {
        // Obtenemos todas las incidencias 
        $incidencias = Incidencia::obtenerPorEstado('Todas');
        $tecnicos    = Tecnico::obtenerTodos(); 

        $this->view('admin/index', [
            'titulo'      => 'Gestión de Incidencias',
            'incidencias' => $incidencias,
            'tecnicos'    => $tecnicos
        ]);
    }

    /**
     * Alta manual de avisos realizada por el Administrador.
     * Delega la validación de 48h, fecha futura y generación de localizador al Servicio.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=incidencias');
            return;
        }

        $adminUserId = Auth::id() ?? (int) Session::get('usuario_id', 0);

        $payload = [
            'cliente_id'       => (int) ($_POST['cliente_id'] ?? 0),
            'especialidad_id'  => (int) ($_POST['especialidad_id'] ?? 0),
            'tipo_servicio_id' => (int) ($_POST['tipo_servicio_id'] ?? 0),
            'tipo_urgencia'    => is_string($_POST['tipo_urgencia'] ?? null) ? trim($_POST['tipo_urgencia']) : 'Estándar',
            'fecha_servicio'   => is_string($_POST['fecha_servicio'] ?? null) ? trim($_POST['fecha_servicio']) : '',
            'descripcion'      => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '',
            'direccion'        => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : ''
        ];

        //Se le pasa $adminUserId para que en el historial quede a nombre del admin
        $resultado = $this->service->crearIncidencia($payload, $adminUserId);

        if ($resultado['exito']) {
            Session::addFlash('success', 'Incidencia registrada con éxito. Localizador: ' . $resultado['localizador']);
        } else {
            Session::addFlash('error', $resultado['mensaje']);
        }

        $this->redirect('index.php?action=incidencias');
    }

    /**
     * Asignación de técnico a una incidencia.
     * Transición automática: Cambia de Pendiente a Asignada y registra trazabilidad.
     */
    public function asignar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=incidencias');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $tecnicoId    = (int) ($_POST['tecnico_id'] ?? 0);
        $adminUserId  = Auth::id() ?? (int) Session::get('usuario_id', 0);

        if ($incidenciaId <= 0 || $tecnicoId <= 0) {
            Session::addFlash('error', 'Debe seleccionar una incidencia y un técnico válidos.');
            $this->redirect('index.php?action=incidencias');
            return;
        }

        $resultado = $this->service->asignarTecnicoPorAdmin($incidenciaId, $tecnicoId, $adminUserId);

        if ($resultado['exito']) {
            Session::addFlash('success', $resultado['mensaje']);
        } else {
            Session::addFlash('error', $resultado['mensaje']);
        }

        $this->redirect('index.php?action=incidencias');
    }

    /**
     * Modificación de datos generales de una incidencia (Solo en estados Pendiente o Asignada).
     */
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=incidencias');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $adminUserId  = Auth::id() ?? (int) Session::get('usuario_id', 0);

        $payload = [
            'tecnico_id'      => isset($_POST['tecnico_id']) && $_POST['tecnico_id'] !== '' ? (int) $_POST['tecnico_id'] : null,
            'especialidad_id' => isset($_POST['especialidad_id']) ? (int) $_POST['especialidad_id'] : null,
            'descripcion'     => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : null,
            'direccion'       => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : null,
            'fecha_servicio'  => is_string($_POST['fecha_servicio'] ?? null) ? trim($_POST['fecha_servicio']) : null,
            'tipo_urgencia'   => is_string($_POST['tipo_urgencia'] ?? null) ? trim($_POST['tipo_urgencia']) : null,
            'estado'          => is_string($_POST['estado'] ?? null) ? trim($_POST['estado']) : null,
            'comentario'      => is_string($_POST['comentario'] ?? $_POST['observaciones'] ?? null) 
                                 ? trim($_POST['comentario'] ?? $_POST['observaciones']) : 'Modificado por el Administrador'
        ];

        //Filtramos valores nulos para conservar la información previa de campos no informados
        $datosFiltrados = array_filter($payload, static fn($v) => !is_null($v));

        $resultado = $this->service->modificarPorAdmin($incidenciaId, $datosFiltrados, $adminUserId);

        if ($resultado['exito']) {
            Session::addFlash('success', $resultado['mensaje']);
        } else {
            Session::addFlash('error', $resultado['mensaje']);
        }

        $this->redirect('index.php?action=incidencias');
    }

    /**
     * Cancelación de la incidencia por parte del Administrador.
     */
    public function cancelar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=incidencias');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $observacion  = is_string($_POST['observacion'] ?? null) ? trim($_POST['observacion']) : 'Cancelada por el Administrador';
        $adminUserId  = Auth::id() ?? (int) Session::get('usuario_id', 0);

        $datosActualizar = [
            'estado'     => 'Cancelada',
            'comentario' => $observacion
        ];

        $resultado = $this->service->modificarPorAdmin($incidenciaId, $datosActualizar, $adminUserId);

        if ($resultado['exito']) {
            Session::addFlash('success', 'Incidencia cancelada correctamente.');
        } else {
            Session::addFlash('error', $resultado['mensaje']);
        }

        $this->redirect('index.php?action=incidencias');
    }
}