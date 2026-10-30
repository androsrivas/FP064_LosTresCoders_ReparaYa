<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Incidencia;
use App\Services\IncidenciaService;

class IncidenciaController extends Controller
{
    private IncidenciaService $service;

    public function __construct()
    {
        $this->service = new IncidenciaService();
    }

    /**
     * Carga y muestra el listado de servicios del cliente autenticado
     */
    public function misServicios(): void
    {
        $clienteId = (int)($_SESSION['usuario_id'] ?? 0);
        $servicios = Incidencia::obtenerPorCliente($clienteId);

        $this->view('mis_servicios', [
            'servicios' => $servicios
        ]);
    }

    /**
     * Procesa la creación de una incidencia (Cliente / Admin)
     */
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->service->crearIncidencia($_POST);

            if ($resultado['exito']) {
                unset($_SESSION['form_data']);
                $this->flash('success', 'Incidencia registrada con éxito. Localizador: ' . $resultado['localizador']);

                $redirectAction = ($_SESSION['usuario_rol'] ?? '') === 'Administrador' ? 'gestion' : 'mis_servicios';
                $this->redirect("index.php?action={$redirectAction}");
            } else {
                $_SESSION['form_data'] = $_POST;
                $this->flash('danger', $resultado['mensaje']);

                $redirectAction = ($_SESSION['usuario_rol'] ?? '') === 'Administrador' ? 'gestion' : 'mis_servicios';
                $this->redirect("index.php?action={$redirectAction}");
            }
        }
    }

    /**
     * Carga los datos y renderiza la ficha/detalle de una incidencia
     */
    public function verDetalle(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $incidencia = Incidencia::obtenerPorId($id);

        if (!$incidencia) {
            $this->flash('danger', 'Incidencia no encontrada.');
            $this->redirect('index.php?action=mis_servicios');
        }

        $this->view('detalle_servicio', [
            'incidencia' => $incidencia
        ]);
    }

    /**
     * Actualización de estado por parte de un Técnico (Asignada -> En curso -> Finalizada)
     */
    public function cambiarEstadoTecnico(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['incidencia_id'] ?? 0);
            $nuevoEstado = $_POST['nuevo_estado'] ?? '';

            $ok = Incidencia::actualizarEstado($id, $nuevoEstado);

            if ($ok) {
                $this->flash('success', "Estado actualizado a '{$nuevoEstado}' correctamente.");
            } else {
                $this->flash('danger', 'No se pudo actualizar el estado de la incidencia.');
            }

            $this->redirect("index.php?action=detalle_servicio&id={$id}");
        }
    }

    /**
     * Cancelación de incidencia solicitada por el Cliente
     */
    public function cancelarCliente(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $incidenciaId = (int)($_POST['incidencia_id'] ?? 0);
            $clienteId    = (int)($_SESSION['usuario_id'] ?? 0);
            $motivo       = $_POST['motivo'] ?? '';

            $resultado = $this->service->cancelarPorCliente($incidenciaId, $clienteId, $motivo);

            if ($resultado['exito']) {
                $this->flash('success', $resultado['mensaje']);
            } else {
                $this->flash('danger', $resultado['mensaje']);
            }

            $this->redirect('index.php?action=mis_servicios');
        }
    }

    /**
     * Modificación o asignación completa por parte del Administrador
     */
    public function modificarAdmin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $incidenciaId = (int)($_POST['incidencia_id'] ?? 0);
            $adminUserId  = (int)($_SESSION['usuario_id'] ?? 0);

            $resultado = $this->service->modificarPorAdmin($incidenciaId, $_POST, $adminUserId);

            if ($resultado['exito']) {
                unset($_SESSION['form_data']);
                $this->flash('success', $resultado['mensaje']);
            } else {
                $_SESSION['form_data'] = $_POST;
                $this->flash('danger', $resultado['mensaje']);
            }

            $this->redirect('index.php?action=gestion');
        }
    }
}