<?php

use App\Domain\Recipe\Search\Repositories\EloquentBooleanRecipeSearch;
use \App\Domain\Recipe\Search\Repositories\EloquentNaturalLanguageRecipeSearch;
use App\Domain\Recipe\Search\ValueObjects\SearchCriteria;
use App\Models\Author;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


uses(TestCase::class, DatabaseMigrations::class);

// Parameterized search tests
$implementations = [
    EloquentBooleanRecipeSearch::class,
    EloquentNaturalLanguageRecipeSearch::class,
];

function setupTestData(): void
{
    // Create users with different emails
    $user1 = Author::factory()->create(['email' => 'chef@example.com']);
    $user2 = Author::factory()->create(['email' => 'cook@example.com']);

    // Recipe 1: Pasta with tomato sauce
    $pastaRecipe = Recipe::factory()->create([
        'author_id' => $user1->id,
        'name' => 'Pasta with Tomato Sauce',
        'description' => 'A simple Italian dish with pasta and tomato sauce',
    ]);

    RecipeIngredient::factory()->create([
        'recipe_id' => $pastaRecipe->id,
        'name' => 'Spaghetti pasta',
    ]);

    RecipeIngredient::factory()->create([
        'recipe_id' => $pastaRecipe->id,
        'name' => 'Tomato sauce',
    ]);

    RecipeStep::factory()->create([
        'recipe_id' => $pastaRecipe->id,
        'instruction' => 'Boil the pasta until al dente',
        'step_order' => 1,
    ]);

    // Recipe 2: Potato salad
    $potatoRecipe = Recipe::factory()->create([
        'author_id' => $user2->id,
        'name' => 'Classic Potato Salad',
        'description' => 'A delicious potato salad for picnics',
    ]);

    RecipeIngredient::factory()->create([
        'recipe_id' => $potatoRecipe->id,
        'name' => '4 large potatoes',
    ]);

    RecipeStep::factory()->create([
        'recipe_id' => $potatoRecipe->id,
        'instruction' => 'Mix with mayonnaise and seasoning',
        'step_order' => 1,
    ]);

    // Recipe 3: Vegan pasta dish
    $veganPasta = Recipe::factory()->create([
        'author_id' => $user1->id,
        'name' => 'Vegan Pasta Primavera',
        'description' => 'Healthy pasta with vegetables',
    ]);

    RecipeIngredient::factory()->create([
        'recipe_id' => $veganPasta->id,
        'name' => 'Penne pasta',
    ]);

    // add Bánh mì recipe
    $banhMi = Recipe::factory()->create([
        'author_id' => $user1->id,
        'name' => 'Bánh mì',
        'description' => 'A Vietnamese sandwich with pork and vegetables',
    ]);
    RecipeIngredient::factory()->create([
        'recipe_id' => $banhMi->id,
        'name' => 'Baguette',
    ]);
    RecipeStep::factory()->create([
        'recipe_id' => $banhMi->id,
        'instruction' => 'Assemble the sandwich with pork and vegetables',
        'step_order' => 1,
    ]);

}

// Database configuration tests
test('database collation is utf8mb4_0900_ai_ci', function () {
    setupTestData();
    $collation = DB::selectOne('SELECT @@collation_database as collation');
    expect($collation->collation)->toBe('utf8mb4_0900_ai_ci');
});

test('fulltext index exists', function () {
    setupTestData();
    $indexes = DB::select(
        "SHOW INDEX FROM recipes
         WHERE Index_type = 'FULLTEXT'
         AND Key_name = 'recipes_name_description_fulltext'"
    );
    expect($indexes)->not->toBeEmpty();
});

test('database is mysql', function () {
    expect(config('database.default'))->toBe('mysql')
        ->and(DB::connection()->getDatabaseName())->toContain('testing');
});

test('search by keyword in name', function (string $implementationClass) {
    setupTestData();
    $searchRepo = app()->make($implementationClass);

    $criteria = new SearchCriteria(keyword: 'Pasta');
    $results = $searchRepo->search($criteria);

    expect($results->total())->toBe(2)
        ->and($results->getCollection()->pluck('name'))->toContain('Pasta with Tomato Sauce')
        ->and($results->getCollection()->pluck('name'))->toContain('Vegan Pasta Primavera');
})->with($implementations);

test('search by keyword non-existing multiword', function (string $implementationClass) {
    setupTestData();
    $searchRepo = app()->make($implementationClass);

    $criteria = new SearchCriteria(keyword: 'Pasta Fish');
    $results = $searchRepo->search($criteria);
    expect($results->total())->toBe(0);
})->with($implementations);

test('search by author email', function (string $implementationClass) {
    setupTestData();
    $searchRepo = app()->make($implementationClass);

    $criteria = new SearchCriteria(authorEmail: 'chef@example.com');
    $results = $searchRepo->search($criteria);

    expect($results->total())->toBe(3)
        ->and($results->getCollection()->pluck('name'))->toContain('Pasta with Tomato Sauce')
        ->and($results->getCollection()->pluck('name'))->toContain('Vegan Pasta Primavera');

    $criteria = new SearchCriteria(authorEmail: 'cook@example.com');
    $results = $searchRepo->search($criteria);
    expect($results->total())->toBe(1)
        ->and($results->getCollection()->pluck('name'))->toContain('Classic Potato Salad');
})->with($implementations);

// [Continue with all other search tests in the same pattern...]

// Non-search related tests
test('recipe has ordered steps', function () {
    $recipe = Recipe::factory()->create();

    RecipeIngredient::factory()->count(3)
        ->create(['recipe_id' => $recipe->id]);

    RecipeStep::factory()->count(3)
        ->state(new Sequence(
            fn ($s) => ['step_order' => 3 - $s->index]
        ))
        ->create(['recipe_id' => $recipe->id]);

    $recipe->load(['ingredients', 'steps']);
    expect($recipe->steps->pluck('step_order')->all())->toBe([1, 2, 3]);
});

test('duplicate step order throws exception', function () {
    $recipe = Recipe::factory()->create();
    RecipeStep::factory()->create([
        'recipe_id' => $recipe->id,
        'step_order' => 1,
    ]);

    expect(fn () => RecipeStep::factory()->create([
        'recipe_id' => $recipe->id,
        'step_order' => 1,
    ]))->toThrow(QueryException::class);
});

test('search Bánh mì without accent', function (string $implementationClass) {
    setupTestData();
    $searchRepo = app()->make($implementationClass);

    $criteria = new SearchCriteria(keyword: "Banh mi");
    $results = $searchRepo->search($criteria);
    expect($results->total())->toBe(1);

    $results = $searchRepo->search(new SearchCriteria(keyword: 'Bánh mì'));
    expect($results->total())->toBe(1)
        ->and($results->getCollection()->pluck('name'))->toContain('Bánh mì');
})->with($implementations);
