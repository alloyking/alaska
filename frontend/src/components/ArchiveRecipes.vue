// filepath: /Users/timshultis/Sites/alaska/frontend/src/components/ArchiveRecipes.vue
<template>
    <div class="px-4 py-6">
        <div class="bg-white p-4 pb-8 z-10 shadow-md rounded-lg">
            <RecipeSearch @search="handleSearch" />
        </div>
        <div class="my-4 pt-4"></div>
        <div>
            <div v-if="loading" class="flex justify-center my-12">
                <div class="animate-pulse text-center">
                    <div class="text-gray-500">Loading recipes...</div>
                    <div
                        class="mt-2 w-12 h-12 border-4 border-indigo-300 border-t-indigo-600 rounded-full animate-spin mx-auto"></div>
                </div>
            </div>
            <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 my-8 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-red-600 font-medium">Error: {{ error.message }}</p>
                    </div>
                </div>
            </div>
            <div v-else>
                <ul v-if="recipes.length" class="grid gap-4">
                    <li v-for="recipe in recipes" :key="recipe.id">
                        <RecipeCard :recipe="recipe" />
                    </li>
                </ul>

                <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                    <p class="text-gray-500">No recipes found matching your criteria.</p>
                </div>
                <!-- Pagination -->
                <nav v-if="links" class="flex justify-center space-x-5 mt-8 pt-4">
                    <button
                        v-if="links.prev"
                        @click="handlePagination(links.prev)"
                        class="px-4 py-2 border rounded-md transition-colors border-gray-300 text-gray-700 hover:bg-gray-100"
                    >
                        Previous
                    </button>
                    <button
                        v-if="links.next"
                        @click="handlePagination(links.next)"
                        class="px-4 py-2 border rounded-md transition-colors border-gray-300 text-gray-700 hover:bg-gray-100"
                    >
                        Next
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import RecipeSearch from './RecipeSearch.vue';
import RecipeCard from './RecipeCard.vue';
import { Recipe, PaginationLinks, RecipeResponse } from '@/types/recipe';

const recipes = ref<Recipe[]>([])
const links = ref<PaginationLinks | null>(null)
const loading = ref(true) // Changed from false to true
const error = ref(null)
const searchFilters = reactive({
    keyword: '',
    authorEmail: '',
    ingredient: '',
})

async function fetchRecipes(page = 1) {
    loading.value = true
    error.value = null

    try {
        const params = {
            page,
            keyword: searchFilters.keyword,
            author_email: searchFilters.authorEmail,
            ingredient: searchFilters.ingredient,
        }

        const { data } = await axios.get<RecipeResponse>('/api/recipes', { params })
        recipes.value = data.data
        links.value = data.links
    } catch (err) {
        error.value = err
    } finally {
        loading.value = false
    }
}

function handleSearch(filters: { keyword?: string, authorEmail?: string, ingredient?: string }) {
    Object.assign(searchFilters, filters)
    fetchRecipes(1)
}

function handlePagination(url: string) {
    if (!url) return
    const pageParam = new URL(url).searchParams.get('page')
    const page = pageParam ? parseInt(pageParam, 10) : 1
    fetchRecipes(page)
}

onMounted(() => {
    fetchRecipes()
})
</script>
