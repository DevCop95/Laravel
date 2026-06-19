<?php

namespace Database\Factories;

use App\Models\Veterinarian;
use Illuminate\Database\Eloquent\Factories\Factory;

class VeterinarianFactory extends Factory
{
    protected $model = Veterinarian::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '3' . fake()->numerify('#########'),
            'specialty' => fake()->randomElement(['Medicina general', 'Cirugia', 'Dermatologia', 'Urgencias']),
        ];
    }
}
