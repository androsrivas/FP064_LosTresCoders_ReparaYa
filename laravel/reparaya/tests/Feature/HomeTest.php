<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ManagementCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestora_can_access_home_page(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::PropertyManager,
        ]);

        ManagementCompany::create([
            'user_id' => $user->id,
            'company_name' => 'Fincas Test SL',
            'cif' => 'B12345678',
            'contact_phone' => '930000000',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}