<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'author' => $this->author->only('name', 'email'),
            'ingredients' => $this->ingredients->map->only('name', 'quantity', 'sort_order'),
            'steps' => $this->steps->map->only('step_order', 'instruction'),
        ];
    }
}
