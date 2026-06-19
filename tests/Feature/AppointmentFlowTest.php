<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentFlowTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_appointments(): void
    {
        $this->get('/citas')->assertRedirect('/login');
    }

    public function test_admin_can_access_appointments(): void
    {
        $this->actingAs($this->admin)
            ->get('/citas')
            ->assertStatus(200);
    }

    public function test_admin_can_create_appointment(): void
    {
        $owner = Owner::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id]);
        $veterinarian = Veterinarian::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($this->admin)->post('/appointments', [
            'pet_id' => $pet->id,
            'veterinarian_id' => $veterinarian->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateTimeString(),
            'reason' => 'Chequeo general',
            'status' => 'scheduled',
            'service_price' => $service->price,
        ]);

        $this->assertDatabaseHas('appointments', [
            'pet_id' => $pet->id,
            'reason' => 'Chequeo general',
            'status' => 'scheduled',
        ]);
    }

    public function test_public_token_is_generated_on_create(): void
    {
        $owner = Owner::factory()->create();
        $pet = Pet::factory()->create(['owner_id' => $owner->id]);
        $veterinarian = Veterinarian::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($this->admin)->post('/appointments', [
            'pet_id' => $pet->id,
            'veterinarian_id' => $veterinarian->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateTimeString(),
            'reason' => 'Urgencia',
            'status' => 'scheduled',
            'service_price' => $service->price,
        ]);

        $appointment = Appointment::where('pet_id', $pet->id)->first();
        $this->assertNotNull($appointment->public_token);
    }

    public function test_cannot_create_appointment_without_required_fields(): void
    {
        $this->actingAs($this->admin)->post('/appointments', [
            'pet_id' => '',
            'veterinarian_id' => '',
            'service_id' => '',
            'appointment_date' => '',
            'reason' => '',
            'status' => '',
        ])->assertSessionHasErrors(['pet_id', 'veterinarian_id', 'service_id', 'appointment_date', 'reason', 'status']);
    }
}
