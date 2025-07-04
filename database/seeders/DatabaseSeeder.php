<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use App\Models\Author;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Recipe::factory()
            ->count(10)
            ->create()
            ->each(function ($recipe) {

                // For each recipe, let's make five ingredients
                RecipeIngredient::factory()
                    ->count(5)
                    ->create(['recipe_id' => $recipe->id]);

                // Add 7 instructional steps with a step_order
                RecipeStep::factory()
                    ->count(7)
                    ->state(new Sequence(
                        fn ($sequence) => ['step_order' => $sequence->index + 1]
                    ))
                    ->create(['recipe_id' => $recipe->id]);
            });

        // Adding some additional hardcoded recipes with ingredients and steps to reflect Vietnamese / French recipe names.
        // I should probably also consider Chinese, Thai, etc. But I'm not.

        $banhMi = Recipe::factory()->create([
            'name' => 'Bánh mì',
            'slug' => 'banh-mi',
            'description' => 'A Vietnamese sandwich that consists of a French baguette filled with tastey things.',
        ]);

        RecipeIngredient::factory()->create([
            'recipe_id' => $banhMi->id,
            'name' => 'French baguette',
            'quantity' => '1 pcs',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $banhMi->id,
            'name' => 'Pâté',
            'quantity' => '50 grams',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $banhMi->id,
            'name' => 'Pickled vegetables',
            'quantity' => '100 grams',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $banhMi->id,
            'name' => 'Cilantro',
            'quantity' => '1 bunch',
        ]);
        RecipeIngredient::factory()->create([
            'recipe_id' => $banhMi->id,
            'name' => 'Chili sauce',
            'quantity' => '2 tbsp',
        ]);

        RecipeStep::factory()->create([
            'recipe_id' => $banhMi->id,
            'instruction' => 'Slice the baguette lengthwise.',
            'step_order' => 1,
        ]);
        RecipeStep::factory()->create([
            'recipe_id' => $banhMi->id,
            'instruction' => 'Spread pâté on both sides of the baguette.',
            'step_order' => 2,
        ]);
        RecipeStep::factory()->create([
            'recipe_id' => $banhMi->id,
            'instruction' => 'Add vegetables and cilantro.',
            'step_order' => 3,
        ]);
        RecipeStep::factory()->create([
            'recipe_id' => $banhMi->id,
            'instruction' => 'Drizzle with a spicy sauce or place on the side for midwest folks.',
            'step_order' => 4,
        ]);

        // Its hard to test with fake data, so let's add some real recipes.
        $user1 = Author::factory()->create(['email' => 'chef@example.com']);
        $user2 = Author::factory()->create(['email' => 'cook@example.com']);

        // Recipe 1: Pasta with tomato sauce
        $pastaRecipe = Recipe::factory()->create([
            'author_id' => $user1->id,
            'name' => 'Pasta with Tomato Sauce',
            'description' => 'A simple Italian dish with pasta and tomato sauce'
        ]);

        RecipeIngredient::factory()->create([
            'recipe_id' => $pastaRecipe->id,
            'name' => 'Spaghetti pasta'
        ]);

        RecipeIngredient::factory()->create([
            'recipe_id' => $pastaRecipe->id,
            'name' => 'Tomato sauce'
        ]);

        RecipeStep::factory()->create([
            'recipe_id' => $pastaRecipe->id,
            'instruction' => 'Boil the pasta until al dente',
            'step_order' => 1
        ]);

        // Recipe 2: Potato salad
        $potatoRecipe = Recipe::factory()->create([
            'author_id' => $user2->id,
            'name' => 'Classic Potato Salad',
            'description' => 'A delicious potato salad for picnics'
        ]);

        RecipeIngredient::factory()->create([
            'recipe_id' => $potatoRecipe->id,
            'name' => '4 large potatoes'
        ]);

        RecipeStep::factory()->create([
            'recipe_id' => $potatoRecipe->id,
            'instruction' => 'Mix with mayonnaise and seasoning',
            'step_order' => 1
        ]);

        // Recipe 3: Vegan pasta dish
        $veganPasta = Recipe::factory()->create([
            'author_id' => $user1->id,
            'name' => 'Vegan Pasta Primavera',
            'description' => 'Healthy pasta with vegetables'
        ]);

        RecipeIngredient::factory()->create([
            'recipe_id' => $veganPasta->id,
            'name' => 'Penne pasta'
        ]);
    }
}
