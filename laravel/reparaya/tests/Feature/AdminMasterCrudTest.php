<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\IncidentStatus;
use App\Enums\Urgency;
use App\Enums\UserRole;
use App\Models\Incident;
use App\Models\Speciality;
use App\Models\Technician;
use App\Models\User;
use Database\Seeders\SpecialitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class AdminMasterCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        //Temporal solo para ejecución local antes de fusionar con migration/laravel
        //config(['app.url' => 'http://localhost']);
        //$this->app['url']->forceRootUrl('http://localhost');

        $this->seed(SpecialitySeeder::class);
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_non_admin_cannot_access_specialities_or_technicians_crud(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->assertForbiddenRoute($customer, route('admin.specialities.index'));
        $this->assertForbiddenRoute($customer, route('admin.technicians.index'));
    }

    public function test_admin_can_create_speciality(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['_token' => 'test-token'])
            ->post(route('admin.specialities.store'), [
                '_token' => 'test-token',
                'name' => 'Climatización Industrial',
                'description' => 'Instalación y mantenimiento de equipos de aire acondicionado.',
                'responsibilities' => 'Revisión de circuitos frigoríficos y carga de gas.',
                'base_salary' => 1850.50,
                'base_price' => 75.00,
            ])
            ->assertRedirect(route('admin.specialities.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('specialities', [
            'name' => 'Climatización Industrial',
        ]);
    }

    public function test_speciality_with_assigned_technicians_cannot_be_deleted(): void
    {
        $speciality = Speciality::firstOrFail();
        $techUser = User::factory()->create(['role' => UserRole::Technician]);

        Technician::create([
            'user_id' => $techUser->id,
            'speciality_id' => $speciality->id,
            'full_name' => 'Carlos Ruiz',
            'is_available' => true,
        ]);

        $this->actingAs($this->admin)
            ->withSession(['_token' => 'test-token'])
            ->delete(route('admin.specialities.destroy', $speciality), [
                '_token' => 'test-token',
            ])
            ->assertRedirect(route('admin.specialities.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('specialities', ['id' => $speciality->id]);
    }

    public function test_speciality_with_incidents_cannot_be_deleted(): void
    {
        $speciality = Speciality::firstOrFail();
        $client = User::factory()->create(['role' => UserRole::Customer]);

        $this->createIncident($client, $speciality);

        $this->actingAs($this->admin)
            ->withSession(['_token' => 'test-token'])
            ->delete(route('admin.specialities.destroy', $speciality), [
                '_token' => 'test-token',
            ])
            ->assertRedirect(route('admin.specialities.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('specialities', ['id' => $speciality->id]);
    }

    public function test_admin_can_create_technician_with_linked_user(): void
    {
        $speciality = Speciality::firstOrFail();

        $this->actingAs($this->admin)
            ->withSession(['_token' => 'test-token'])
            ->post(route('admin.technicians.store'), [
                '_token' => 'test-token',
                'full_name' => 'Laura Gómez',
                'email' => 'laura.tecnico@reparaya.test',
                'phone' => '600123456',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'speciality_id' => $speciality->id,
                'is_available' => '1',
            ])
            ->assertRedirect(route('admin.technicians.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'laura.tecnico@reparaya.test',
            'role' => UserRole::Technician->value,
        ]);

        $this->assertDatabaseHas('technicians', [
            'full_name' => 'Laura Gómez',
            'speciality_id' => $speciality->id,
        ]);
    }

    public function test_technician_with_assigned_incidents_cannot_be_deleted(): void
    {
        $speciality = Speciality::firstOrFail();
        $client = User::factory()->create(['role' => UserRole::Customer]);
        $techUser = User::factory()->create(['role' => UserRole::Technician]);

        $technician = Technician::create([
            'user_id' => $techUser->id,
            'speciality_id' => $speciality->id,
            'full_name' => 'Marta López',
            'is_available' => true,
        ]);

        $this->createIncident($client, $speciality, $technician);

        $this->actingAs($this->admin)
            ->withSession(['_token' => 'test-token'])
            ->delete(route('admin.technicians.destroy', $technician), [
                '_token' => 'test-token',
            ])
            ->assertRedirect(route('admin.technicians.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('technicians', ['id' => $technician->id]);
    }

    private function assertForbiddenRoute(User $user, string $url): void
    {
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)->get($url);
            $this->fail("Se esperaba una excepción 403 al acceder a {$url}.");
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        } finally {
            $this->withExceptionHandling();
        }
    }

    private function createIncident(User $client, Speciality $speciality, ?Technician $technician = null): Incident
    {
        $incident = new Incident([
            'speciality_id' => $speciality->id,
            'description' => 'Avería de prueba para test CRUD.',
            'address' => 'Calle Mayor 10, Madrid',
            'service_date' => '2026-12-20 10:00:00',
            'urgency' => Urgency::Standard,
        ]);

        $incident->reference = 'INC-TEST-' . Str::upper(Str::random(6));
        $incident->client_id = $client->id;
        $incident->technician_id = $technician?->id;
        $incident->status = IncidentStatus::Pending;
        $incident->save();

        return $incident;
    }
}