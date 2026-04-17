<?php

namespace Database\Factories;

use App\Models\Cuve;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cuve>
 */
class CuveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('[A-Z]{2}-[0-9]{3}'),
            'nom' => fake()->word(),
            'produit_id' => \App\Models\Produit::factory(),
            'capacite_totale' => fake()->numberBetween(10000, 500000),
            'niveau_actuel' => fake()->numberBetween(0, 400000),
            'seuil_alerte_bas' => 10000,
            'seuil_alerte_haut' => 450000,
            'status' => fake()->randomElement(['operationnel', 'maintenance', 'hors_service']),
            'type_douane' => fake()->randomElement(['sous_douane', 'acquitte']),
        ];
    }
}
