<?php

namespace Tests\Feature;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);
    }

    public function test_admin_can_access_owners_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/responsables')
            ->assertStatus(200);
    }

    public function test_admin_can_create_owner(): void
    {
        $response = $this->actingAs($this->admin)->post('/owners', [
            'name' => 'Juan Perez',
            'email' => 'juan@test.com',
            'phone_country_code' => '+57',
            'phone' => '3101234567',
            'address' => 'Calle 10 #15-20',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('owners', [
            'name' => 'Juan Perez',
            'email' => 'juan@test.com',
            'phone_country_code' => '+57',
        ]);
    }

    public function test_admin_can_update_owner(): void
    {
        $owner = Owner::factory()->create();

        $this->actingAs($this->admin)->patch("/owners/{$owner->id}", [
            'name' => 'Nombre Actualizado',
            'email' => $owner->email,
            'phone_country_code' => '+57',
            'phone' => '3109999999',
            'address' => 'Nueva direccion',
        ]);

        $this->assertDatabaseHas('owners', [
            'id' => $owner->id,
            'name' => 'Nombre Actualizado',
        ]);
    }

    public function test_admin_can_delete_owner(): void
    {
        $owner = Owner::factory()->create();

        $this->actingAs($this->admin)->delete("/owners/{$owner->id}");

        $this->assertSoftDeleted('owners', ['id' => $owner->id]);
    }

    public function test_cannot_create_owner_with_invalid_email(): void
    {
        $this->actingAs($this->admin)->post('/owners', [
            'name' => 'Test',
            'email' => 'not-an-email',
            'phone_country_code' => '+57',
            'phone' => '3101234567',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_cannot_create_owner_without_name(): void
    {
        $this->actingAs($this->admin)->post('/owners', [
            'name' => '',
            'email' => 'test@test.com',
            'phone_country_code' => '+57',
            'phone' => '3101234567',
        ])->assertSessionHasErrors(['name']);
    }
}
