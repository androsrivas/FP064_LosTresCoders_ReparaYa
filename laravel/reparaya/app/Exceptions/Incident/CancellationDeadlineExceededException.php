<?php
declare(strict_types=1);

namespace App\Exceptions\Incident;

/**
 * Se intenta cancelar una incidencia sin la antelación mínima respecto a la fecha del servicio.
 */
final class CancellationDeadlineExceededException extends IncidentException
{
    public static function withinHours(int $hours): self
    {
        return new self(
            "Solo se puede cancelar una incidencia con más de {$hours} horas de antelación."
        );
    }
}
