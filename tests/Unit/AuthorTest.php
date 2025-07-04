<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Recipe;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_author_has_many_recipes()
    {
        $author = Author::factory()->create();
        Recipe::factory()->count(3)->create(['author_id' => $author->id]);

        $this->assertCount(3, $author->recipes);
        $this->assertInstanceOf(Recipe::class, $author->recipes->first());
    }

    public function test_author_model_has_correct_fillable_attributes()
    {
        $author = new Author();
        $this->assertEquals(['uuid', 'email'], $author->getFillable());
    }

    public function test_email_must_be_unique()
    {
        $this->expectException(QueryException::class);

        $email = 'unique@example.com';
        Author::factory()->create(['email' => $email]);
        Author::factory()->create(['email' => $email]); // Should fail
    }

    public function test_can_create_author()
    {
        $author = Author::factory()->create();
        $this->assertInstanceOf(Author::class, $author);
        $this->assertNotNull($author->id);
        $this->assertNotNull($author->email);
    }
}
