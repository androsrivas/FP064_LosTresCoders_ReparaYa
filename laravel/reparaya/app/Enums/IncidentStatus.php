<?php
declare(strict_types=1);

namespace App\Enums;

enum IncidentStatus: string
{
    case Pending = 'Pendiente';
    case Assigned = 'Asignada';
    case InProgress = 'En curso';
    case Finished = 'Finalizada';
    case Cancelled = 'Cancelada';

    public function isFinal(): bool
    {
        return $this === self::Finished || $this === self::Cancelled;
    }
}
