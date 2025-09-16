<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Main content area -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

onMounted(() => {
  // Initialize the SPA
  console.log('App layout mounted with Inertia.js')
})
</script>
