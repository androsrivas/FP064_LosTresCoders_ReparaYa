<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;
use App\Models\HistorialEstado;
use App\Models\Incidencia;
use App\Models\Tecnico;
use PDOException;
use Throwable;

/**
 * Casos de uso de las incidencias: coordina reglas, persistencia e historial.
 * Las decisiones de negocio están en EstadoIncidencia y ReglasIncidencia.
 */
class IncidenciaService
{
    private const FORMATO_FECHA_BD = 'Y-m-d H:i:s';
    private const MAX_INTENTOS_LOCALIZADOR = 5;
    private const MYSQL_ENTRADA_DUPLICADA = 1062;
    private const CAMPOS_MODIFICABLES = [
        'tecnico_id', 'especialidad_id', 'descripcion', 'direccion',
        'fecha_servicio', 'tipo_urgencia', 'estado',
    ];

    /**
     * Registra una nueva incidencia en estado Pendiente.
     *
     * @param int|null $usuarioId Autor del alta para el historial. Por defecto, el cliente.
     */
    public function crearIncidencia(array $datos, ?int $usuarioId = null): array
    {
        return $this->ejecutar('Error al procesar el registro de la incidencia.', function () use ($datos, $usuarioId): array {
            ReglasIncidencia::exigirDatosDeAlta($datos);

            $tipoUrgencia = $datos['tipo_urgencia'] ?? ReglasIncidencia::URGENCIA_ESTANDAR;
            ReglasIncidencia::exigirUrgenciaValida($tipoUrgencia);

            $fecha = ReglasIncidencia::parsearFecha((string) ($datos['fecha_servicio'] ?? ''));
            ReglasIncidencia::exigirFechaFutura($fecha);
            ReglasIncidencia::exigirAntelacionDeAlta($tipoUrgencia, $fecha);

            $datos['tipo_urgencia'] = $tipoUrgencia;
            $datos['fecha_servicio'] = $fecha->format(self::FORMATO_FECHA_BD);
            $datos['estado'] = EstadoIncidencia::PENDIENTE;

            return ['exito' => true] + $this->insertarConLocalizadorUnico($datos, $usuarioId ?? (int) $datos['cliente_id']);
        });
    }

    /**
     * Cancela una incidencia a petición de su cliente.
     */
    public function cancelarPorCliente(int $incidenciaId, int $clienteId, string $motivo = ''): array
    {
        return $this->ejecutar('Error al procesar la cancelación.', function () use ($incidenciaId, $clienteId, $motivo): array {
            $incidencia = $this->buscar($incidenciaId);

            // Mismo mensaje que si no existe, para no revelar incidencias de otros clientes.
            if ((int) $incidencia['cliente_id'] !== $clienteId) {
                throw new ReglaNegocioException('Incidencia no encontrada.');
            }

            if (!EstadoIncidencia::esModificable($incidencia['estado'])) {
                throw new ReglaNegocioException("No se puede cancelar una incidencia en estado {$incidencia['estado']}.");
            }

            ReglasIncidencia::exigirAntelacionDeCancelacion(ReglasIncidencia::parsearFecha($incidencia['fecha_servicio']));

            $this->cambiarEstado(
                $incidencia,
                EstadoIncidencia::CANCELADA,
                $clienteId,
                $motivo !== '' ? $motivo : 'Incidencia cancelada por el cliente.'
            );

            return self::ok('Incidencia cancelada con éxito.');
        });
    }

    /**
     * Avanza una incidencia asignada al técnico autenticado: Asignada -> En curso -> Finalizada.
     */
    public function cambiarEstadoPorTecnico(int $incidenciaId, int $usuarioId, string $nuevoEstado): array
    {
        return $this->ejecutar('Error al cambiar el estado de la incidencia.', function () use ($incidenciaId, $usuarioId, $nuevoEstado): array {
            $incidencia = $this->buscar($incidenciaId);
            $tecnico = Tecnico::findByUsuarioId($usuarioId);

            if ($tecnico === null || (int) $incidencia['tecnico_id'] !== (int) $tecnico['id']) {
                throw new ReglaNegocioException('Incidencia no encontrada.');
            }

            if (!EstadoIncidencia::puedeAvanzarTecnico($incidencia['estado'], $nuevoEstado)) {
                throw new ReglaNegocioException("No se puede pasar de {$incidencia['estado']} a {$nuevoEstado}.");
            }

            $this->cambiarEstado($incidencia, $nuevoEstado, $usuarioId, 'Estado actualizado por el técnico.');

            return self::ok('Estado de la incidencia actualizado.');
        });
    }

