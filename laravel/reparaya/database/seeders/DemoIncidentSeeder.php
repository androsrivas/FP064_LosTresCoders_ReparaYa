<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\IncidentStatus;
use App\Enums\Urgency;
use App\Models\Commission;
use App\Models\Community;
use App\Models\Incident;
use App\Models\ManagementCompany;
use App\Models\Speciality;
use App\Models\Technician;
use App\Models\User;
use App\Services\CommissionCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class DemoIncidentSeeder extends Seeder
{
    /** @var array<int, int> Contador para repartir incidencias entre técnicos de la misma especialidad. */
    private array $rotation = [];

    public function run(): void
    {
        $admin = User::where('email', 'admin@reparaya.test')->firstOrFail();
        $specialities = Speciality::all()->keyBy('name');
        $communities = Community::with('managementCompany.user')->get()->keyBy('name');

        // Puede haber más de un técnico por especialidad.
        $technicians = Technician::with('user')->orderBy('id')->get()->groupBy('speciality_id');

        foreach ($this->rows() as $row) {
            $status = $row['status'];
            $speciality = $specialities[$row['speciality']];
            $community = $row['community'] !== null ? $communities[$row['community']] : null;

            // Decisión pendiente (rol B): en incidencias de comunidad, el "cliente" es el usuario de la gestora.
            $client = $community?->managementCompany->user
                ?? User::where('email', $row['client'])->firstOrFail();

            $technician = in_array($status, [IncidentStatus::Pending, IncidentStatus::Cancelled], true)
                ? null
                : $this->pickTechnician($technicians->get($speciality->id), $speciality);

            $incident = Incident::firstOrNew(['reference' => $row['reference']]);
            $incident->fill([
                'speciality_id' => $speciality->id,
                'community_id' => $community?->id,
                'description' => $row['description'],
                'address' => $community?->address ?? $row['address'] ?? 'Calle Mayor 12, 2ºA',
                'service_date' => $row['service_date'],
                'urgency' => $row['urgency'] ?? Urgency::Standard,
            ]);
            $incident->forceFill([
                'client_id' => $client->id,
                'technician_id' => $technician?->id,
                'status' => $status,
                'finished_at' => $row['finished_at'] ?? null,
            ])->save();

            if ($incident->wasRecentlyCreated) {
                $this->logHistory($incident, $client, $admin, $technician);
            }

            if ($status === IncidentStatus::Finished && $community !== null) {
                $this->createCommission($incident, $community->managementCompany, $speciality);
            }
        }
    }

    /** Reparte las incidencias entre los técnicos de una especialidad de forma rotatória y determinista. */
    private function pickTechnician(?Collection $pool, Speciality $speciality): Technician
    {
        if ($pool === null || $pool->isEmpty()) {
            throw new \RuntimeException("No hi ha cap tècnic per a l'especialitat {$speciality->name}.");
        }

        $index = $this->rotation[$speciality->id] ?? 0;
        $this->rotation[$speciality->id] = $index + 1;

        return $pool[$index % $pool->count()];
    }

    /** Registra en el historial todos los pasos hasta el estado actual. */
    private function logHistory(Incident $incident, User $client, User $admin, ?Technician $technician): void
    {
        $path = match ($incident->status) {
            IncidentStatus::Pending => [IncidentStatus::Pending],
            IncidentStatus::Cancelled => [IncidentStatus::Pending, IncidentStatus::Cancelled],
            IncidentStatus::Assigned => [IncidentStatus::Pending, IncidentStatus::Assigned],
            IncidentStatus::InProgress => [IncidentStatus::Pending, IncidentStatus::Assigned, IncidentStatus::InProgress],
            IncidentStatus::Finished => [IncidentStatus::Pending, IncidentStatus::Assigned, IncidentStatus::InProgress, IncidentStatus::Finished],
        };

        $previous = null;

        foreach ($path as $new) {
            $actor = match ($new) {
                IncidentStatus::Assigned => $admin,
                IncidentStatus::InProgress, IncidentStatus::Finished => $technician?->user,
                default => $client,
            };

            $incident->statusLogs()->create([
                'user_id' => $actor?->id,
                'previous_status' => $previous,
                'new_status' => $new,
            ]);

            $previous = $new;
        }
    }

    private function createCommission(Incident $incident, ManagementCompany $company, Speciality $speciality): void
    {
        Commission::firstOrCreate(
            ['incident_id' => $incident->id],
            [
                'management_company_id' => $company->id,
                'base_price' => $speciality->base_price,
                'commission_rate' => $company->commission_rate,
                'amount' => CommissionCalculator::amount($speciality->base_price, $company->commission_rate),
                'finished_at' => $incident->finished_at,
            ],
        );
    }

    /** @return list<array<string, mixed>> */
    private function rows(): array
    {
        $finished = static fn (string $ref, string $community, string $speciality, string $date, string $description, Urgency $urgency = Urgency::Standard): array => [
            'reference' => $ref,
            'community' => $community,
            'client' => null,
            'speciality' => $speciality,
            'status' => IncidentStatus::Finished,
            'service_date' => "{$date} 09:00:00",
            'finished_at' => "{$date} 12:00:00",
            'urgency' => $urgency,
            'description' => $description,
        ];

        $open = static fn (string $ref, ?string $community, ?string $client, string $speciality, IncidentStatus $status, \DateTimeInterface $date, string $description, Urgency $urgency = Urgency::Standard, ?string $address = null): array => [
            'reference' => $ref,
            'community' => $community,
            'client' => $client,
            'speciality' => $speciality,
            'status' => $status,
            'service_date' => $date,
            'urgency' => $urgency,
            'description' => $description,
            'address' => $address,
        ];

        return [
            // 20 Finalizadas → Centro 6, Norte 5, Sur 4, Este 3, Oeste 2 (API: 30/25/20/15/10 %)

            // Centro (6)
            $finished('REP-20260904-DEMO01', 'Edificio Sol', 'Fontanería', '2026-09-04', 'Fuga en la bajante comunitaria.'),
            $finished('REP-20260908-DEMO15', 'Comunidad Consell de Cent 310', 'Cerrajería', '2026-09-08', 'Cambio del bombín de la puerta del portal.'),
            $finished('REP-20260925-DEMO03', 'Comunidad Rambla 21', 'Climatización', '2026-09-25', 'Revisión de la climatización del vestíbulo.'),
            $finished('REP-20261013-DEMO20', 'Comunidad Rambla 21', 'Pintura', '2026-10-13', 'Repintado del vestíbulo tras filtraciones de humedad.'),
            $finished('REP-20261015-DEMO06', 'Edificio Sol', 'Electricidad', '2026-10-15', 'El diferencial del ascensor salta.', Urgency::Urgent),
            $finished('REP-20261029-DEMO08', 'Comunidad Rambla 21', 'Fontanería', '2026-10-29', 'Cambio de la válvula general.'),

            // Norte (5)
            $finished('REP-20260918-DEMO02', 'Residencial Las Palmeras', 'Electricidad', '2026-09-18', 'Avería en el alumbrado del garaje.'),
            $finished('REP-20260922-DEMO17', 'Edificio Montseny', 'Gas', '2026-09-22', 'Revisión anual de la caldera comunitaria.'),
            $finished('REP-20261009-DEMO05', 'Residencial Las Palmeras', 'Fontanería', '2026-10-09', 'Fuga en el riego de las zonas comunes.', Urgency::Urgent),
            $finished('REP-20261027-DEMO22', 'Edificio Montseny', 'Carpintería', '2026-10-27', 'Ajuste de la puerta del cuarto de contadores.'),
            $finished('REP-20261105-DEMO09', 'Residencial Las Palmeras', 'Climatización', '2026-11-05', 'Mantenimiento de la sala de máquinas.'),

            // Sur (4)
            $finished('REP-20260911-DEMO16', 'Urbanización Mar Blau', 'Cristalería', '2026-09-11', 'Sustitución del cristal roto de la puerta de acceso.', Urgency::Urgent),
            $finished('REP-20261003-DEMO04', 'Torre Mirador', 'Fontanería', '2026-10-03', 'Atasco en la red de saneamiento.'),
            $finished('REP-20261020-DEMO21', 'Edificio Miramar', 'Albañilería', '2026-10-20', 'Reparación de grietas en la fachada del patio interior.'),
            $finished('REP-20261110-DEMO10', 'Torre Mirador', 'Electricidad', '2026-11-10', 'Sustitución de luminarias de la escalera.'),

            // Este (3)
            $finished('REP-20261006-DEMO19', 'Edificio Aragó', 'Electricidad', '2026-10-06', 'Fallo en el alumbrado de emergencia.'),
            $finished('REP-20261022-DEMO07', 'Edificio Levante', 'Electricidad', '2026-10-22', 'Portero automático sin corriente.'),
            $finished('REP-20261112-DEMO24', 'Edificio Aragó', 'Cerrajería', '2026-11-12', 'La puerta del garaje no cierra.', Urgency::Urgent),

            // Oeste (2)
            $finished('REP-20260929-DEMO18', 'Residencial Diagonal Park', 'Climatización', '2026-09-29', 'Avería en la bomba de calor del gimnasio comunitario.'),
            $finished('REP-20261103-DEMO23', 'Comunidad Els Pins', 'Pintura', '2026-11-03', 'Pintado de la barandilla de la escalera.'),

            // Otros estados (no generan comisión ni cuentan en la API)

            // Pendientes
            $open('REP-20261114-DEMO11', null, 'laura.garcia@reparaya.test', 'Fontanería', IncidentStatus::Pending,
                now()->addDays(5)->setTime(10, 0), 'El grifo de la cocina gotea.'),
            $open('REP-20261114-DEMO25', null, 'ana.martinez@reparaya.test', 'Electrodomésticos', IncidentStatus::Pending,
                now()->addDays(4)->setTime(9, 30), 'La lavadora no centrifuga.', address: 'Carrer de Balmes 56, 3º1ª'),
            $open('REP-20261114-DEMO26', 'Urbanización Mar Blau', null, 'Fontanería', IncidentStatus::Pending,
                now()->addDay()->setTime(8, 0), 'Fuga en la depuradora de la piscina comunitaria.', Urgency::Urgent),
            $open('REP-20261114-DEMO32', null, 'elena.torres@reparaya.test', 'Gas', IncidentStatus::Pending,
                now()->addDay()->setTime(12, 0), 'El calentador no enciende.', Urgency::Urgent, 'Carrer de Sants 140, 1º2ª'),
            $open('REP-20261114-DEMO34', null, 'paula.ruiz@reparaya.test', 'Climatización', IncidentStatus::Pending,
                now()->addDays(7)->setTime(17, 0), 'Instalación de un split en el dormitorio.', address: 'Carrer de Pujades 77, 4º3ª'),

            // Asignadas
            $open('REP-20261114-DEMO12', 'Edificio Levante', null, 'Climatización', IncidentStatus::Assigned,
                now()->addDays(3)->setTime(11, 0), 'El aire acondicionado del local comunitario no enfría.'),
            $open('REP-20261114-DEMO27', null, 'david.sanchez@reparaya.test', 'Cerrajería', IncidentStatus::Assigned,
                now()->addDay()->setTime(9, 0), 'La cerradura de la puerta principal está bloqueada.', Urgency::Urgent, 'Carrer de Muntaner 210, 5º1ª'),
            $open('REP-20261114-DEMO28', 'Comunidad Consell de Cent 310', null, 'Electricidad', IncidentStatus::Assigned,
                now()->addDays(2)->setTime(10, 0), 'El temporizador de la luz de la escalera no funciona.'),
            $open('REP-20261114-DEMO33', null, 'sergio.navarro@reparaya.test', 'Cristalería', IncidentStatus::Assigned,
                now()->addDays(6)->setTime(16, 0), 'Cambio de la mampara de la ducha.', address: 'Carrer de Verdi 33, 2º2ª'),
            $open('REP-20261114-DEMO35', null, 'jorge.molina@reparaya.test', 'Electrodomésticos', IncidentStatus::Assigned,
                now()->addDays(2)->setTime(15, 0), 'El frigorífico no enfría.', address: 'Avinguda de Roma 90, 6º4ª'),

            // En curso
            $open('REP-20261114-DEMO13', null, 'carlos.lopez@reparaya.test', 'Electricidad', IncidentStatus::InProgress,
                now()->setTime(9, 0), 'Los enchufes del salón no tienen corriente.', Urgency::Urgent),
            $open('REP-20261114-DEMO29', null, 'lucia.fernandez@reparaya.test', 'Pintura', IncidentStatus::InProgress,
                now()->setTime(8, 0), 'Pintado del dormitorio principal.', address: 'Carrer de Còrsega 402, 1º1ª'),
            $open('REP-20261114-DEMO30', 'Residencial Diagonal Park', null, 'Albañilería', IncidentStatus::InProgress,
                now()->setTime(10, 0), 'Sustitución de baldosas levantadas en el portal.'),

            // Canceladas (solo desde Pendiente)
            $open('REP-20261114-DEMO14', null, 'laura.garcia@reparaya.test', 'Climatización', IncidentStatus::Cancelled,
                now()->addDays(10)->setTime(16, 0), 'Revisión de la caldera (cancelada por el cliente).'),
            $open('REP-20261114-DEMO31', null, 'miguel.romero@reparaya.test', 'Carpintería', IncidentStatus::Cancelled,
                now()->addDays(8)->setTime(11, 0), 'Montaje de armario empotrado (cancelada por el cliente).', address: 'Carrer de Provença 15, bajos'),
        ];
    }
}