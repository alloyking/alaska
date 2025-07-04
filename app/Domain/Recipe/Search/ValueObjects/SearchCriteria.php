<?php

declare(strict_types=1);

namespace App\Domain\Recipe\Search\ValueObjects;

final readonly class SearchCriteria
{
    public function __construct(
        private ?string $authorEmail = null,
        private ?string $keyword = null,
        private ?string $ingredient = null,
    ) {}

    public function authorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function keyword(): ?string
    {
        return $this->keyword;
    }

    public function ingredient(): ?string
    {
        return $this->ingredient;
    }
}