    /**
     * Asigna un técnico desde el Administrador de Negocio.
     */
    public function asignarTecnicoPorAdmin(int $incidenciaId, int $tecnicoId, int $adminUserId): array
    {
        return $this->ejecutar('Error al asignar el técnico.', function () use ($incidenciaId, $tecnicoId, $adminUserId): array {
            $incidencia = $this->buscar($incidenciaId);

            if (!EstadoIncidencia::esModificable($incidencia['estado'])) {
                throw new ReglaNegocioException('Solo se pueden asignar técnicos a incidencias en estado Pendiente o Asignada.');
            }

            ReglasIncidencia::exigirTecnicoCompatible(Tecnico::findById($tecnicoId), (int) $incidencia['especialidad_id']);

            $filas = Model::transaction(function () use ($incidencia, $tecnicoId, $adminUserId): int {
                $filas = Incidencia::asignarTecnico((int) $incidencia['id'], $tecnicoId);

                if ($filas > 0) {
                    HistorialEstado::registrar(
                        (int) $incidencia['id'],
                        $incidencia['estado'],
                        EstadoIncidencia::ASIGNADA,
                        $adminUserId,
                        "Técnico ID {$tecnicoId} asignado por el Administrador."
                    );
                }

                return $filas;
            });

            return self::ok($filas > 0 ? 'Técnico asignado correctamente.' : 'El técnico ya estaba asignado.');
        });
    }

    /**
     * Modifica los campos generales de una incidencia desde el Administrador de Negocio.
     * Los campos que no se informan conservan su valor actual.
     */
    public function modificarPorAdmin(int $incidenciaId, array $datos, int $adminUserId): array
    {
        return $this->ejecutar('Error al modificar la incidencia.', function () use ($incidenciaId, $datos, $adminUserId): array {
            $incidencia = $this->buscar($incidenciaId);

            if (!EstadoIncidencia::esModificable($incidencia['estado'])) {
                throw new ReglaNegocioException('El administrador solo puede modificar incidencias en estado Pendiente o Asignada.');
            }

            $resultante = self::camposInformados($datos) + $incidencia;

            ReglasIncidencia::exigirUrgenciaValida($resultante['tipo_urgencia']);
            $resultante['fecha_servicio'] = self::validarFechaModificada($resultante['fecha_servicio'], $incidencia['fecha_servicio']);

            $tecnicoId = !empty($resultante['tecnico_id']) ? (int) $resultante['tecnico_id'] : null;
            $resultante['estado'] = EstadoIncidencia::resolverPorAdmin($incidencia['estado'], $resultante['estado'], $tecnicoId);

            // Solo una incidencia Asignada conserva técnico. Se decide antes de validarlo para que el
            // administrador pueda devolverla a Pendiente aunque el técnico ya no exista o no sea compatible.
            if ($resultante['estado'] !== EstadoIncidencia::ASIGNADA) {
                $resultante['tecnico_id'] = null;
            } else {
                ReglasIncidencia::exigirTecnicoCompatible(Tecnico::findById($tecnicoId), (int) $resultante['especialidad_id']);
            }

            $comentario = !empty($datos['comentario'])
                ? $datos['comentario']
                : 'Incidencia modificada manualmente por el Administrador.';

            $filas = Model::transaction(function () use ($incidencia, $resultante, $adminUserId, $comentario): int {
                $filas = Incidencia::actualizarIncidencia((int) $incidencia['id'], $resultante);

                // MySQL devuelve 0 filas si el formulario no cambia ningún valor.
                if ($filas > 0) {
                    HistorialEstado::registrar(
                        (int) $incidencia['id'],
                        $incidencia['estado'],
                        $resultante['estado'],
                        $adminUserId,
                        $comentario
                    );
                }

                return $filas;
            });

            return self::ok($filas > 0 ? 'Incidencia actualizada con éxito.' : 'No se detectaron cambios en la incidencia.');
        });
    }

