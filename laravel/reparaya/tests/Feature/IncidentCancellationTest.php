<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\IncidentStatus;
use App\Enums\Urgency;
use App\Enums\UserRole;
use App\Models\Incident;
use App\Models\Speciality;
use App\Models\User;
use App\Services\IncidentService;
use Database\Seeders\SpecialitySeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IncidentCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SpecialitySeeder::class);
        $this->client = User::factory()->create(['role' => UserRole::Customer]);

        // Momento de alta fijo: todas las fechas del test son relativas a este
        $this->travelTo(Carbon::parse('2026-12-01 10:00:00'));
    }

    public function test_client_cannot_cancel_another_clients_incident(): void
    {
        $incident = $this->createIncident($this->client, '2026-12-10 10:00:00');
        $otherClient = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($otherClient)
            ->patch(route('incidents.cancel', $incident))
            ->assertForbidden();

        $this->assertSame(IncidentStatus::Pending, $incident->fresh()->status);
    }

    public function test_cancellation_is_rejected_with_exactly_48_hours_left(): void
    {
        $incident = $this->createIncident($this->client, '2026-12-05 10:00:00');
        $this->travelTo(Carbon::parse('2026-12-03 10:00:00'));

        $this->actingAs($this->client)
            ->patch(route('incidents.cancel', $incident))
            ->assertSessionHasErrors('general');

        $this->assertSame(IncidentStatus::Pending, $incident->fresh()->status);
    }

    public function test_cancellation_is_allowed_with_more_than_48_hours_left(): void
    {
        $incident = $this->createIncident($this->client, '2026-12-05 10:00:00');
        $this->travelTo(Carbon::parse('2026-12-03 09:59:00'));

        $this->actingAs($this->client)
            ->patch(route('incidents.cancel', $incident))
            ->assertSessionHasNoErrors();

        $incident->refresh();
        $this->assertSame(IncidentStatus::Cancelled, $incident->status);
        $this->assertCount(2, $incident->statusLogs);
    }

    public function test_incident_with_status_in_progress_not_cancellable_outside_48_hours_window(): void
    {
        $incident = $this->createIncident($this->client, '2026-12-10 10:00:00');
        $incident->status = IncidentStatus::InProgress;
        $incident->save();

        $this->actingAs($this->client)
            ->patch(route('incidents.cancel', $incident))
            ->assertSessionHasErrors(
                ['general' => 'No se puede cancelar una incidencia en estado En curso. '
            . 'Solo se pueden cancelar incidencias en estado Pendiente o Asignada.'
            ]);

        $this->assertSame(IncidentStatus::InProgress, $incident->fresh()->status);
    }

    /**
     * Crea una incidencia estándar a través del servicio, como lo haría el controlador.
     */
    private function createIncident(User $client, string $serviceDate): Incident
    {
        return app(IncidentService::class)->createForClient($client, [
            'speciality_id' => Speciality::firstOrFail()->id,
            'description' => 'Fuga de agua debajo del fregadero.',
            'address' => 'Carrer Major 1, Barcelona',
            'service_date' => $serviceDate,
            'urgency' => Urgency::Standard->value,
        ]);
    }
}
