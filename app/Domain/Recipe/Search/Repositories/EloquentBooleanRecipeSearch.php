<?php

declare(strict_types=1);

namespace App\Domain\Recipe\Search\Repositories;

use App\Domain\Recipe\Search\Interfaces\RecipeSearchInterface;
use App\Domain\Recipe\Search\ValueObjects\SearchCriteria;
use App\Models\Recipe;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

final class EloquentBooleanRecipeSearch implements RecipeSearchInterface
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
        // TODO - one might explore more on using BOOLEAN MODE here.
        // Because if you did:
        // WHERE MATCH(name,description) AGAINST('+vegan +salad -meat' IN BOOLEAN MODE).  You could get Vegan Salad recipes, but not meat ones.
        // Which if we had to weight certain fields like that in this project, it would be useful.
        // at the very least boolean mode allows  $keyword.'*' and "phrase" searches.

        if ($keyword = $filters->keyword()) {
            $query->where(function ($query) use ($keyword) {

                $terms = preg_split('/\s+/', trim($keyword));

                if (count($terms) > 1) {
                    // multi-word exact-phrase match (instruction are not clearly defined in the spec.)
                    // what if the keyword is "Pasta Fish"? I don't want to search for "Pasta" and "Fish" separately, but as a phrase.
                    $escaped = str_replace('"', '\"', trim($keyword));
                    // --> +"Pasta Fish"
                    $booleanQuery = '+"' . $escaped . '"';
                } else {
                    // single word is an exact term match (no wildcard) here either.
                    // --> "+Pasta"
                    $booleanQuery = '+' . $terms[0];
                }

                // name & description
                $query->whereFullText(['name', 'description'], $booleanQuery, ['mode' => 'boolean']);

                // ingredients
                $query->orWhereHas('ingredients', function ($q) use ($booleanQuery) {
                    $q->whereFullText('name', $booleanQuery, ['mode' => 'boolean']);
                });

                // steps
                $query->orWhereHas('steps', function ($q) use ($booleanQuery) {
                    $q->whereFullText('instruction', $booleanQuery, ['mode' => 'boolean']);
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
