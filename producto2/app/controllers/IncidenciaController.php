<?php

declare(strict_types=1);

namespace App\Controllers;

<<<<<<< HEAD
<<<<<<< HEAD
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
=======
=======
>>>>>>> origin/main
use App\Core\Auth;
use App\Core\Controller;
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

        $clienteId = (int)Session::get('usuario_id', 0);
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
            $this->redirect('index.php?action=mis_servicios');
            return;
        }

        // [REVISION]: Inyección del cliente_id desde la sesión activa y filtrado de payload (Mass Assignment)
        $rol = (string)Session::get('usuario_rol', '');
        $clienteId = (int)Session::get('usuario_id', 0);

        $payload = [
            'cliente_id'        => ($rol === 'admin' && isset($_POST['cliente_id'])) ? (int)$_POST['cliente_id'] : $clienteId,
            'tipo_servicio_id'  => (int)($_POST['tipo_servicio_id'] ?? 0),
            'descripcion'       => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '',
            'direccion'         => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : ''
        ];

        $resultado = $this->service->crearIncidencia($payload);

        if ($resultado['exito']) {
            Session::forget('form_data');
            $this->flash('success', 'Incidencia registrada con éxito. Localizador: ' . $resultado['localizador']);

            $redirectAction = $rol === 'admin' ? 'gestion' : 'mis_servicios';
            $this->redirect("index.php?action={$redirectAction}");
            return;
        }

        Session::set('form_data', $payload);
        $this->flash('danger', $resultado['mensaje']);

        $redirectAction = $rol === 'admin' ? 'gestion' : 'mis_servicios';
        $this->redirect("index.php?action={$redirectAction}");
        return;
    }

    /**
     * Carga los datos y renderiza la ficha/detalle de una incidencia
     */
    public function verDetalle(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $incidencia = $this->service->obtenerPorId($id);

        if (!$incidencia) {
            $this->flash('danger', 'Incidencia no encontrada.');
            $this->redirect('index.php?action=mis_servicios');
            return;
        }

        // [REVISION]: Guard IDOR (Ownership Check) antes de renderizar
        $usuarioId = (int)Session::get('usuario_id', 0);
        $rol       = (string)Session::get('usuario_rol', '');
        $historial = HistorialEstado::obtenerPorIncidencia($id);

        $esPropietario = ((int)$incidencia['cliente_id'] === $usuarioId);
        $esTecnico     = ((int)($incidencia['tecnico_id'] ?? 0) === $usuarioId);
        $esAdmin       = ($rol === 'admin');

        if (!$esPropietario && !$esTecnico && !$esAdmin) {
            $this->flash('danger', 'No tiene permisos para consultar esta incidencia.');
            $this->redirect('index.php?action=mis_servicios');
            return;
        }

        // [REVISION]: Carga e inyección del historial
        $this->view('detalle_servicio', [
            'incidencia' => $incidencia,
            'historial' => $historial
        ]);
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

        $id = (int)($_POST['incidencia_id'] ?? 0);
        $tecnicoId   = (int)Session::get('usuario_id', 0);

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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->redirect('index.php?action=mis_servicios');
            return;
        }

            $incidenciaId = (int)($_POST['incidencia_id'] ?? 0);
            $clienteId    = (int)Session::get('usuario_id', 0);

            // [REVISION]: Control de TypeError para evitar arrays en strict_types
            $motivo       = is_string($_POST['motivo'] ?? null) ? trim($_POST['motivo']) : '';

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
            
        $incidenciaId = (int)($_POST['incidencia_id'] ?? 0);
        $adminUserId  = (int)Session::get('usuario_id', 0);

        // [REVISION]: Filtrado de payload para evitar Mass Assignment, Completo para asignación manual de los campos
        $payload = [
            'tecnico_id'      => isset($_POST['tecnico_id']) && $_POST['tecnico_id'] !== '' ? (int)$_POST['tecnico_id'] : null,
            'especialidad_id' => isset($_POST['especialidad_id']) ? (int)$_POST['especialidad_id'] : null,
            'descripcion'     => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '',
            'direccion'       => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : '',
            'fecha_servicio'  => is_string($_POST['fecha_servicio'] ?? null) ? trim($_POST['fecha_servicio']) : '',
            'tipo_urgencia'   => is_string($_POST['tipo_urgencia'] ?? null) ? trim($_POST['tipo_urgencia']) : '',
            'estado'          => is_string($_POST['estado'] ?? null) ? trim($_POST['estado']) : ''
            // [REVISION]: Capturamos la nota o motivo para la columna comentario de historial_incidencias
            'comentario'      => is_string($_POST['comentario'] ?? $_POST['observaciones'] ?? null) 
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

        $incidenciaId = (int)($_POST['incidencia_id'] ?? 0);
        $tecnicoId    = (int)($_POST['tecnico_id'] ?? 0);
        $adminUserId  = (int)Session::get('usuario_id', 0);

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
<<<<<<< HEAD
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
>>>>>>> origin/main
}