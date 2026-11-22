<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\IncidentStatus;
use App\Enums\Urgency;
use App\Exceptions\Incident\InvalidServiceDateException;
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
        $urgency = $data['urgency'] instanceof Urgency
            ? $data['urgency']
            : Urgency::from($data['urgency']);

        if ($urgency === Urgency::Urgent) {
            if ($serviceDate->isPast()) {
                throw InvalidServiceDateException::notInFuture();
            }
        } elseif ($serviceDate->lt(now()->addHours(self::MIN_HOURS_ADVANCE))) {
            throw InvalidServiceDateException::tooSoon(self::MIN_HOURS_ADVANCE);
        }

        return DB::transaction(function () use ($client, $data, $serviceDate, $urgency): Incident {
            $incident = new Incident([
                'speciality_id' => $data['speciality_id'],
                'community_id'  => $data['community_id'] ?? null,
                'description'   => trim($data['description']),
                'address'       => trim($data['address']),
                'service_date'  => $serviceDate,
                'urgency'       => $urgency,
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
        DB::transaction(function () use ($incident, $client): void {
            $lockedIncident = Incident::lockForUpdate()->findOrFail($incident->id);

            if (!in_array($lockedIncident->status, [IncidentStatus::Pending, IncidentStatus::Assigned], true)) {
                throw new DomainException(
                    'Solo se pueden cancelar incidencias en estado Pendiente o Asignada.'
                );
            }

            if ($lockedIncident->service_date->lt(now()->addHours(self::MIN_HOURS_ADVANCE))) {
                throw new DomainException(
                    'No es posible cancelar una incidencia con menos de 48 horas de antelación.'
                );
            }

                $previousStatus = $lockedIncident->status;

                $lockedIncident->status = IncidentStatus::Cancelled;
                $lockedIncident->save();

                $lockedIncident->statusLogs()->create([
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