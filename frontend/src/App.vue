<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <header>
  </header>

    <main class="w-full flex justify-center">
        <div class="w-full max-w-4xl px-4">
            <RouterView />
        </div>
    </main>
</template>
