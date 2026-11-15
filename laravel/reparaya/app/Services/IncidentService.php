<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class IncidentService
{
    private const MIN_HOURS_ADVANCE = 48;

    /**
     * Crea una nueva incidencia para el cliente y registra su entrada inicial en el historial.
     *
     * @param array<string, mixed> $data
     */
    public function createForClient(User $client, array $data): Incident
    {
        $serviceDate = Carbon::parse($data['service_date']);

        if ($serviceDate->lt(now()->addHours(self::MIN_HOURS_ADVANCE))) {
            throw new DomainException(
                'La fecha del servicio debe solicitarse con al menos 48 horas de antelación.'
            );
        }

        return DB::transaction(function () use ($client, $data, $serviceDate): Incident {
            $incident = new Incident([
                'speciality_id' => $data['speciality_id'],
                'community_id'  => $data['community_id'] ?? null,
                'description'   => trim($data['description']),
                'address'       => trim($data['address']),
                'service_date'  => $serviceDate,
                'urgency'       => $data['urgency'],
            ]);

            // Campos protegidos que no van en #[Fillable]
            $incident->client_id = $client->id;
            $incident->reference = $this->generateUniqueReference();
            $incident->status = IncidentStatus::Pending;
            $incident->save();

            // Registro inicial en incident_status_logs
            $incident->statusLogs()->create([
                'user_id'         => $client->id,
                'previous_status' => null,
                'new_status'      => IncidentStatus::Pending,
                'note'            => 'Incidencia registrada.',
            ]);

            return $incident;
        });
    }

    /**
     * Cancela una incidencia del cliente si su estado y antelación lo permiten.
     */
    public function cancelByClient(Incident $incident, User $client): void
    {
        if (!in_array($incident->status, [IncidentStatus::Pending, IncidentStatus::Assigned], true)) {
            throw new DomainException(
                'Solo se pueden cancelar incidencias en estado Pendiente o Asignada.'
            );
        }

        if ($incident->service_date->lt(now()->addHours(self::MIN_HOURS_ADVANCE))) {
            throw new DomainException(
                'No es posible cancelar una incidencia con menos de 48 horas de antelación.'
            );
        }

        DB::transaction(function () use ($incident, $client): void {
            $previousStatus = $incident->status;

            $incident->status = IncidentStatus::Cancelled;
            $incident->save();

            $incident->statusLogs()->create([
                'user_id'         => $client->id,
                'previous_status' => $previousStatus,
                'new_status'      => IncidentStatus::Cancelled,
                'note'            => 'Incidencia cancelada por el cliente.',
            ]);
        });
    }

    /**
     * Genera un localizador único siguiendo el formato REP-YYYYMMDD-XXXXXX.
     */
    private function generateUniqueReference(): string
    {
        $datePrefix = now()->format('Ymd');

        do {
            $suffix = Str::upper(Str::random(6));
            $reference = "REP-{$datePrefix}-{$suffix}";
        } while (Incident::where('reference', $reference)->exists());

        return $reference;
    }
}