<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VeterinarianCrudTest extends TestCase
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

    public function test_admin_can_access_veterinarians_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/veterinarios')
            ->assertStatus(200);
    }

    public function test_admin_can_create_veterinarian_and_linked_user(): void
    {
        $this->actingAs($this->admin)->post('/veterinarians', [
            'name' => 'Laura Gomez',
            'email' => 'laura@clinica.com',
            'phone' => '3001112233',
            'specialty' => 'Medicina general',
        ])->assertStatus(302);

        $this->assertDatabaseHas('veterinarians', ['email' => 'laura@clinica.com']);
        $this->assertDatabaseHas('users', ['email' => 'laura@clinica.com', 'role' => 'veterinarian']);
    }

    public function test_admin_can_update_veterinarian(): void
    {
        $vet = Veterinarian::factory()->create();

        $this->actingAs($this->admin)->patch("/veterinarians/{$vet->id}", [
            'name' => 'Nombre Actualizado',
            'email' => $vet->email,
            'phone' => '3009998877',
            'specialty' => 'Cirugia',
        ]);

        $this->assertDatabaseHas('veterinarians', ['id' => $vet->id, 'name' => 'Nombre Actualizado']);
    }

    public function test_admin_can_delete_veterinarian(): void
    {
        $vet = Veterinarian::factory()->create();

        $this->actingAs($this->admin)->delete("/veterinarians/{$vet->id}");

        $this->assertSoftDeleted('veterinarians', ['id' => $vet->id]);
    }

    public function test_cannot_create_veterinarian_with_duplicate_email(): void
    {
        Veterinarian::factory()->create(['email' => 'dup@clinica.com']);

        $this->actingAs($this->admin)->post('/veterinarians', [
            'name' => 'Otro Vet',
            'email' => 'dup@clinica.com',
            'phone' => '3001112233',
            'specialty' => 'Urgencias',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_veterinarian_role_cannot_access_veterinarians_page(): void
    {
        $vetUser = User::factory()->create(['role' => 'veterinarian']);

        $this->actingAs($vetUser)
            ->get('/veterinarios')
            ->assertStatus(403);
    }
}
