<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recipe;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeIngredient>
 */
class RecipeIngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'name' => fake()->word(),
            'quantity' => fake()->randomFloat(1, 0.5, 5)
                .' '
                .fake()->randomElement(['cups', 'tbsp', 'tsp', 'grams', 'pcs']),
        ];
    }
}
