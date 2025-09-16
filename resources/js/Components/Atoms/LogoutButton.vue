<template>
  <button
    @click="handleLogout"
    :disabled="loading"
    :class="[
      'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white transition-colors duration-200',
      loading 
        ? 'bg-gray-400 cursor-not-allowed' 
        : 'bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500'
    ]"
  >
    <svg 
      v-if="loading" 
      class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
      fill="none" 
      viewBox="0 0 24 24"
    >
      <circle 
        class="opacity-25" 
        cx="12" 
        cy="12" 
        r="10" 
        stroke="currentColor" 
        stroke-width="4"
      ></circle>
      <path 
        class="opacity-75" 
        fill="currentColor" 
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <svg 
      v-else 
      class="-ml-1 mr-2 h-4 w-4" 
      fill="none" 
      viewBox="0 0 24 24" 
      stroke="currentColor"
    >
      <path 
        stroke-linecap="round" 
        stroke-linejoin="round" 
        stroke-width="2" 
        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
      />
    </svg>
    {{ loading ? 'Logging out...' : 'Logout' }}
  </button>
</template>

<script setup>
import { ref } from 'vue'
import { useInertiaAuth } from '@/Composables/useInertiaAuth'

const { smartLogout } = useInertiaAuth()
const loading = ref(false)

const handleLogout = async () => {
  if (loading.value) return
  
  loading.value = true
  
  try {
    await smartLogout({
      onSuccess: () => {
        console.log('Logout successful')
      },
      onError: (errors) => {
        console.error('Logout error:', errors)
        loading.value = false
      }
    })
  } catch (error) {
    console.error('Logout failed:', error)
    loading.value = false
  }
}
</script>
