<?php

namespace Database\Factories;

use App\Models\Depotage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Depotage>
 */
class DepotageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'produit_id' => \App\Models\Produit::factory(),
            'quantite' => fake()->randomFloat(2, 100, 10000),
            'prix_unitaire' => fake()->randomFloat(2, 1, 5),
            'date_depotage' => fake()->dateTimeBetween('-1 year', 'now'),
            'destination' => fake()->city(),
            'transporteur' => fake()->company(),
            'numero_bl' => fake()->unique()->regexify('BL-[0-9]{6}'),
            'status' => fake()->randomElement(['en_cours', 'termine', 'annule']),
        ];
    }
}
