<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'service_type' => fake()->randomElement(['Consulta', 'Cirugia', 'Prevencion', 'Especialidad']),
            'price' => fake()->numberBetween(50000, 500000),
            'description' => fake()->sentence(),
        ];
    }
}
