<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Recipe\Search\Interfaces\RecipeSearchInterface;
use App\Http\Requests\SearchRecipesRequest;
use App\Http\Resources\RecipeCollection;
use App\Models\Recipe;
use App\Http\Resources\RecipeResource;


final readonly class RecipeController
{
    public function search(
        SearchRecipesRequest $request,
        RecipeSearchInterface $searchService
    ) {

        $request->validated();
        $perPage = $request->per_page ?? 8;
        $criteria = $request->getCriteria();
        $paginator = $searchService->search($criteria, $perPage);

        return new RecipeCollection($paginator);
    }

    public function show(Recipe $recipe)
    {
        return new RecipeResource($recipe->load(['author', 'ingredients', 'steps']));
    }
}
