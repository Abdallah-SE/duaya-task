<template>
  <div class="px-6 py-4 border-b border-gray-200">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
          <span class="text-sm font-medium text-indigo-600">
            {{ userInitial }}
          </span>
        </div>
      </div>
      <div class="ml-3">
        <p class="text-sm font-medium text-gray-900">{{ userName }}</p>
        <p class="text-xs text-gray-500">{{ userEmail }}</p>
        <div v-if="jobTitle" class="mt-1">
          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
            {{ jobTitle }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const userInitial = computed(() => {
  return props.user?.name?.charAt(0)?.toUpperCase() || 'U'
})

const userName = computed(() => {
  return props.user?.name || 'User'
})

const userEmail = computed(() => {
  return props.user?.email || 'user@example.com'
})

const jobTitle = computed(() => {
  return props.user?.employee?.job_title || null
})
</script>
