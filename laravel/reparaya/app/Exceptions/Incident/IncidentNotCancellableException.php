<?php
declare(strict_types=1);

namespace App\Exceptions\Incident;

use App\Enums\IncidentStatus;

/**
 * La incidencia está en un estado que no permite cancelarla.
 */
final class IncidentNotCancellableException extends IncidentException
{
    public static function forStatus(IncidentStatus $status): self
    {
        return new self(
            "No se puede cancelar una incidencia en estado {$status->value}."
            . ' Solo se pueden cancelar incidencias en estado Pendiente o Asignada.'
        );
    }
}
