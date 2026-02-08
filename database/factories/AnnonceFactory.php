<?php

namespace Database\Factories;

use App\Models\Annonce;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Annonce>
 */
class AnnonceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'prix' => fake()->numberBetween(500000, 5000000),
            'marque' => fake()->randomElement(['Renault', 'Peugeot', 'Hyundai', 'Chevrolet', 'Toyota']),
            'modele' => fake()->randomElement(['Clio', '308', 'i10', 'Cruze', 'Corolla']),
            'annee' => fake()->year(),
            'kilometrage' => fake()->numberBetween(0, 300000),
            'carburant' => fake()->randomElement(['Essence', 'Diesel', 'Hybride', 'Électrique']),
            'boite_vitesse' => fake()->randomElement(['Manuelle', 'Automatique']),
            'ville' => fake()->randomElement(['Alger', 'Oran', 'Constantine', 'Tlemcen']),
            'vehicle_type' => fake()->randomElement(['Voiture', 'Utilitaire', 'Moto']),
            'image_path' => null,
            'image_path_2' => null,
            'image_path_3' => null,
            'image_path_4' => null,
            'image_path_5' => null,
            'user_id' => User::factory(),
            'show_phone' => fake()->boolean(),
            'condition' => fake()->randomElement(['oui', 'non']),
            'couleur' => fake()->colorName(),
            'document_type' => fake()->randomElement(['carte_grise', 'procuration']),
            'finition' => null,
            'is_active' => false,
            'views' => 0,
        ];
    }

    /**
     * Indicate that the annonce should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
