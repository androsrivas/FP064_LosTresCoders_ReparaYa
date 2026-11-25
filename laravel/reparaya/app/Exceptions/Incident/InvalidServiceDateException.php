<?php
declare(strict_types=1);

namespace App\Exceptions\Incident;

/**
 * La fecha de servicio solicitada no cumple las reglas de antelación al dar de alta una incidencia.
 */
final class InvalidServiceDateException extends IncidentException
{
    /**
     * Incidencia urgente con fecha igual o anterior al momento actual.
     */
    public static function notInFuture(): self
    {
        return new self(
            'La fecha del servicio urgente debe ser posterior al momento actual.'
        );
    }

    /**
     * Incidencia estándar solicitada sin la antelación mínima.
     */
    public static function tooSoon(int $hours): self
    {
        return new self(
            "La fecha del servicio estándar debe solicitarse con al menos {$hours} horas de antelación."
        );
    }
}
