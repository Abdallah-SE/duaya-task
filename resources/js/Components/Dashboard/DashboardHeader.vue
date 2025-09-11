<template>
  <div class="dashboard-header">
    <div class="header-content">
      <div class="header-text">
        <h1 class="header-title">{{ title }}</h1>
        <p class="header-subtitle">{{ subtitle }}</p>
      </div>
      
      <div class="header-actions">
        <button
          v-for="action in actions"
          :key="action.id"
          @click="$emit('action-click', action.id)"
          :disabled="action.loading"
          :class="getActionClasses(action)"
        >
          <svg 
            v-if="action.loading" 
            class="animate-spin -ml-1 mr-2 h-4 w-4" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <component 
            v-else-if="action.icon" 
            :is="getIconComponent(action.icon)" 
            class="w-4 h-4 mr-2" 
          />
          {{ action.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  actions: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['action-click'])

const getActionClasses = (action) => {
  const baseClasses = 'inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed'
  
  const variantClasses = {
    primary: 'border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
    secondary: 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500',
    outline: 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500',
    danger: 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
    success: 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500'
  }
  
  return `${baseClasses} ${variantClasses[action.variant] || variantClasses.primary}`
}

const getIconComponent = (iconName) => {
  const icons = {
    plus: 'PlusIcon',
    download: 'DownloadIcon',
    refresh: 'RefreshIcon',
    settings: 'CogIcon',
    filter: 'FunnelIcon',
    export: 'ArrowDownTrayIcon'
  }
  
  return icons[iconName] || 'div'
}
</script>

<style scoped>
.dashboard-header {
  @apply bg-white shadow-sm border-b border-gray-200;
}

.header-content {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6;
}

.header-text {
  @apply flex-1 min-w-0;
}

.header-title {
  @apply text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate;
}

.header-subtitle {
  @apply mt-1 text-sm text-gray-500;
}

.header-actions {
  @apply mt-4 flex flex-col sm:flex-row sm:mt-0 sm:ml-4 space-y-3 sm:space-y-0 sm:space-x-3;
}

@media (min-width: 640px) {
  .header-content {
    @apply flex items-center justify-between;
  }
  
  .header-actions {
    @apply mt-0;
  }
}
</style>
