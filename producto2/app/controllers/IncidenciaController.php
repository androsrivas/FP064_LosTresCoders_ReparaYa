<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;
use App\Core\Session;
use App\Models\HistorialEstado;
use App\Services\IncidenciaService;

class IncidenciaController extends Controller
{
    private IncidenciaService $service;

    public function __construct()
    {
        Auth::requireRole(Roles::PARTICULAR);

        $this->service = new IncidenciaService();
    }

    /**
     * Carga y muestra el listado de servicios del cliente autenticado
     */
    public function misServicios(): void
    {
        // [REVISION]: Guard de rol y sustitución de $_SESSION por Session::get()
        Auth::requireRole('Particular');

        $clienteId = (int) Session::get('usuario_id', 0);
        // [REVISION]: Canalización de la consulta a través del Servicio
        $servicios = $this->service->obtenerPorCliente($clienteId);

        $this->view('mis_servicios', [
            'servicios' => $servicios
        ]);
    }

    /**
     * Procesa la creación de una incidencia (Cliente / Admin)
     */
    public function crear(): void
    {
        // [REVISION]: Evitar pantallas en blanco si la petición no es POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $referer = $_SERVER['HTTP_REFERER'] ?? '/perfil';
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

        $redirectAction = $rol === 'admin' ? 'gestion' : '';
        $this->redirect("/{$redirectAction}");
        return;
    }

    /**
     * Carga los datos y renderiza la ficha/detalle de una incidencia
     */
    public function verDetalle($id = 0): void
    {
        // 1. Asegurar sesión activa si no se usó middleware
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = (int) $id;
        if ($id <= 0) {
            $id = (int) ($_GET['id'] ?? 0);
        }

        $incidencia = $this->service->obtenerPorId($id);

        if (!$incidencia) {
            http_response_code(404);
            echo '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Incidencia no encontrada.</div>';
            return;
        }

        // 2. Obtención de usuario utilizando la clase Auth o revisando claves alternativas de la sesión
        $usuarioId = (int) (
            Auth::id()
            ?? Auth::user()['id']
            ?? Session::get('usuario_id')
            ?? Session::get('user_id')
            ?? ($_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0)
        );

        $rol = (string) (
            Auth::user()['rol']
            ?? Session::get('usuario_rol')
            ?? Session::get('user_rol')
            ?? ($_SESSION['usuario_rol'] ?? $_SESSION['user_rol'] ?? $_SESSION['user']['rol'] ?? '')
        );

        $clienteIdIncidencia = (int) ($incidencia['cliente_id'] ?? $incidencia['usuario_id'] ?? 0);
        $tecnicoIdIncidencia = (int) ($incidencia['tecnico_id'] ?? 0);

        $esPropietario = ($clienteIdIncidencia === $usuarioId && $usuarioId > 0);
        $esTecnico = ($tecnicoIdIncidencia === $usuarioId && $usuarioId > 0);
        $esAdmin = (strtolower($rol) === 'admin');

        if (!$esPropietario && !$esTecnico && !$esAdmin) {
            http_response_code(403);
            echo '<div class="alert alert-danger mb-0"><i class="bi bi-shield-lock me-2"></i>No tiene permisos para consultar esta incidencia.</div>';
            return;
        }

        $historial = HistorialEstado::obtenerPorIncidencia($id) ?? [];
        // $mensajes = $this->service->obtenerMensajesForo($id) ?? [];

        $userRol = $rol;
        $userId = $usuarioId;

        require __DIR__ . '/../views/incidencias/_contenido_detalle.php';
    }

    /**
     * Actualización de estado por parte de un Técnico (Asignada -> En curso -> Finalizada)
     */
    public function cambiarEstadoTecnico(): void
    {
        // [REVISION]: Guard de rol y método POST
        Auth::requireRole('Tecnico');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=mis_servicios');
            return;
        }

        $id = (int) ($_POST['incidencia_id'] ?? 0);
        $tecnicoId = (int) Session::get('usuario_id', 0);

        // [REVISION]: Control de TypeError para evitar arrays en strict_types
        $nuevoEstado = is_string($_POST['nuevo_estado'] ?? null) ? trim($_POST['nuevo_estado']) : '';

        // [REVISION]: Delegación completa al Servicio (máquina de estados + trazabilidad + asignación)
        $resultado = $this->service->cambiarEstadoPorTecnico($id, $tecnicoId, $nuevoEstado);

        if ($resultado['exito']) {
            $this->flash('success', $resultado['mensaje']);
        } else {
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect("index.php?action=detalle_servicio&id={$id}");
        return;
    }

    /**
     * Cancelación de incidencia solicitada por el Cliente
     */
    public function cancelarCliente(): void
    {
        // [REVISION]: Guard de rol y método POST
        Auth::requireRole('Particular');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=mis_servicios');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $clienteId = (int) Session::get('usuario_id', 0);

        // [REVISION]: Control de TypeError para evitar arrays en strict_types
        $motivo = is_string($_POST['motivo'] ?? null) ? trim($_POST['motivo']) : '';

        $resultado = $this->service->cancelarPorCliente($incidenciaId, $clienteId, $motivo);

        if ($resultado['exito']) {
            $this->flash('success', $resultado['mensaje']);
        } else {
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect('index.php?action=mis_servicios');
        return;

    }

    /**
     * Modificación o asignación completa por parte del Administrador
     */
    public function modificarAdmin(): void
    {
        // [REVISION]: Guard de rol y método POST
        Auth::requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=gestion');
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

        $this->redirect('index.php?action=gestion');
        return;
    }

    /**
     * Asignación rápida de técnico por el Administrador.
     */
    public function asignarTecnicoAdmin(): void
    {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?action=gestion');
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

        $this->redirect('index.php?action=gestion');
        return;
    }
}