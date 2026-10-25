<?php

require_once __DIR__ . '/../models/Incidencia.php';
require_once __DIR__ . '/../models/HistorialEstado.php';

class IncidenciaService {
    private $incidenciaModel;
    private $historialModel;

    public function __construct() {
        $this->incidenciaModel = new Incidencia();
        $this->historialModel = new HistorialEstado();
    }

    /**
     * Genera un código localizador único con formato REP-YYYYMMDD-XXXX
     */
    private function generarLocalizador() {
        return 'REP-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    }

    /**
     * Crear una incidencia aplicando la regla de las 48h y registrando el historial inicial
     */
    public function crearIncidencia($datos, $usuarioId) {
        // Regla de antelación mínima de 48 horas para servicios Estándar
        if (($datos['tipo_urgencia'] ?? '') === 'Estándar') {
            $fechaServicio = new DateTime($datos['fecha_servicio']);
            $ahora = new DateTime();
            $diferenciaHoras = ($fechaServicio->getTimestamp() - $ahora->getTimestamp()) / 3600;

            if ($diferenciaHoras < 48) {
                throw new Exception("Las solicitudes estándar deben realizarse con un mínimo de 48 horas de antelación.");
            }
        }

        // Generar localizador único y determinar estado inicial
        $datos['localizador'] = $this->generarLocalizador();
        $estadoInicial = $datos['estado'] ?? 'Pendiente';

        // 1. Insertar la incidencia en la BD
        $incidenciaId = $this->incidenciaModel->crear($datos);

        // 2. Registrar el estado inicial en el historial
        if ($incidenciaId) {
            $this->historialModel->registrar(
                $incidenciaId,
                'Pendiente', // Estado origen
                $estadoInicial,
                $usuarioId,
                'Alta de la solicitud de incidencia'
            );
        }

        return $incidenciaId;
    }

    /**
     * Transición de estado (incluye la acción de cancelar)
     */
    public function cambiarEstado($incidenciaId, $nuevoEstado, $usuarioId, $rolUsuario, $comentario = null) {
        $incidencia = $this->incidenciaModel->obtenerPorId($incidenciaId);
        if (!$incidencia) {
            throw new Exception("La incidencia solicitada no existe.");
        }

        $estadoActual = $incidencia['estado'];

        // Las incidencias cerradas (Finalizada o Cancelada) no admiten más transiciones
        if (in_array($estadoActual, ['Finalizada', 'Cancelada'])) {
            throw new Exception("Una incidencia en estado Finalizada o Cancelada no se puede modificar.");
        }

        // Reglas específicas para cuando un Cliente solicita cancelar
        if ($nuevoEstado === 'Cancelada' && $rolUsuario === 'Cliente') {
            if (!in_array($estadoActual, ['Pendiente', 'Asignada'])) {
                throw new Exception("Solo es posible cancelar solicitudes en estado Pendiente o Asignada.");
            }

            $fechaServicio = new DateTime($incidencia['fecha_servicio']);
            $ahora = new DateTime();
            $diferenciaHoras = ($fechaServicio->getTimestamp() - $ahora->getTimestamp()) / 3600;

            if ($diferenciaHoras <= 48) {
                throw new Exception("No es posible cancelar: deben faltar más de 48 horas para la cita programada.");
            }
        }

        // Actualizar el estado en la base de datos
        $exito = $this->incidenciaModel->actualizarEstado($incidenciaId, $nuevoEstado);

        if ($exito) {
            // Guardar la traza del cambio en el historial
            $this->historialModel->registrar(
                $incidenciaId,
                $estadoActual,
                $nuevoEstado,
                $usuarioId,
                $comentario ?? "Cambio de estado a {$nuevoEstado}"
            );
        }

        return $exito;
    }

    /**
     * Asignar técnico a una incidencia (pasa a estado 'Asignada')
     */
    public function asignarTecnico($incidenciaId, $tecnicoId, $adminUsuarioId) {
        $incidencia = $this->incidenciaModel->obtenerPorId($incidenciaId);
        if (!$incidencia) {
            throw new Exception("Incidencia no encontrada.");
        }

        $estadoAnterior = $incidencia['estado'];
        $exito = $this->incidenciaModel->asignarTecnico($incidenciaId, $tecnicoId);

        if ($exito) {
            $this->historialModel->registrar(
                $incidenciaId,
                $estadoAnterior,
                'Asignada',
                $adminUsuarioId,
                "Técnico asignado (ID Técnico: {$tecnicoId})"
            );
        }

        return $exito;
    }

    /**
     * Edición de datos completos de una incidencia (uso exclusivo para Administrador)
     */
    public function editarPorAdmin($incidenciaId, $datosActualizados, $adminUsuarioId) {
        $incidenciaExistente = $this->incidenciaModel->obtenerPorId($incidenciaId);
        if (!$incidenciaExistente) {
            throw new Exception("La incidencia no existe.");
        }

        $exito = $this->incidenciaModel->actualizarCompleto($incidenciaId, $datosActualizados);

        if ($exito) {
            $this->historialModel->registrar(
                $incidenciaId,
                $incidenciaExistente['estado'],
                $datosActualizados['estado'],
                $adminUsuarioId,
                "Edición de parámetros realizada por Administrador"
            );
        }

        return $exito;
    }

    /**
     * Métodos de consulta delegados al modelo
     */
    public function obtenerDetalle($incidenciaId) {
        return $this->incidenciaModel->obtenerPorId($incidenciaId);
    }

    public function obtenerPorCliente($clienteId) {
        return $this->incidenciaModel->obtenerPorCliente($clienteId);
    }

    public function obtenerPorTecnico($usuarioTecnicoId) {
        return $this->incidenciaModel->obtenerPorUsuarioTecnico($usuarioTecnicoId);
    }

    public function obtenerSinAsignar() {
        return $this->incidenciaModel->obtenerSinAsignar();
    }
}