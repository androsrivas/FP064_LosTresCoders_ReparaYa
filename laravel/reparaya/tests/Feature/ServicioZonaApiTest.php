<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\IncidentStatus;
use App\Enums\Urgency;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ServicioZonaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_data_when_no_finished_incidents_exist(): void
    {
        $this->getJson('/api/servicios/zonas')
            ->assertOk()
            ->assertExactJson([
                'data' => [],
                'total_servicios_realizados' => 0,
            ]);
    }

    public function test_aggregates_only_finished_incidents_by_zone_and_sin_zona(): void
    {
        $now = now();

        $specialityId = DB::table('specialities')->insertGetId([
            'name' => 'Fontanería',
            'base_price' => 50.00,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $zoneCentroId = DB::table('zones')->insertGetId([
            'name' => 'Centro',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $zoneNorteId = DB::table('zones')->insertGetId([
            'name' => 'Norte',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $manager = User::factory()->create();
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $companyId = DB::table('management_companies')->insertGetId([
            'user_id' => $manager->id,
            'name' => 'Fincas Sol',
            'tax_id' => 'B12345678',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $communityCentroId = DB::table('communities')->insertGetId([
            'management_company_id' => $companyId,
            'zone_id' => $zoneCentroId,
            'name' => 'Comunidad Centro',
            'address' => 'Calle Mayor 1',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $communityNorteId = DB::table('communities')->insertGetId([
            'management_company_id' => $companyId,
            'zone_id' => $zoneNorteId,
            'name' => 'Comunidad Norte',
            'address' => 'Avenida Norte 2',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 2 incidencias finalizadas en Centro (50 %)
        $this->insertIncident('REP-20261127-000001', $manager->id, $specialityId, $communityCentroId, IncidentStatus::Finished);
        $this->insertIncident('REP-20261127-000002', $manager->id, $specialityId, $communityCentroId, IncidentStatus::Finished);

        // 1 incidencia finalizada en Norte (25 %)
        $this->insertIncident('REP-20261127-000003', $manager->id, $specialityId, $communityNorteId, IncidentStatus::Finished);

        // 1 incidencia finalizada de particular sin comunidad -> "Sin zona" (25 %)
        $this->insertIncident('REP-20261127-000004', $customer->id, $specialityId, null, IncidentStatus::Finished);

        // Incidencias en otros estados que NO deben contarse
        $this->insertIncident('REP-20261127-000005', $manager->id, $specialityId, $communityCentroId, IncidentStatus::Pending);
        $this->insertIncident('REP-20261127-000006', $customer->id, $specialityId, null, IncidentStatus::Cancelled);

        $this->getJson('/api/servicios/zonas')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    [
                        'zona' => 'Centro',
                        'servicios_realizados' => 2,
                        'porcentaje_total' => 50,
                    ],
                    [
                        'zona' => 'Norte',
                        'servicios_realizados' => 1,
                        'porcentaje_total' => 25,
                    ],
                    [
                        'zona' => 'Sin zona',
                        'servicios_realizados' => 1,
                        'porcentaje_total' => 25,
                    ],
                ],
                'total_servicios_realizados' => 4,
            ]);
    }

    private function insertIncident(
        string $reference,
        int $clientId,
        int $specialityId,
        ?int $communityId,
        IncidentStatus $status,
    ): void {
        $now = now();

        DB::table('incidents')->insert([
            'reference' => $reference,
            'client_id' => $clientId,
            'speciality_id' => $specialityId,
            'community_id' => $communityId,
            'description' => 'Servicio de prueba',
            'address' => 'Calle Test 123',
            'service_date' => $now,
            'urgency' => Urgency::Standard->value,
            'status' => $status->value,
            'finished_at' => $status === IncidentStatus::Finished ? $now : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}