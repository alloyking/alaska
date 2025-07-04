<template>
    <div class="container mx-auto px-4 py-8">
        <div v-if="loading" class="flex justify-center my-12">
            <div class="animate-pulse text-center">
                <div class="text-gray-500">Loading recipe...</div>
                <div class="mt-2 w-12 h-12 border-4 border-indigo-300 border-t-indigo-600 rounded-full animate-spin mx-auto"></div>
            </div>
        </div>

        <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 my-8 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-red-600 font-medium">Error: {{ error.message }}</p>
                </div>
            </div>
        </div>

        <div v-else>
            <div class="mb-6">
                <button @click="goBack" class="flex items-center text-indigo-600 hover:text-indigo-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to recipes
                </button>
            </div>

            <RecipeCard v-if="recipe" :recipe="recipe" />

            <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                <p class="text-gray-500">Recipe not found.</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import RecipeCard from '@/components/RecipeCard.vue';
import { Recipe } from '@/types/recipe';

const route = useRoute();
const router = useRouter();
const recipe = ref<Recipe | null>(null);
const loading = ref(false);
const error = ref(null);

async function fetchRecipe() {
    const slug = route.params.slug;
    if (!slug) return;

    loading.value = true;
    error.value = null;

    try {
        const { data } = await axios.get(`/api/recipes/${slug}`);
        recipe.value = data.data;
    } catch (err) {
        error.value = err;
    } finally {
        loading.value = false;
    }
}

function goBack() {
    router.back();
}

onMounted(() => {
    fetchRecipe();
});
</script>
