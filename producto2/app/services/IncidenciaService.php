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
                Model::beginTransaction();

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
                    Model::commit();

                    return [
                        'exito'       => true, 
                        'id'          => $idCreated, 
                        'localizador' => $datos['localizador']
                    ];
                }

                // [REVISION] Cancelar transacción si falla la creación
                Model::rollBack();

            } catch (\PDOException $e) {
                // [REVISION] Rollback en captura de PDO
                Model::rollBack();

                // Si colisiona la restricción UNIQUE (código 23000/1062) reintentamos
                if ($e->getCode() === '23000' && $intentos < $maxIntentos) {
                    continue;
                }
                catch (\Exception $e) {
                    // [REVISION] Rollback ante cualquier otra excepción imprevista
                    Model::rollBack();
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

        // [REVISION] El maldito strict mode
        if (!$incidencia || (int)$incidencia['cliente_id'] != $clienteId) {
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
        
        // [REVISION] Transaccion y comprobacion
        try {
            Model::beginTransaction();

            // Usamos la constante en lugar del string hardcodeado 'Cancelada'
            $resultadoUpdate = Incidencia::actualizarEstado($incidenciaId, self::ESTADO_CANCELADA);

            if ($resultadoUpdate === false) {
                Model::rollBack();
                return ['exito' => false, 'mensaje' => 'Error al procesar la cancelación.'];
            }

            // Registrar el cambio de estado en el historial
            HistorialEstado::registrar(
                $incidenciaId,
                $estadoAnterior,
                self::ESTADO_CANCELADA,
                $clienteId,
                !empty($motivo) ? $motivo : 'Incidencia cancelada por el cliente.'
            );

            Model::commit();

            return ['exito' => true, 'mensaje' => 'Incidencia cancelada con éxito.'];

        } catch (\PDOException $e) {
            Model::rollBack();
            return ['exito' => false, 'mensaje' => 'Error de base de datos al cancelar la incidencia.'];
        } catch (\Exception $e) {
            Model::rollBack();
            return ['exito' => false, 'mensaje' => 'Error inesperado al procesar la cancelación.'];
        }
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
        if (!in_array($incidencia['estado'], [self::ESTADO_PENDIENTE, self::ESTADO_ASIGNADA], true)) {
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
        try {
            Model::beginTransaction();

            $filasAfectadas = Incidencia::asignarTecnico($incidenciaId, $tecnicoId);

            if ($filasAfectadas <= 0) {
                Model::rollBack();
                return ['exito' => false, 'mensaje' => 'Error al asignar el técnico.'];
            }

            // Registramos el cambio en la trazabilidad
            HistorialEstado::registrar(
                $incidenciaId,
                $estadoAnterior,
                self::ESTADO_ASIGNADA,
                $adminUserId,
                "Técnico ID {$tecnicoId} asignado por el Administrador."
            );

            Model::commit();

            return ['exito' => true, 'mensaje' => 'Técnico asignado correctamente.'];

        } catch (\PDOException $e) {
            Model::rollBack();
            return ['exito' => false, 'mensaje' => 'Error de base de datos al asignar el técnico.'];
        } catch (\Exception $e) {
            Model::rollBack();
            return ['exito' => false, 'mensaje' => 'Error inesperado al asignar el técnico.'];
        }
    }

    /**
     * Modificación de la incidencia por el Administrador de Negocio. Permite cambios de campos generales
     * y transiciones de estado restringidas a Asignada o Cancelada.
     */
    public function modificarPorAdmin(int $incidenciaId, array $datosAprobar, int $adminUserId): array
    {
        $incidencia = Incidencia::obtenerPorId($incidenciaId);

        if (!$incidencia) {
            return ['exito' => false, 'mensaje' => 'Incidencia no encontrada.'];
        }

        // Restricción explícita: Modificaciones/cancelaciones por el admin solo en Pendiente o Asignada
        if (!in_array($incidencia['estado'], [self::ESTADO_PENDIENTE, self::ESTADO_ASIGNADA], true)) {
            return ['exito' => false, 'mensaje' => 'El administrador solo puede modificar incidencias en estado Pendiente o Asignada.'];
        }

        // [REVISION]: Validar que el nuevo estado sea válido (Asignada o Cancelada) si viene en el payload
        if (!empty($datosAprobar['estado']) && $datosAprobar['estado'] !== $incidencia['estado']) {
            $estadoSolicitado = $datosAprobar['estado'];
            $estadosPermitidosAdmin = [self::ESTADO_ASIGNADA, self::ESTADO_CANCELADA];

            if (!in_array($estadoSolicitado, $estadosPermitidosAdmin, true)) {
                return ['exito' => false, 'mensaje' => 'El administrador solo puede cambiar el estado a Asignada o Cancelada.'];
            }

            if ($estadoSolicitado === self::ESTADO_ASIGNADA) {
                $tecnicoIdEvaluado = !empty($datosAprobar['tecnico_id']) ? $datosAprobar['tecnico_id'] : $incidencia['tecnico_id'];
                if (empty($tecnicoIdEvaluado)) {
                    return ['exito' => false, 'mensaje' => 'No se puede cambiar el estado a Asignada sin asignar un técnico.'];
                }
            }
        }

        // 1. Determinar especialidad resultante (si cambia en el form o se mantiene la actual)
        $especialidadId = isset($datosAprobar['especialidad_id']) 
            ? (int)$datosAprobar['especialidad_id'] 
            : (int)$incidencia['especialidad_id'];

        // 2. Si se indica/modifica un técnico, se valida formalmente pero NO se altera el estado automáticamente
        if (!empty($datosAprobar['tecnico_id'])) {
            $tecnicoId = (int)$datosAprobar['tecnico_id'];
            $tecnico = Tecnico::obtenerPorId($tecnicoId);

            if (!$tecnico) {
                return ['exito' => false, 'mensaje' => 'El técnico seleccionado no existe.'];
            }

            if ((int)$tecnico['especialidad_id'] !== (int)$incidencia['especialidad_id']) {
                return ['exito' => false, 'mensaje' => 'La especialidad del técnico no coincide con la de la incidencia.'];
            }
        }

        // 3. Validación de la fecha de servicio si se modifica
        if (!empty($datosAprobar['fecha_servicio'])) {
            try {
                $fechaServicio = new DateTime($datosAprobar['fecha_servicio']);
                if ($fechaServicio < new DateTime()) {
                    return ['exito' => false, 'mensaje' => 'La fecha de servicio no puede ser en el pasado.'];
                }
            } catch (\Exception $e) {
                return ['exito' => false, 'mensaje' => 'El formato de la fecha de servicio no es válido.'];
            }
        }

        // [REVISION]: Obtenemos el comentario para la columna 'comentario' de historial_incidencias
        $comentarioFinal = !empty($datosAprobar['comentario']) 
            ? $datosAprobar['comentario'] : 'Incidencia modificada manualmente por el Administrador.';


        $estadoAnterior = $incidencia['estado'];
       // Si no se envía estado nuevo en el payload manual, se preserva el anterior
        $nuevoEstado = $datosAprobar['estado'] ?? $estadoAnterior;

        try {
            Model::beginTransaction();

            $filasAfectadas = Incidencia::actualizarIncidencia($incidenciaId, $datosAprobar);

            // [REVISION]: Solo entra en error si falla la consulta (false) o no devuelve entero
            if ($filasAfectadas === false) {
                Model::rollBack();
                return ['exito' => false, 'mensaje' => 'Error al actualizar la incidencia.'];
            }

            // Si MySQL devuelve 0 (formulario enviado sin modificar ningún valor en los campos)
            if ($filasAfectadas === 0) {
                Model::commit();
                return ['exito' => true, 'mensaje' => 'No se detectaron cambios en la incidencia.'];
            }

            HistorialEstado::registrar(
                $incidenciaId,
                $estadoAnterior,
                $nuevoEstado,
                $adminUserId,
                $comentarioFinal,
            );

            Model::commit();

            return ['exito' => true, 'mensaje' => 'Incidencia actualizada con éxito.'];

        } catch (\PDOException $e) {
            Model::rollBack();
            return ['exito' => false, 'mensaje' => 'Error de base de datos al modificar la incidencia.'];
        } catch (\Exception $e) {
            Model::rollBack();
            return ['exito' => false, 'mensaje' => 'Error inesperado al modificar la incidencia.'];
        }
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