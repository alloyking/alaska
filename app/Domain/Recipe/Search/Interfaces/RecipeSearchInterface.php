<?php

declare(strict_types=1);

namespace App\Domain\Recipe\Search\Interfaces;

use App\Domain\Recipe\Search\ValueObjects\SearchCriteria;

interface RecipeSearchInterface
{
    public function search(SearchCriteria $filters): iterable;
}
