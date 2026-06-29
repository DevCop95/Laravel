<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetHistoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_any_pet_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pet = Pet::factory()->create();

        $this->actingAs($admin)
            ->get("/pacientes/{$pet->id}/historial")
            ->assertStatus(200);
    }

    public function test_veterinarian_can_view_own_pet_history(): void
    {
        $vet = Veterinarian::factory()->create();
        $vetUser = User::factory()->create(['role' => 'veterinarian']);
        $vet->update(['user_id' => $vetUser->id]);

        $pet = Pet::factory()->create(['veterinarian_id' => $vet->id]);

        $this->actingAs($vetUser)
            ->get("/pacientes/{$pet->id}/historial")
            ->assertStatus(200);
    }

    public function test_veterinarian_cannot_view_other_pet_history(): void
    {
        $vet = Veterinarian::factory()->create();
        $vetUser = User::factory()->create(['role' => 'veterinarian']);
        $vet->update(['user_id' => $vetUser->id]);

        // Paciente de OTRO veterinario.
        $otherVet = Veterinarian::factory()->create();
        $otherPet = Pet::factory()->create(['veterinarian_id' => $otherVet->id]);

        $this->actingAs($vetUser)
            ->get("/pacientes/{$otherPet->id}/historial")
            ->assertStatus(403);
    }
}