    public function obtenerPorId(int $id): ?array
    {
        return Incidencia::obtenerPorId($id);
    }

    public function obtenerPorCliente(int $clienteId): array
    {
        return Incidencia::obtenerPorCliente($clienteId);
    }

    /**
     * Incidencias de un técnico a partir de su usuario: incidencias.tecnico_id apunta a tecnicos.id.
     */
    public function obtenerPorUsuarioTecnico(int $usuarioId): array
    {
        $tecnico = Tecnico::findByUsuarioId($usuarioId);

        return $tecnico === null ? [] : Incidencia::obtenerPorTecnico((int) $tecnico['id']);
    }

    /**
     * Convierte el resultado de un caso de uso en la respuesta que espera el controlador.
     */
    private function ejecutar(string $mensajeError, callable $casoDeUso): array
    {
        try {
            return $casoDeUso();
        } catch (ReglaNegocioException $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('IncidenciaService: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => $mensajeError];
        }
    }

    private function buscar(int $incidenciaId): array
    {
        return Incidencia::obtenerPorId($incidenciaId)
            ?? throw new ReglaNegocioException('Incidencia no encontrada.');
    }

    /**
     * Reintenta solo si colisiona el localizador. Otros errores de integridad (como una
     * clave foránea inexistente) comparten SQLSTATE 23000 y no se resuelven reintentando.
     */
    private function insertarConLocalizadorUnico(array $datos, int $usuarioId): array
    {
        for ($intento = 1; ; $intento++) {
            $datos['localizador'] = $this->generar();

            try {
                $id = Model::transaction(function () use ($datos, $usuarioId): int {
                    $id = Incidencia::crear($datos);
                    HistorialEstado::registrar($id, null, $datos['estado'], $usuarioId, 'Incidencia registrada.');
                    return $id;
                });

                return ['id' => $id, 'localizador' => $datos['localizador']];
            } catch (PDOException $e) {
                $esDuplicado = ($e->errorInfo[1] ?? null) === self::MYSQL_ENTRADA_DUPLICADA;

                if (!$esDuplicado || $intento >= self::MAX_INTENTOS_LOCALIZADOR) {
                    throw $e;
                }
            }
        }
    }

    private function cambiarEstado(array $incidencia, string $nuevoEstado, int $usuarioId, string $comentario): void
    {
        Model::transaction(function () use ($incidencia, $nuevoEstado, $usuarioId, $comentario): void {
            Incidencia::actualizarEstado((int) $incidencia['id'], $nuevoEstado);
            HistorialEstado::registrar((int) $incidencia['id'], $incidencia['estado'], $nuevoEstado, $usuarioId, $comentario);
        });
    }

    /**
     * El formulario envía todos los campos: una fecha pasada solo se rechaza si es nueva, para poder corregir otros datos de una incidencia cuya fecha ya ha vencido.
     */
    private static function validarFechaModificada(string $fechaNueva, string $fechaActual): string
    {
        $nueva = ReglasIncidencia::parsearFecha($fechaNueva);

        if ($nueva != ReglasIncidencia::parsearFecha($fechaActual)) {
            ReglasIncidencia::exigirFechaFutura($nueva);
        }

        return $nueva->format(self::FORMATO_FECHA_BD);
    }

    /** El controlador envía '' o 0 en los campos vacíos: no deben sobrescribir los valores actuales. */
    private static function camposInformados(array $datos): array
    {
        return array_filter(
            array_intersect_key($datos, array_flip(self::CAMPOS_MODIFICABLES)),
            static fn ($valor): bool => !in_array($valor, [null, '', 0], true)
        );
    }

    private static function ok(string $mensaje): array
    {
        return ['exito' => true, 'mensaje' => $mensaje];
    }

    private static function generar(): string
    {
        return 'REP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
