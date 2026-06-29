<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCrudTest extends TestCase
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

    public function test_admin_can_access_services_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/servicios')
            ->assertStatus(200);
    }

    public function test_admin_can_create_service(): void
    {
        $this->actingAs($this->admin)->post('/services', [
            'name' => 'Consulta general',
            'service_type' => 'Consulta',
            'price' => 80000,
            'description' => 'Revision clinica general',
        ])->assertStatus(302);

        $this->assertDatabaseHas('services', ['name' => 'Consulta general', 'price' => 80000]);
    }

    public function test_admin_can_update_service(): void
    {
        $service = Service::factory()->create();

        $this->actingAs($this->admin)->patch("/services/{$service->id}", [
            'name' => 'Servicio Actualizado',
            'service_type' => 'Cirugia',
            'price' => 150000,
            'description' => 'Actualizado',
        ]);

        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'Servicio Actualizado']);
    }

    public function test_admin_can_delete_service(): void
    {
        $service = Service::factory()->create();

        $this->actingAs($this->admin)->delete("/services/{$service->id}");

        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }

    public function test_cannot_create_service_with_negative_price(): void
    {
        $this->actingAs($this->admin)->post('/services', [
            'name' => 'Servicio',
            'service_type' => 'Consulta',
            'price' => -10,
        ])->assertSessionHasErrors(['price']);
    }

    public function test_veterinarian_role_cannot_create_service(): void
    {
        $vetUser = User::factory()->create(['role' => 'veterinarian']);

        $this->actingAs($vetUser)->post('/services', [
            'name' => 'Servicio',
            'service_type' => 'Consulta',
            'price' => 1000,
        ])->assertStatus(403);
    }
}
