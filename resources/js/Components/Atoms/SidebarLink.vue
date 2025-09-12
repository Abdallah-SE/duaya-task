<template>
  <Link 
    :href="href" 
    :class="linkClass"
  >
    <SidebarIcon 
      v-if="iconPath" 
      :icon-path="iconPath" 
    />
    <span>{{ label }}</span>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import SidebarIcon from './SidebarIcon.vue'

defineProps({
  href: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  iconPath: String,
  linkClass: {
    type: String,
    default: 'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200'
  }
})
</script>
