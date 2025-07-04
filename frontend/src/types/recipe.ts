export interface Author {
    name?: string;
    email: string;
}

export interface Ingredient {
    name: string;
    quantity: string;
}

export interface Step {
    step_order: number;
    instruction: string;
}

export interface Recipe {
    id: number | string;
    name: string;
    author: Author;
    description?: string;
    ingredients: Ingredient[];
    steps: Step[];
}

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

export interface RecipeResponse {
    data: Recipe[];
    links: PaginationLinks;
}
