<template>
  <button 
    :class="buttonClass"
    @click="$emit('click')"
  >
    <SidebarIcon 
      v-if="iconPath" 
      :icon-path="iconPath" 
    />
    <span v-if="label">{{ label }}</span>
  </button>
</template>

<script setup>
import SidebarIcon from './SidebarIcon.vue'

defineProps({
  label: String,
  iconPath: String,
  buttonClass: {
    type: String,
    default: 'group flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200'
  }
})

defineEmits(['click'])
</script>
