<template>
  <div class="quick-actions">
    <div class="actions-grid">
      <button
        v-for="action in actions"
        :key="action.id"
        @click="$emit('action-click', action.id)"
        :disabled="action.loading"
        :class="getActionClasses(action)"
      >
        <div class="action-icon">
          <component :is="getIconComponent(action.icon)" class="w-6 h-6" />
        </div>
        <div class="action-content">
          <span class="action-label">{{ action.label }}</span>
          <span v-if="action.count !== undefined" class="action-count">{{ action.count }}</span>
        </div>
        <div v-if="action.loading" class="action-loading">
          <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  actions: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['action-click'])

const getActionClasses = (action) => {
  const baseClasses = 'relative flex items-center p-4 border rounded-lg shadow-sm hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed'
  
  const variantClasses = {
    success: 'border-green-200 bg-green-50 hover:bg-green-100 text-green-800',
    warning: 'border-yellow-200 bg-yellow-50 hover:bg-yellow-100 text-yellow-800',
    danger: 'border-red-200 bg-red-50 hover:bg-red-100 text-red-800',
    info: 'border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-800',
    primary: 'border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-800'
  }
  
  return `${baseClasses} ${variantClasses[action.variant] || variantClasses.primary}`
}

const getIconComponent = (iconName) => {
  const icons = {
    users: 'UsersIcon',
    clock: 'ClockIcon',
    exclamation: 'ExclamationTriangleIcon',
    activity: 'ActivityIcon',
    'user-check': 'UserCheckIcon',
    'user-x': 'UserXIcon'
  }
  
  return icons[iconName] || 'div'
}
</script>

<style scoped>
.quick-actions {
  @apply w-full;
}

.actions-grid {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4;
}

.action-icon {
  @apply flex-shrink-0;
}

.action-content {
  @apply ml-3 flex-1 min-w-0;
}

.action-label {
  @apply block text-sm font-medium;
}

.action-count {
  @apply block text-xs opacity-75;
}

.action-loading {
  @apply absolute top-2 right-2;
}
</style>
