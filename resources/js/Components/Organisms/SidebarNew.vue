<template>
  <!-- Sidebar -->
  <div :class="sidebarClass">
    <!-- Sidebar Header -->
    <SidebarHeader @close="$emit('close')" />

    <!-- User Info -->
    <SidebarUserInfo :user="user" />

    <!-- Navigation Menu -->
    <SidebarNavigation 
      :user="user" 
      @logout="handleLogout" 
    />
  </div>

  <!-- Mobile overlay -->
  <div 
    v-if="isOpen" 
    @click="$emit('close')" 
    class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
  ></div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarHeader from '@/Components/Molecules/SidebarHeader.vue'
import SidebarUserInfo from '@/Components/Molecules/SidebarUserInfo.vue'
import SidebarNavigation from '@/Components/Molecules/SidebarNavigation.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const sidebarClass = computed(() => {
  const baseClass = 'fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0'
  const visibilityClass = props.isOpen ? 'translate-x-0' : '-translate-x-full'
  
  return `${baseClass} ${visibilityClass}`
})

const handleLogout = () => {
  router.post('/logout')
}
</script>
