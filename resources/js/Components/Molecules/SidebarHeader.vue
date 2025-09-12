<template>
  <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
          <SidebarIcon 
            icon-path="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
            icon-class="w-5 h-5 text-white"
          />
        </div>
      </div>
      <div class="ml-3">
        <h2 class="text-lg font-semibold text-gray-900">Duaya Task</h2>
        <p class="text-xs text-gray-500">Activity Monitor</p>
      </div>
    </div>
    <!-- Close button for mobile -->
    <SidebarButton 
      v-if="showCloseButton"
      icon-path="M6 18L18 6M6 6l12 12"
      button-class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100"
      @click="$emit('close')"
    />
  </div>
</template>

<script setup>
import SidebarIcon from '@/Components/Atoms/SidebarIcon.vue'
import SidebarButton from '@/Components/Atoms/SidebarButton.vue'

defineProps({
  showCloseButton: {
    type: Boolean,
    default: true
  }
})

defineEmits(['close'])
</script>
