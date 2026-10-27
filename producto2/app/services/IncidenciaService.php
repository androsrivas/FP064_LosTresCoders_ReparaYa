<?php

require_once __DIR__ . '/../models/Incidencia.php';
require_once __DIR__ . '/../models/HistorialEstado.php';

class IncidenciaService
{
    private $incidenciaModel;
    private $historialModel;

    public function __construct(PDO $pdo)
    {
        $this->incidenciaModel = new Incidencia();
        $this->historialModel = new HistorialEstado($pdo);
    }

    /**
     * Registra una nueva incidencia aplicando reglas de fecha y localizador.
     */
    public function crearIncidencia(array $datos): array
    {
        // 1. Validar regla de 48 horas de antelación si no es urgente
        if (($datos['tipo_urgencia'] ?? 'Estandar') !== 'Urgente') {
            $fechaServicio = new DateTime($datos['fecha_servicio']);
            $ahora = new DateTime();
            $diferenciaHoras = ($fechaServicio->getTimestamp() - $ahora->getTimestamp()) / 3600;

            if ($diferenciaHoras < 48) {
                return [
                    'exito' => false, 
                    'mensaje' => 'Las solicitudes estándar requieren un mínimo de 48 horas de antelación.'
                ];
            }
        }

        // 2. Generar localizador único (ej. REP-20261027-A1B2)
        $datos['localizador'] = 'REP-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $datos['estado'] = 'Pendiente';

        // 3. Crear incidencia
        $idCreated = $this->incidenciaModel->crear($datos);

        if ($idCreated) {
            // Trazabilidad inicial
            $this->historialModel->registrar(
                $idCreated,
                null,
                'Pendiente',
                $datos['cliente_id'],
                'Incidencia registrada por el cliente.'
            );

            return ['exito' => true, 'id' => $idCreated, 'localizador' => $datos['localizador']];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo registrar la incidencia.'];
    }

    /**
     * Cancela una incidencia iniciada por el Cliente.
     */
    public function cancelarPorCliente(int $incidenciaId, int $clienteId, string $motivo = ''): array
    {
        $incidencia = $this->incidenciaModel->obtenerPorId($incidenciaId);

        if (!$incidencia || $incidencia['cliente_id'] != $clienteId) {
            return ['exito' => false, 'mensaje' => 'Incidencia no encontrada.'];
        }

        // Solo cancelable en Pendiente o Asignada
        if (!in_array($incidencia['estado'], ['Pendiente', 'Asignada'])) {
            return ['exito' => false, 'mensaje' => 'No se puede cancelar una incidencia en estado ' . $incidencia['estado'] . '.'];
        }

        // Verificar 48 horas de antelación
        $fechaServicio = new DateTime($incidencia['fecha_servicio']);
        $ahora = new DateTime();
        $horasRestantes = ($fechaServicio->getTimestamp() - $ahora->getTimestamp()) / 3600;

        if ($horasRestantes <= 48) {
            return ['exito' => false, 'mensaje' => 'No es posible cancelar con menos de 48 horas de antelación.'];
        }

        // Ejecutar cancelación
        $estadoAnterior = $incidencia['estado'];
        $ok = $this->incidenciaModel->actualizarEstado($incidenciaId, 'Cancelada');

        if ($ok) {
            $this->historialModel->registrar(
                $incidenciaId,
                $estadoAnterior,
                'Cancelada',
                $clienteId,
                'Cancelada por el cliente. Motivo: ' . ($motivo ?: 'Sin motivo especificado')
            );
            return ['exito' => true, 'mensaje' => 'Incidencia cancelada correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'Error al procesar la cancelación.'];
    }

    /**
     * Asigna un técnico desde el Administrador de Negocio.
     */
    public function asignarTecnicoPorAdmin(int $incidenciaId, int $tecnicoId, int $adminUserId): array
    {
        $incidencia = $this->incidenciaModel->obtenerPorId($incidenciaId);

        if (!$incidencia) {
            return ['exito' => false, 'mensaje' => 'Incidencia no encontrada.'];
        }

        // Restricción: Administrador solo puede intervenir si está en Pendiente o Asignada
        if (!in_array($incidencia['estado'], ['Pendiente', 'Asignada'])) {
            return ['exito' => false, 'mensaje' => 'Solo se pueden asignar técnicos a incidencias en estado Pendiente o Asignada.'];
        }

        $estadoAnterior = $incidencia['estado'];
        $ok = $this->incidenciaModel->asignarTecnico($incidenciaId, $tecnicoId);

        if ($ok) {
            $this->historialModel->registrar(
                $incidenciaId,
                $estadoAnterior,
                'Asignada',
                $adminUserId,
                "Técnico ID {$tecnicoId} asignado por el Administrador de Negocio."
            );
            return ['exito' => true, 'mensaje' => 'Técnico asignado correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'Error al asignar el técnico.'];
    }

    /**
     * Modificación de la incidencia por el Administrador de Negocio.
     */
    public function modificarPorAdmin(int $incidenciaId, array $datosAprobar, int $adminUserId): array
    {
        $incidencia = $this->incidenciaModel->obtenerPorId($incidenciaId);

        if (!$incidencia) {
            return ['exito' => false, 'mensaje' => 'Incidencia no encontrada.'];
        }

        // Restricción explícita: Modificaciones/cancelaciones por el admin solo en Pendiente o Asignada
        if (!in_array($incidencia['estado'], ['Pendiente', 'Asignada'])) {
            return ['exito' => false, 'mensaje' => 'El administrador solo puede modificar incidencias en estado Pendiente o Asignada.'];
        }

        $estadoAnterior = $incidencia['estado'];
        $ok = $this->incidenciaModel->actualizarIncidencia($incidenciaId, $datosAprobar);

        if ($ok) {
            $this->historialModel->registrar(
                $incidenciaId,
                $estadoAnterior,
                $datosAprobar['estado'],
                $adminUserId,
                'Incidencia modificada por el Administrador de Negocio.'
            );
            return ['exito' => true, 'mensaje' => 'Incidencia actualizada con éxito.'];
        }

        return ['exito' => false, 'mensaje' => 'Error al actualizar la incidencia.'];
    }
}