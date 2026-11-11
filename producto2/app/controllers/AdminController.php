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
        $incidencias = Incidencia::obtenerTodas();
        $tecnicos    = Tecnico::all(); 

        $this->view('admin/gestion', [
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
            // [REVISION]: Evitar pantallas en blanco si la petición no es POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $referer = $_SERVER['HTTP_REFERER'] ?? '/gestion';
            $this->redirect($referer);
            return;
        }

        // [REVISION]: Inyección del cliente_id desde la sesión activa y filtrado de payload (Mass Assignment)
        $rol = (string) Session::get('usuario_rol', '');
        $clienteId = 0;
        if (class_exists('App\Core\Auth') && Auth::check()) {
            $user = Auth::user();
            $clienteId = (int) ($user['id'] ?? $user['usuario_id'] ?? 0);
        }
        if ($clienteId === 0) {
            $clienteId = (int) (Session::get('usuario_id') ?? Session::get('user_id') ?? 0);
        }

        $payload = [
            'cliente_id' => ($rol === 'admin' && isset($_POST['cliente_id'])) ? (int) $_POST['cliente_id'] : $clienteId,
            'especialidad_id' => (int) ($_POST['especialidad_id'] ?? 0),
            'tipo_urgencia' => trim($_POST['tipo_urgencia'] ?? 'Estándar'),
            'fecha_servicio' => trim($_POST['fecha_servicio'] ?? ''),
            'descripcion' => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '',
            'direccion' => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : ''
        ];

        $resultado = $this->service->crearIncidencia($payload);

        if ($resultado['exito']) {
            Session::forget('old');
            Session::forget('errors');
            $this->flash('success', 'Incidencia registrada con éxito. Localizador: ' . $resultado['localizador']);

            $redirectAction = $rol === 'admin' ? 'gestion' : 'perfil';
            $this->redirect("/{$redirectAction}");
            return;
        }

        Session::set('errors', ['general' => [$resultado['mensaje']]]);
        Session::set('old', $payload);

        $redirectAction = $rol === 'admin' ? 'gestion' : 'perfil';
        $this->redirect("/{$redirectAction}");
        return;
    }

    /**
     * Asignación de técnico a una incidencia.
     * Transición automática: Cambia de Pendiente a Asignada y registra trazabilidad.
     */
    public function asignar(): void
    {
          Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/gestion');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $tecnicoId = (int) ($_POST['tecnico_id'] ?? 0);
        $adminUserId = (int) Session::get('usuario_id', 0);

        if ($incidenciaId <= 0 || $tecnicoId <= 0) {
            $this->flash('danger', 'Debe seleccionar una incidencia y un técnico válidos.');
            $this->redirect('index.php?action=gestion');
            return;
        }

        $resultado = $this->service->asignarTecnicoPorAdmin($incidenciaId, $tecnicoId, $adminUserId);

        if ($resultado['exito']) {
            Session::forget('form_data');
            $this->flash('success', $resultado['mensaje']);
        } else {
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect('/gestion');
        return;
    }

    /**
     * Modificación de datos generales de una incidencia (Solo en estados Pendiente o Asignada).
     */
    public function actualizar(): void
    {
         // [REVISION]: Guard de rol y método POST
        Auth::requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/gestion');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $adminUserId = (int) Session::get('usuario_id', 0);

        // [REVISION]: Filtrado de payload para evitar Mass Assignment, Completo para asignación manual de los campos
        $payload = [
            'tecnico_id' => isset($_POST['tecnico_id']) && $_POST['tecnico_id'] !== '' ? (int) $_POST['tecnico_id'] : null,
            'especialidad_id' => isset($_POST['especialidad_id']) ? (int) $_POST['especialidad_id'] : null,
            'descripcion' => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '',
            'direccion' => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : '',
            'fecha_servicio' => is_string($_POST['fecha_servicio'] ?? null) ? trim($_POST['fecha_servicio']) : '',
            'tipo_urgencia' => is_string($_POST['tipo_urgencia'] ?? null) ? trim($_POST['tipo_urgencia']) : '',
            'estado' => is_string($_POST['estado'] ?? null) ? trim($_POST['estado']) : '',
            // [REVISION]: Capturamos la nota o motivo para la columna comentario de historial_incidencias
            'comentario' => is_string($_POST['comentario'] ?? $_POST['observaciones'] ?? null)
                ? trim($_POST['comentario'] ?? $_POST['observaciones']) : ''
        ];

        // Llama al método de modificación manual sin automatizaciones filtrando los campos no modificados para no modificar los actuales
        $resultado = $this->service->modificarPorAdmin($incidenciaId, array_filter($payload, fn($v) => !is_null($v)), $adminUserId);

        if ($resultado['exito']) {
            Session::forget('form_data');
            $this->flash('success', $resultado['mensaje']);
        } else {
            Session::set('form_data', $payload);
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect('/gestion');
        return;
    }

    /**
     * Cancelación de la incidencia por parte del Administrador. //
     */
    public function cancelar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/gestion');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $observacion  = is_string($_POST['observacion'] ?? null) ? trim($_POST['observacion']) : 'Cancelada por el Administrador';
        $adminUserId  = Auth::id() ?? (int) Session::get('usuario_id', 0);

       $resultado = $this->service->cancelarPorAdmin($incidenciaId, $observacion, $adminUserId);

        $resultado = $this->service->cancelarPorAdmin($incidenciaId, $datosActualizar, $adminUserId);

        if ($resultado['exito']) {
            Session::addFlash('success', 'Incidencia cancelada correctamente.');
        } else {
            Session::addFlash('error', $resultado['mensaje']);
        }

        $this->redirect('/gestion');
    }
}