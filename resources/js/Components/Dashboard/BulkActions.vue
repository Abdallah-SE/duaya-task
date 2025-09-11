<template>
  <div class="bulk-actions" v-if="selectedItems.length > 0">
    <div class="bulk-header">
      <span class="selection-count">{{ selectedItems.length }} selected</span>
      <button @click="$emit('clear-selection')" class="clear-selection-btn">
        Clear
      </button>
    </div>
    
    <div class="bulk-actions-list">
      <button
        v-for="action in availableActions"
        :key="action.id"
        @click="$emit('action-click', action.id)"
        :disabled="action.loading"
        :class="getActionClasses(action)"
      >
        <component :is="getIconComponent(action.icon)" class="w-4 h-4 mr-2" />
        {{ action.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  selectedItems: {
    type: Array,
    default: () => []
  },
  availableActions: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['action-click', 'clear-selection'])

const getActionClasses = (action) => {
  const baseClasses = 'inline-flex items-center px-3 py-2 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed'
  
  const variantClasses = {
    primary: 'border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
    secondary: 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500',
    danger: 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
    warning: 'border-transparent text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500'
  }
  
  return `${baseClasses} ${variantClasses[action.variant] || variantClasses.secondary}`
}

const getIconComponent = (iconName) => {
  const icons = {
    download: 'DownloadIcon',
    'user-minus': 'UserMinusIcon',
    trash: 'TrashIcon',
    edit: 'PencilIcon',
    'arrow-up': 'ArrowUpIcon',
    'arrow-down': 'ArrowDownIcon'
  }
  
  return icons[iconName] || 'div'
}
</script>

<style scoped>
.bulk-actions {
  @apply bg-indigo-50 border border-indigo-200 rounded-lg p-4;
}

.bulk-header {
  @apply flex items-center justify-between mb-3;
}

.selection-count {
  @apply text-sm font-medium text-indigo-900;
}

.clear-selection-btn {
  @apply text-sm text-indigo-600 hover:text-indigo-800;
}

.bulk-actions-list {
  @apply flex flex-wrap gap-2;
}
</style>
