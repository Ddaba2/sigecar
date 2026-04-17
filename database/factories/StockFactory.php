<?php

namespace Database\Factories;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
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
            'cuve_id' => \App\Models\Cuve::factory(),
            'quantite' => fake()->randomFloat(2, 100, 10000),
            'date_stock' => fake()->dateTimeBetween('-1 year', 'now'),
            'type_operation' => fake()->randomElement(['entree', 'sortie']),
        ];
    }
}
