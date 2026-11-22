<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

final class IncidentPolicy
{
    /**
     * El cliente solo puede ver el detalle de sus propias incidencias.
     */
    public function view(User $user, Incident $incident): bool
    {
        return $user->id === $incident->client_id;
    }

    /**
     * El cliente solo puede cancelar sus propias incidencias.
     * El estado y las 48 h los valida IncidentService.
     */
    public function cancel(User $user, Incident $incident): bool
    {
        return $user->id === $incident->client_id;
    }
}
