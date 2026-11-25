<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminUserCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
   
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_non_admin_cannot_access_users_crud(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_another_admin(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $this->validData())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.index'));

        $created = User::where('email', 'nuevo@reparaya.test')->firstOrFail();
        $this->assertSame(UserRole::Admin, $created->role);
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_role_sent_in_request_is_ignored(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $this->validData(['role' => UserRole::Customer->value]))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            UserRole::Admin,
            User::where('email', 'nuevo@reparaya.test')->firstOrFail()->role
        );
    }

    public function test_updating_without_password_keeps_the_current_one(): void
    {
        $other = User::factory()->create(['role' => UserRole::Admin, 'password' => 'original123']);

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $other), [
                'name' => 'Nombre cambiado',
                'email' => $other->email,
                'password' => '',
                'password' => '',
            ])
            ->assertSessionHasNoErrors();

        $other->refresh();
        $this->assertSame('Nombre cambiado', $other->name);
        $this->assertTrue(Hash::check('original123', $other->password));
    }

    public function test_admin_cannot_edit_a_customer(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $customer))
            ->assertForbidden();
    }

    public function test_admin_can_soft_delete_and_restore_a_customer(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $customer))
            ->assertSessionHasNoErrors();
        $this->assertSoftDeleted($customer);

        $this->actingAs($this->admin)
            ->patch(route('admin.users.restore', $customer))
            ->assertSessionHasNoErrors();
        $this->assertNotSoftDeleted($customer);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertForbidden();
        
            $this->assertNotSoftDeleted($this->admin);
    }

    public function test_admin_cannot_delete_a_technician(): void
    {
        $technician = User::factory()->create(['role' => UserRole::Technician]);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $technician))
            ->assertForbidden();
        $this->assertNotSoftDeleted($technician);
    }

    /**
     * Datos válidos de alta; $overrides permite cambiar o añadir campos.
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nuevo Admin',
            'email' => 'nuevo@reparaya.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }
}
