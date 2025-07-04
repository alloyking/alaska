<?php

declare(strict_types=1);

namespace App\Domain\Recipe\Search\Repositories;

use App\Domain\Recipe\Search\ValueObjects\SearchCriteria;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Recipe;
use Illuminate\Support\Facades\Log;
use App\Domain\Recipe\Search\Interfaces\RecipeSearchInterface;

final class EloquentNaturalLanguageRecipeSearch implements RecipeSearchInterface
{
    public function search(SearchCriteria $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Recipe::query()->with(['author', 'ingredients', 'steps']);

        // Filter by author email
        if ($email = $filters->authorEmail()) {
            $query->whereHas('author', function ($query) use ($email) {
                $query->where('email', $email);
            });
        }

        // Full-text keyword search (https://laravel-news.com/whereFullText)
        // Natural Language mode allows for more flexible searching,
        if ($keyword = $filters->keyword()) {
            $query->where(function ($query) use ($keyword) {

                $clean = trim($keyword, "\"' ");
                $words = preg_split('/\s+/', $clean);

                $naturalQuery = count($words) > 1
                    // exact-phrase match: "Pasta Fish"
                    ? '"' . $clean . '"'
                    // single-term match: Pasta
                    : $clean;

                // (omitting the 'mode' option would also default to natural)
                $query->whereFullText(['name','description'], $naturalQuery, ['mode' => 'natural']);

                $query->orWhereHas('ingredients', function ($q) use ($naturalQuery) {
                    $q->whereFullText('name', $naturalQuery, ['mode' => 'natural']);
                });

                $query->orWhereHas('steps', function ($q) use ($naturalQuery) {
                    $q->whereFullText('instruction', $naturalQuery, ['mode' => 'natural']);
                });
            });
        }

        // Filter by ingredient
        if ($ingredient = $filters->ingredient()) {
            $query->whereHas('ingredients', function ($query) use ($ingredient) {
                $query->whereFullText('name', $ingredient.'*', ['mode' => 'boolean']);
            });
        }
        Log::debug($query->toSql());

        return $query->distinct()->paginate($perPage);
    }
}
