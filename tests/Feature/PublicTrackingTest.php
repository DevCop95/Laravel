<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Service;
use App\Models\Veterinarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_page_is_accessible_with_valid_token(): void
    {
        $owner = Owner::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id]);
        $veterinarian = Veterinarian::factory()->create();
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'veterinarian_id' => $veterinarian->id,
            'service_id' => $service->id,
            'public_token' => Str::uuid()->toString(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->get("/seguimiento/{$appointment->public_token}")
            ->assertStatus(200);
    }

    public function test_tracking_returns_404_for_invalid_token(): void
    {
        $this->get('/seguimiento/invalid-token-12345')
            ->assertStatus(404);
    }

    public function test_tracking_returns_410_when_expired(): void
    {
        $owner = Owner::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id]);
        $veterinarian = Veterinarian::factory()->create();
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'veterinarian_id' => $veterinarian->id,
            'service_id' => $service->id,
            'public_token' => Str::uuid()->toString(),
            'expires_at' => now()->subDay(),
        ]);

        $this->get("/seguimiento/{$appointment->public_token}")
            ->assertStatus(410);
    }

    public function test_tracking_data_endpoint_returns_json(): void
    {
        $owner = Owner::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id]);
        $veterinarian = Veterinarian::factory()->create();
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'pet_id' => $pet->id,
            'veterinarian_id' => $veterinarian->id,
            'service_id' => $service->id,
            'public_token' => Str::uuid()->toString(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->getJson("/seguimiento/{$appointment->public_token}/data")
            ->assertStatus(200)
            ->assertJsonStructure([
                'pet_name',
                'species',
                'owner_name',
                'status',
                'status_label',
                'service_name',
                'veterinarian_name',
            ]);
    }
}
