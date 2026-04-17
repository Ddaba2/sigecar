<?php

namespace Database\Factories;

use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produit>
 */
class ProduitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->word(),
            'code' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{3}'),
            'type' => fake()->randomElement(['essence', 'gasoil', 'jet_a1', 'marine']),
            'density' => fake()->randomFloat(4, 0.7000, 0.9000),
            'unit' => 'L',
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
