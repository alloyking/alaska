<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Recipe\Search\ValueObjects\SearchCriteria;

class SearchRecipesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'author_email' => ['sometimes', 'nullable', 'email'],
            'keyword'      => ['sometimes', 'nullable', 'string'],
            'ingredient'   => ['sometimes', 'nullable', 'string'],
            'page'         => ['sometimes', 'nullable', 'integer', 'min:1'],
            'per_page'     => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function getCriteria(): SearchCriteria
    {
        return new SearchCriteria(
            $this->input('author_email'),
            $this->input('keyword'),
            $this->input('ingredient'),
        );
    }
}
