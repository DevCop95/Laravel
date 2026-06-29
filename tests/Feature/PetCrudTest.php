<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetCrudTest extends TestCase
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

    public function test_admin_can_access_pets_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/pacientes')
            ->assertStatus(200);
    }

    public function test_admin_can_create_pet_with_owner(): void
    {
        $vet = Veterinarian::factory()->create();

        $this->actingAs($this->admin)->post('/pets', [
            'name' => 'Firulais',
            'species' => 'Perro',
            'breed' => 'Criollo',
            'birth_date' => '2021-05-10',
            'veterinarian_id' => $vet->id,
            'owner_name' => 'Ana Lopez',
            'owner_email' => 'ana@test.com',
            'owner_phone_country_code' => '+57',
            'owner_phone' => '3101234567',
            'owner_address' => 'Calle 1',
        ])->assertStatus(302);

        $this->assertDatabaseHas('pets', ['name' => 'Firulais', 'species' => 'Perro']);
        $this->assertDatabaseHas('owners', ['email' => 'ana@test.com']);
    }

    public function test_admin_can_update_pet(): void
    {
        $pet = Pet::factory()->create();

        $this->actingAs($this->admin)->patch("/pets/{$pet->id}", [
            'name' => 'Nuevo Nombre',
            'species' => 'Gato',
            'breed' => 'Siames',
            'birth_date' => '2020-01-01',
            'veterinarian_id' => null,
            'owner_name' => 'Ana Lopez',
            'owner_email' => $pet->owner->email,
            'owner_phone_country_code' => '+57',
            'owner_phone' => '3109999999',
            'owner_address' => 'Otra direccion',
        ]);

        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'name' => 'Nuevo Nombre']);
    }

    public function test_admin_can_delete_pet(): void
    {
        $pet = Pet::factory()->create();

        $this->actingAs($this->admin)->delete("/pets/{$pet->id}");

        $this->assertSoftDeleted('pets', ['id' => $pet->id]);
    }

    public function test_cannot_create_pet_without_name(): void
    {
        $this->actingAs($this->admin)->post('/pets', [
            'name' => '',
            'species' => 'Perro',
            'owner_name' => 'Ana',
            'owner_email' => 'ana@test.com',
            'owner_phone_country_code' => '+57',
        ])->assertSessionHasErrors(['name']);
    }

    public function test_guest_cannot_create_pet(): void
    {
        $this->post('/pets', [])->assertRedirect('/login');
    }
}
