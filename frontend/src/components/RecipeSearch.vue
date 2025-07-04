<template>
    <div class="bg-white p-4 pb-8 z-10 ">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Find Recipes</h2>
        <form @submit.prevent="emitSearch" class="flex flex-col md:flex-row gap-4">
            <!-- Loop through all filter fields -->
            <div v-for="(field, key) in fieldDefinitions" :key="key" class="flex-1 relative">
                <input
                    v-model="filters[key]"
                    :type="field.type"
                    :placeholder="field.placeholder"
                    class="w-full text-gray-700 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <button
                    v-if="filters[key]"
                    @click="clearField(key)"
                    type="button"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    :aria-label="`Clear ${field.placeholder.toLowerCase()}`"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <button
                type="submit"
                class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded hover:bg-indigo-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                Search
            </button>
        </form>
    </div>
</template>

<script setup>
import {reactive} from 'vue'

const filters = reactive({
    keyword: '',
    authorEmail: '',
    ingredient: '',
})

const fieldDefinitions = {
    keyword: {
        type: 'text',
        placeholder: 'Keyword'
    },
    authorEmail: {
        type: 'email',
        placeholder: 'Author Email'
    },
    ingredient: {
        type: 'text',
        placeholder: 'Ingredient'
    }
}

const emit = defineEmits(['search'])

function emitSearch() {
    emit('search', {...filters})
}

function clearField(field) {
    filters[field] = ''
    emitSearch();
}
</script>
