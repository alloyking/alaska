<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Recipe\Search\Interfaces\RecipeSearchInterface;
use App\Domain\Recipe\Search\Repositories\EloquentBooleanRecipeSearch;
use App\Domain\Recipe\Search\Repositories\EloquentNaturalLanguageRecipeSearch;
use App\Domain\Recipe\Search\ValueObjects\SearchCriteria;
use App\Models\Author;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use DatabaseMigrations;

    private RecipeSearchInterface $searchRepository;

    public function implementations(): array
    {
        return [
            'Eloquent implementation' => [
                EloquentBooleanRecipeSearch::class,
            ],
            'Alternative implementation' => [
                EloquentNaturalLanguageRecipeSearch::class,
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }

    private function setupTestData(): void
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

    public function test_database_collation_is_utf8mb4_0900_ai_ci()
    {
        $collation = DB::selectOne('SELECT @@collation_database as collation');
        $this->assertEquals('utf8mb4_0900_ai_ci', $collation->collation, 'Expected database collation to be utf8mb4_0900_ai_ci');
    }

    // arch test of sorts to confirm the database is set up correctly
    public function test_fulltext_index_exists()
    {
        $indexes = DB::select(
            "SHOW INDEX
         FROM recipes
       WHERE Index_type = 'FULLTEXT'
         AND Key_name = 'recipes_name_description_fulltext'"
        );

        $this->assertNotEmpty($indexes, 'Expected FULLTEXT index on recipes(name,description)');
    }

    // make sure were are not on SQLite since this is fulltext
    public function test_database_is_mysql()
    {
        $this->assertEquals('mysql', config('database.default'));
        $this->assertStringContainsString('testing', DB::connection()->getDatabaseName());
    }

    #[DataProvider('implementations')]
    public function test_search_by_keyword_in_name($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(keyword: 'Pasta');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(2, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Pasta with Tomato Sauce'));
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Vegan Pasta Primavera'));
    }

    #[DataProvider('implementations')]
    public function test_search_by_keyword_non_existing_multiword($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        // Search for a multi-word keyword that doesn't exist
        $criteria = new SearchCriteria(keyword: 'Pasta Fish');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(0, $results->total(), 'Expected no results for "Pasta Fish"');
    }

    #[DataProvider('implementations')]
    public function test_search_by_author_email($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(authorEmail: 'chef@example.com');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(3, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Pasta with Tomato Sauce'));
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Vegan Pasta Primavera'));

        // Test with a different email
        $criteria = new SearchCriteria(authorEmail: 'cook@example.com');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Classic Potato Salad'));
    }

    #[DataProvider('implementations')]
    public function test_search_by_keyword_in_description($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(keyword: 'Italian');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Pasta with Tomato Sauce'));
    }

    #[DataProvider('implementations')]
    public function test_search_by_keyword_in_ingredients($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(keyword: 'Spaghetti');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Pasta with Tomato Sauce'));
    }

    #[DataProvider('implementations')]
    public function test_search_by_keyword_partial_ingredients($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(keyword: 'Spaghe');
        $results = $this->searchRepository->search($criteria);

        // should return 0 results as it is a partial match
        $this->assertEquals(0, $results->total());
    }

    #[DataProvider('implementations')]
    public function test_search_by_keyword_in_steps($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(keyword: 'mayonnaise');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Classic Potato Salad'));
    }

    #[DataProvider('implementations')]
    public function test_search_by_ingredient_partial_match($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(ingredient: 'potat');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Classic Potato Salad'));
    }

    #[DataProvider('implementations')]
    public function test_search_by_combination_of_parameters($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);
        // Test combining author email and keyword
        $criteria = new SearchCriteria(
            authorEmail: 'chef@example.com',
            keyword: 'Vegan'
        );
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Vegan Pasta Primavera'));

        // Test combining author email and ingredient
        $criteria = new SearchCriteria(
            authorEmail: 'chef@example.com',
            ingredient: 'pasta'
        );
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(2, $results->total());

        // Test combining all parameters
        $criteria = new SearchCriteria(
            authorEmail: 'chef@example.com',
            keyword: 'Vegan',
            ingredient: 'penne'
        );
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Vegan Pasta Primavera'));
    }

    #[DataProvider('implementations')]
    public function test_recipe_has_ordered_steps($implementationClass)
    {

        $this->searchRepository = app()->make($implementationClass);

        // Create a recipe
        $recipe = Recipe::factory()->create();

        // Create out-of-order steps
        RecipeIngredient::factory()->count(3)
            ->create(['recipe_id' => $recipe->id]);

        RecipeStep::factory()->count(3)
            ->state(new Sequence(
                fn ($s) => ['step_order' => 3 - $s->index]
            ))
            ->create(['recipe_id' => $recipe->id]);

        // Reload relationships
        $recipe->load(['ingredients', 'steps']);

        // Assert they come back sorted
        $this->assertEquals([1, 2, 3], $recipe->steps->pluck('step_order')->all());
    }

    #[DataProvider('implementations')]
    public function test_duplicate_step_order_throws_exception($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $this->expectException(QueryException::class);

        $recipe = Recipe::factory()->create();
        RecipeStep::factory()->create([
            'recipe_id' => $recipe->id, 'step_order' => 1,
        ]);

        // second insert with same recipe_id and step_order should fail
        RecipeStep::factory()->create([
            'recipe_id' => $recipe->id, 'step_order' => 1,
        ]);
    }

    // test that we can find Bánh mì without the accent
    #[DataProvider('implementations')]
    public function test_search_banh_mi_without_accent($implementationClass)
    {
        $this->searchRepository = app()->make($implementationClass);

        $criteria = new SearchCriteria(keyword: 'Banh mi');
        $results = $this->searchRepository->search($criteria);

        $this->assertEquals(1, $results->total(), 'Expected one result for "Banh mi" without accent');

        // Now search again with the accent
        $results = $this->searchRepository->search(new SearchCriteria(keyword: 'Bánh mì'));

        $this->assertEquals(1, $results->total(), 'Expected one result for "Bánh mì" with accent');
        $this->assertTrue($results->getCollection()->pluck('name')->contains('Bánh mì'));
    }
}
