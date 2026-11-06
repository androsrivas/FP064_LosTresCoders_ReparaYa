<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Máquina de estados de una incidencia: qué estados existen y quién puede pasar a cuál.
 */
final class EstadoIncidencia
{
    public const PENDIENTE  = 'Pendiente';
    public const ASIGNADA   = 'Asignada';
    public const EN_CURSO   = 'En curso';
    public const FINALIZADA = 'Finalizada';
    public const CANCELADA  = 'Cancelada';

    private const TRANSICIONES_TECNICO = [
        self::ASIGNADA => self::EN_CURSO,
        self::EN_CURSO => self::FINALIZADA,
    ];

    /** Cliente y administrador solo pueden intervenir en estos estados. */
    public static function esModificable(string $estado): bool
    {
        return in_array($estado, [self::PENDIENTE, self::ASIGNADA], true);
    }

    public static function puedeAvanzarTecnico(string $actual, string $nuevo): bool
    {
        return (self::TRANSICIONES_TECNICO[$actual] ?? null) === $nuevo;
    }

    /**
     * Decide el estado final de una incidencia modificada por el administrador.
     *
     * @param string   $actual     Estado guardado: Pendiente o Asignada.
     * @param string   $solicitado Estado enviado en el formulario, o $actual si no se envió ninguno.
     * @param int|null $tecnicoId  Técnico que tendrá la incidencia tras guardar, o null si no tiene.
     * @throws ReglaNegocioException Si la combinación no está permitida.
     */
    public static function resolverPorAdmin(string $actual, string $solicitado, ?int $tecnicoId): string
    {
        if ($solicitado === self::CANCELADA) {
            if ($actual !== self::PENDIENTE) {
                throw new ReglaNegocioException('Solo se pueden cancelar incidencias en estado Pendiente.');
            }
            return self::CANCELADA;
        }

        if ($solicitado === self::PENDIENTE) {
            // Desde Asignada, el administrador libera la incidencia (p. ej., el técnico ya no puede atenderla).
            // Desde Pendiente, indicar un técnico la pasa automáticamente a Asignada.
            return ($actual === self::PENDIENTE && $tecnicoId !== null) ? self::ASIGNADA : self::PENDIENTE;
        }

        if ($solicitado === self::ASIGNADA) {
            if ($tecnicoId === null) {
                throw new ReglaNegocioException('No se puede cambiar el estado a Asignada sin asignar un técnico.');
            }
            return self::ASIGNADA;
        }

        throw new ReglaNegocioException('El administrador solo puede cambiar el estado a Pendiente, Asignada o Cancelada.');
    }
}
