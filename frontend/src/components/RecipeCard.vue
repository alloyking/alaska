<template>
    <div
        class="bg-white border border-gray-200 rounded-lg shadow-md p-6 text-gray-600 hover:shadow-lg transition-shadow duration-300">
        <h2 class="text-2xl font-bold mb-2 text-indigo-800">
            <a :href="recipe.slug" class="inline-flex items-center gap-1 p-5 -m-5">
                {{ recipe.name || 'Untitled Recipe' }}
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
            </a>
        </h2>
        <p class="text-sm text-gray-500 italic mb-4">
            By: {{recipe.author.email }}
        </p>
        <div class="grid md:grid-cols-1 mt-8">
            <section>
                <h3 class="text-lg font-medium mb-3 text-indigo-600 border-b border-indigo-100 pb-1">Description</h3>
                <p class="max-w-prose">
                    {{ recipe.description || 'No description provided.' }}
                </p>
            </section>
        </div>
        <div class="grid md:grid-cols-2 mt-8">
            <section class="mb-4">
                <h3 class="text-lg font-medium mb-3 text-indigo-600 border-b border-indigo-100 pb-1">Ingredients</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li v-for="(ing, idx) in recipe.ingredients" :key="idx" class="pl-2">
                        {{ ing.quantity }} <span class="font-medium text-gray-800">{{ ing.name }}</span>
                    </li>
                </ul>
            </section>
            <section>
                <h3 class="text-lg font-medium mb-3 text-indigo-600 border-b border-indigo-100 pb-1">Steps</h3>
                <ol class="list-decimal list-outside space-y-2 ml-5">
                    <li v-for="step in recipe.steps" :key="step.step_order" class="pl-1">
                        {{ step.instruction }}
                    </li>
                </ol>
            </section>
        </div>
    </div>
</template>

<script setup>
defineProps({
    recipe: {
        type: Object,
        required: true
    }
})
</script>
