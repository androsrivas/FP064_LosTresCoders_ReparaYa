<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Incidencia;
use App\Models\HistorialEstado;
use App\Models\Tecnico;
use DateTime;

class IncidenciaService
{
    // Constantes para el ENUM tipo_urgencia
        public const URGENCIA_ESTANDAR = 'Estándar';
        public const URGENCIA_URGENTE  = 'Urgente';

        // Constantes para el ENUM estado
        public const ESTADO_PENDIENTE  = 'Pendiente';
        public const ESTADO_ASIGNADA   = 'Asignada';
        public const ESTADO_FINALIZADA = 'Finalizada';
        public const ESTADO_CANCELADA  = 'Cancelada';
        
    /**
     * Registra una nueva incidencia aplicando reglas de fecha y localizador.
     */
    public function crearIncidencia(array $datos): array
    {
        // [REVISION] Normalizamos tipo_urgencia con la constante
        $tipoUrgencia = $datos['tipo_urgencia'] ?? self::URGENCIA_ESTANDAR;

        // [REVISION] Validar presencia de fecha de servicio y formato valido
        if (empty($datos['fecha_servicio'])) {
            return ['exito' => false, 'mensaje' => 'La fecha de servicio es obligatoria.'];
        }

        // [REVISION] Parseo seguro de fechas para evitar Error 500 ante formatos inválidos
        try {
            $fechaServicio = new DateTime($datos['fecha_servicio']);
            $ahora = new DateTime();
        } catch (\Exception $e) {
            return ['exito' => false, 'mensaje' => 'El formato de la fecha de servicio no es válido.'];
        }

        // [REVISION] Regla general: no se permiten fechas en el pasado (aplica tanto a Estándar como a Urgente)
        if ($fechaServicio < $ahora) {
            return ['exito' => false, 'mensaje' => 'La fecha de servicio no puede ser en el pasado.'];
        }

        // 1. Validar regla de 48 horas de antelación si no es urgente
        if ($tipoUrgencia !== self::URGENCIA_URGENTE) {
            $diferenciaHoras = ($fechaServicio->getTimestamp() - $ahora->getTimestamp()) / 3600;

            if ($diferenciaHoras <= 48) {
                return [
                    'exito'   => false, 
                    'mensaje' => 'Las solicitudes estándar requieren un mínimo de 48 horas de antelación.'
                ];
            }
        }
        $datos['tipo_urgencia'] = $tipoUrgencia;
        $datos['estado'] = self::ESTADO_PENDIENTE;

        // [REVISION] Validación y formateo explícito de cliente_id
        $clienteId = (int)($datos['cliente_id'] ?? 0);
        if ($clienteId <= 0) {
            return ['exito' => false, 'mensaje' => 'El ID del cliente no es válido.'];
        }

        //[REVISION] Cambiado a 19 caracteres con 6 hex y bucle con 5 intentos
        // 2. Generar localizador único (ej. REP-20261101-A1B2C3)
        $intentos = 0;
        $maxIntentos = 5;

        do {
            $intentos++;
            $hashHex = strtoupper(bin2hex(random_bytes(3))); // 6 caracteres hex
            $datos['localizador'] = 'REP-' . date('Ymd') . '-' . $hashHex;
            
            try {
                // [REVISION] Implementación de transacción para atomicidad entre crear() e HistorialEstado::registrar()
                Incidencia::beginTransaction();

                // 3. Crear incidencia mediante la llamada estática al modelo
                $idCreated = Incidencia::crear($datos);

                if ($idCreated > 0) {
                    // Trazabilidad inicial
                    HistorialEstado::registrar(
                        $idCreated,
                        null,
                        self::ESTADO_PENDIENTE,
                        $clienteId,
                        'Incidencia registrada por el cliente.'
                    );

                    // [REVISION] Confirmar transacción si ambas operaciones son compuestas
                    Incidencia::commit();

                    return [
                        'exito'       => true, 
                        'id'          => $idCreated, 
                        'localizador' => $datos['localizador']
                    ];
                }

                // [REVISION] Cancelar transacción si falla la creación
                Incidencia::rollBack();

            } catch (\PDOException $e) {
                // [REVISION] Rollback en captura de PDO
                Incidencia::rollBack();

                // Si colisiona la restricción UNIQUE (código 23000/1062) reintentamos
                if ($e->getCode() === '23000' && $intentos < $maxIntentos) {
                    continue;
                }
                catch (\Exception $e) {
                    // [REVISION] Rollback ante cualquier otra excepción imprevista
                    Incidencia::rollBack();
                    return ['exito' => false, 'mensaje' => 'Error al procesar el registro de la incidencia.'];
                }
            }
        } while ($intentos < $maxIntentos);
    }

