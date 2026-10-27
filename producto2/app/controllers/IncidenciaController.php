<?php

require_once __DIR__ . '/../services/IncidenciaService.php';
require_once __DIR__ . '/../models/Incidencia.php';

class IncidenciaController
{
    private $service;
    private $incidenciaModel;

    public function __construct(PDO $pdo)
    {
        $this->service = new IncidenciaService($pdo);
        $this->incidenciaModel = new Incidencia();
    }

    /**
     * Procesa la creación de una incidencia (Cliente / Admin)
     */
    public function crear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Asegurar que exista sesión iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $resultado = $this->service->crearIncidencia($_POST);

            if ($resultado['exito']) {
                // Éxito: limpiamos datos temporales de formulario y redirigimos
                unset($_SESSION['form_data']);
                $_SESSION['exito_incidencia'] = 'Incidencia registrada con éxito con el localizador: ' . $resultado['localizador'];

                // Redirigir según el rol o la vista solicitada (mis_servicios o gestion)
                $redirectAction = ($_SESSION['usuario_rol'] ?? '') === 'Administrador' ? 'gestion' : 'mis_servicios';
                header("Location: /producto2/public/index.php?action={$redirectAction}");
                exit;
            } else {
                // Error: Preservamos los datos introducidos y el mensaje para el modal en la View
                $_SESSION['error_incidencia'] = $resultado['mensaje'];
                $_SESSION['form_data']        = $_POST;

                $redirectAction = ($_SESSION['usuario_rol'] ?? '') === 'Administrador' ? 'gestion' : 'mis_servicios';
                header("Location: /producto2/public/index.php?action={$redirectAction}");
                exit;
            }
        }
    }

    /**
     * Carga los datos necesarios para la vista de detalle de una incidencia
     */
    public function verDetalle(int $id): array
    {
        $incidencia = $this->incidenciaModel->obtenerPorId($id);

        if (!$incidencia) {
            return ['error' => 'Incidencia no encontrada.'];
        }

        return ['incidencia' => $incidencia];
    }

    /**
     * Procesa la actualización de estado por parte de un Técnico (Asignada -> En curso -> Finalizada)
     */
    public function cambiarEstadoTecnico(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $id = (int)$_POST['incidencia_id'];
            $nuevoEstado = $_POST['nuevo_estado'];

            $ok = $this->incidenciaModel->actualizarEstado($id, $nuevoEstado);

            if ($ok) {
                $_SESSION['exito_incidencia'] = "Estado actualizado a '{$nuevoEstado}' correctamente.";
            } else {
                $_SESSION['error_incidencia'] = 'No se pudo actualizar el estado de la incidencia.';
            }

            header("Location: /producto2/public/index.php?action=detalle_servicio&id={$id}");
            exit;
        }
    }

    /**
     * Cancelación de incidencia solicitada por el Cliente (mínimo 48h de antelación)
     */
    public function cancelarCliente(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $incidenciaId = (int)$_POST['incidencia_id'];
            $clienteId    = (int)($_SESSION['usuario_id'] ?? 0);
            $motivo       = $_POST['motivo'] ?? '';

            $resultado = $this->service->cancelarPorCliente($incidenciaId, $clienteId, $motivo);

            if ($resultado['exito']) {
                $_SESSION['exito_incidencia'] = $resultado['mensaje'];
            } else {
                $_SESSION['error_incidencia'] = $resultado['mensaje'];
            }

            header("Location: /producto2/public/index.php?action=mis_servicios");
            exit;
        }
    }

    /**
     * Modificación o asignación completa por parte del Administrador de Negocio
     */
    public function modificarAdmin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $incidenciaId = (int)$_POST['incidencia_id'];
            $adminUserId  = (int)($_SESSION['usuario_id'] ?? 0);

            $resultado = $this->service->modificarPorAdmin($incidenciaId, $_POST, $adminUserId);

            if ($resultado['exito']) {
                $_SESSION['exito_incidencia'] = $resultado['mensaje'];
                unset($_SESSION['form_data']);
            } else {
                $_SESSION['error_incidencia'] = $resultado['mensaje'];
                $_SESSION['form_data']        = $_POST;
            }

            header("Location: /producto2/public/index.php?action=gestion");
            exit;
        }
    }
}