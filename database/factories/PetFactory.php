<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetFactory extends Factory
{
    protected $model = Pet::class;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'species' => fake()->randomElement(['Perro', 'Gato', 'Conejo', 'Ave']),
            'breed' => fake()->word(),
            'birth_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'owner_id' => Owner::factory(),
        ];
    }
}
