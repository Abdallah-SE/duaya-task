<template>
  <svg 
    :class="iconClass" 
    fill="none" 
    viewBox="0 0 24 24" 
    stroke="currentColor"
  >
    <path 
      stroke-linecap="round" 
      stroke-linejoin="round" 
      stroke-width="2" 
      :d="iconPath" 
    />
  </svg>
</template>

<script setup>
defineProps({
  iconPath: {
    type: String,
    required: true
  },
  iconClass: {
    type: String,
    default: 'mr-3 h-5 w-5'
  }
})
</script>
