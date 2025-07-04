<?php

declare(strict_types=1);

namespace App\Domain\Recipe\Search\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Recipe\Search\Interfaces\RecipeSearchInterface;
use App\Domain\Recipe\Search\Repositories\EloquentBooleanRecipeSearch;
use App\Domain\Recipe\Search\Repositories\EloquentNaturalLanguageRecipeSearch;

final class RecipeSearchProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(RecipeSearchInterface::class, function ($app) {
            return new EloquentBooleanRecipeSearch();
            //return new EloquentNaturalLanguageRecipeSearch();
        });
    }
}