    /**
     * Cancela una incidencia iniciada por el Cliente.
     */
    public function cancelarPorCliente(int $incidenciaId, int $clienteId, string $motivo = ''): array
    {
        $incidencia = Incidencia::obtenerPorId($incidenciaId);

        if (!$incidencia || $incidencia['cliente_id'] != $clienteId) {
            return ['exito' => false, 'mensaje' => 'Incidencia no encontrada.'];
        }

        // Solo cancelable en Pendiente o Asignada
        if (!in_array($incidencia['estado'], ['Pendiente', 'Asignada'], true)) {
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
        $filasAfectadas = Incidencia::actualizarEstado($incidenciaId, 'Cancelada');

        if ($filasAfectadas > 0) {
            HistorialEstado::registrar(
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
        $incidencia = Incidencia::obtenerPorId($incidenciaId);

        if (!$incidencia) {
            return ['exito' => false, 'mensaje' => 'Incidencia no encontrada.'];
        }

        // Restricción: Administrador solo puede intervenir si está en Pendiente o Asignada
        if (!in_array($incidencia['estado'], ['Pendiente', 'Asignada'], true)) {
            return ['exito' => false, 'mensaje' => 'Solo se pueden asignar técnicos a incidencias en estado Pendiente o Asignada.'];
        }

        // [REVISION] Validación de existencia del técnico y coincidencia de especialidad
        $tecnico = Tecnico::obtenerPorId($tecnicoId);

        if (!$tecnico) {
            return ['exito' => false, 'mensaje' => 'El técnico seleccionado no existe.'];
        }

        if ((int)$tecnico['especialidad_id'] !== (int)$incidencia['especialidad_id']) {
            return ['exito' => false, 'mensaje' => 'La especialidad del técnico no coincide con la especialidad de la incidencia.'];
        }

        $estadoAnterior = $incidencia['estado'];
        $filasAfectadas = Incidencia::asignarTecnico($incidenciaId, $tecnicoId);

        if ($filasAfectadas > 0) {
            HistorialEstado::registrar(
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
        $incidencia = Incidencia::obtenerPorId($incidenciaId);

        if (!$incidencia) {
            return ['exito' => false, 'mensaje' => 'Incidencia no encontrada.'];
        }

        // Restricción explícita: Modificaciones/cancelaciones por el admin solo en Pendiente o Asignada
        if (!in_array($incidencia['estado'], ['Pendiente', 'Asignada'], true)) {
            return ['exito' => false, 'mensaje' => 'El administrador solo puede modificar incidencias en estado Pendiente o Asignada.'];
        }

        $estadoAnterior = $incidencia['estado'];
        $filasAfectadas = Incidencia::actualizarIncidencia($incidenciaId, $datosAprobar);

        if ($filasAfectadas > 0) {
            HistorialEstado::registrar(
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



    // [REVISION] Añadida función para convertir tecnico_id al hacer la consulta obtenerPorTecnico() del model Incidencia
    /**
     * Conversión de tecnico_id a usuario_id para mostrar las incidencias asociadas a un técnico
     */
    public function obtenerPorUsuarioTecnico(int $usuarioId): array
    {
        // 1. Traduce el ID de usuario al ID del técnico
        $tecnicoId = Tecnico::obtenerIdPorUsuario($usuarioId);

        if (!$tecnicoId) {
            return [];
        }

        // 2. Consulta las incidencias usando el ID de técnico real
        return Incidencia::obtenerPorTecnico($tecnicoId);
    }
}