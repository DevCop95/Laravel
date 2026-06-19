<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Service;
use App\Models\Veterinarian;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'pet_id' => Pet::factory(),
            'veterinarian_id' => Veterinarian::factory(),
            'service_id' => Service::factory(),
            'service_price' => fake()->numberBetween(50000, 500000),
            'appointment_date' => fake()->dateTimeBetween('now', '+30 days'),
            'reason' => fake()->sentence(),
            'status' => 'scheduled',
            'public_token' => Str::uuid()->toString(),
            'expires_at' => now()->addDays(7),
        ];
    }
}
