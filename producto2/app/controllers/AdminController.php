<?php
<<<<<<< HEAD

=======
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
<<<<<<< HEAD
use App\Models\Incidencia;
use App\Core\Session;
use App\Models\HistorialEstado;

class AdminController extends Controller
{
    public function __construct()
    {
        //Restricción de seguridad (comprueba que el usuario ingresa con perfil administrador)
        Auth::requireRole('admin');
    }

    /** Muestra el panel principal de administración */
    public function index(): void
    {
        $this->incidencias();
    }

    /** Listado de incidencias con filtro por estado */
    public function incidencias(): void
    {
        $estadoFiltro = isset($_GET['estado']) ? (string) $_GET['estado'] : 'Todas';
        $incidencias = Incidencia::obtenerPorEstado($estadoFiltro);

        $this->view('admin/index', [
            'titulo'       => 'Gestión de Incidencias',
            'incidencias'  => $incidencias,
            'estadoActual' => $estadoFiltro,
        ]);
    }

    /** 
     * Procesa la creación manual de una incidencia por el Administrador
     */
    public function store(): void
    {
        $this->requireCsrf('/admin/incidencias');

        $clienteId      = (int) $this->input('cliente_id');
        $especialidadId = (int) $this->input('especialidad_id');
        $descripcion    = trim((string) $this->input('descripcion'));
        $direccion      = trim((string) $this->input('direccion'));
        $fechaServicio  = (string) $this->input('fecha_servicio');
        $tipoUrgencia   = (string) $this->input('tipo_urgencia');

        if ($tipoUrgencia === 'Estándar') {
            $horasAntelacion = (strtotime($fechaServicio) - time()) / 3600; 
            if ($horasAntelacion < 48) {
                Session::addFlash('error', 'Las solicitudes estándar requieren un mínimo de 48 horas de antelación.');
                $this->redirect('/admin/incidencias');
            }
        }

        $localizador = Incidencia::generarLocalizador();
        $adminUser   = Auth::user();

        $incidenciaId = Incidencia::crear([
            'localizador'     => $localizador,
            'cliente_id'      => $clienteId,
            'especialidad_id' => $especialidadId,
            'descripcion'    => $descripcion,
            'direccion'      => $direccion,
            'fecha_servicio'  => $fechaServicio,
            'tipo_urgencia'   => $tipoUrgencia,
            'estado'          => 'Pendiente'
        ]);

        if ($incidenciaId > 0) {
            HistorialEstado::crear(
                $incidenciaId, 
                null, 
                'Pendiente', 
                (int) $adminUser['id'], 
                'Incidencia dada de alta por el Administrador'
            );
            Session::addFlash('success', "Incidencia {$localizador} creada correctamente.");
        } else {
            Session::addFlash('error', 'Error al crear la incidencia.');
        }

        $this->redirect('/admin/incidencias');
    }

    /**
     * Asignar un técnico a una incidencia
     * Regla: Solo si la incidencia está en 'Pendiente' o 'Asignada'.
     */
    public function asignar(): void
    {
        $this->requireCsrf('/admin/incidencias');

        $incidenciaId = (int) $this->input('incidencia_id');
        $tecnicoId    = (int) $this->input('tecnico_id');
        $adminUser    = Auth::user();

        $incidencia = Incidencia::obtenerPorId($incidenciaId);
        if (!$incidencia) {
            Session::addFlash('error', 'La incidencia no existe.');
            $this->redirect('/admin/incidencias');
        }

        $estadoAnterior = $incidencia['estado'];

        //Validación de transición
        if (!in_array($estadoAnterior, ['Pendiente', 'Asignada'], true)) {
            Session::addFlash('error', "No se puede asignar un técnico en estado '{$estadoAnterior}'.");
            $this->redirect('/admin/incidencias');
        }

        //Asignar en BBDD (cambia el estado a 'Asignada')
        Incidencia::asignarTecnico($incidenciaId, $tecnicoId);

        //Auditoría en el historial
        HistorialEstado::crear(
            $incidenciaId,
            $estadoAnterior,
            'Asignada',
            (int) $adminUser['id'],
            'Técnico asignado por el Administrador'
        );

        Session::addFlash('success', 'Técnico asignado correctamente.');
        $this->redirect('/admin/incidencias');
    }

    /**
     * Modificar datos de una incidencia
     * Regla: Solo si la incidencia está en 'Pendiente' o 'Asignada'
     */
    public function actualizar(): void
    {
        $this->requireCsrf('/admin/incidencias');

        $incidenciaId = (int) $this->input('incidencia_id');
        $adminUser    = Auth::user();

        $incidencia = Incidencia::obtenerPorId($incidenciaId);
        if (!$incidencia) {
            Session::addFlash('error', 'La incidencia no existe.');
            $this->redirect('/admin/incidencias');
        }

        $estadoAnterior = $incidencia['estado'];

        //Validación de transición
        if (!in_array($estadoAnterior, ['Pendiente', 'Asignada'], true)) {
            Session::addFlash('error', "No se pueden modificar incidencias en estado '{$estadoAnterior}'.");
            $this->redirect('/admin/incidencias');
        }

        $datosActualizados = [
            'especialidad_id' => (int) $this->input('especialidad_id'),
            'descripcion'     => trim((string) $this->input('descripcion')),
            'direccion'       => trim((string) $this->input('direccion')),
            'fecha_servicio'   => (string) $this->input('fecha_servicio'),
            'tipo_urgencia'    => (string) $this->input('tipo_urgencia'),
            'estado'          => $estadoAnterior, // Mantiene el estado
            'tecnico_id'      => $incidencia['tecnico_id']
        ];

        Incidencia::actualizarIncidencia($incidenciaId, $datosActualizados);

        //Auditoría opcional de modificación
        HistorialEstado::crear(
            $incidenciaId,
            $estadoAnterior,
            $estadoAnterior,
            (int) $adminUser['id'],
            'Datos de la incidencia modificados por el Administrador'
        );

        Session::addFlash('success', 'Incidencia modificada correctamente.');
        $this->redirect('/admin/incidencias');
    }

    /**
     * Cancelación de incidencias por el Administrador
     * Regla: Se permite cancelar solo si la incidencia está en 'Pendiente' o 'Asignada'
     */
    public function cancelar(): void
    {
        $this->requireCsrf('/admin/incidencias');

        $incidenciaId = (int) $this->input('incidencia_id');
        $observacion  = trim((string) $this->input('observacion', 'Cancelada por el administrador'));
        $adminUser    = Auth::user();

        $incidencia = Incidencia::obtenerPorId($incidenciaId);
        if (!$incidencia) {
            Session::addFlash('error', 'La incidencia no existe.');
            $this->redirect('/admin/incidencias');
        }

        $estadoAnterior = $incidencia['estado'];

        if (!in_array($estadoAnterior, ['Pendiente', 'Asignada'], true)) {
            Session::addFlash('error', "No se puede cancelar una incidencia en estado '{$estadoAnterior}'.");
            $this->redirect('/admin/incidencias');
        }

        Incidencia::actualizarEstado($incidenciaId, 'Cancelada');

        HistorialEstado::crear(
            $incidenciaId,
            $estadoAnterior,
            'Cancelada',
            (int) $adminUser['id'],
            $observacion
        );

        Session::addFlash('success', 'Incidencia cancelada correctamente.');
        $this->redirect('/admin/incidencias');
=======
use App\Core\Roles;

final class AdminController extends Controller
{
    public function __construct() 
    {
        Auth::requireRole(Roles::ADMIN);
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
    }
}