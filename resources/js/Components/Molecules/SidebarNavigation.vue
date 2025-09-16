<template>
  <nav class="mt-6 px-3">
    <div class="space-y-1">
      <SidebarLink
        v-for="item in navigationItems"
        :key="item.href"
        :href="item.href"
        :label="item.label"
        :icon-path="item.iconPath"
        :link-class="getLinkClass(item.href)"
      />
      
      <!-- Divider -->
      <div class="border-t border-gray-200 my-4"></div>
      
      <!-- Settings -->
      <SidebarLink
        :href="settingsRoute"
        label="Settings"
        icon-path="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
        :link-class="getLinkClass(settingsRoute)"
      />

      <!-- Logout -->
      <div class="px-3 py-2">
        <LogoutButton />
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import SidebarLink from '@/Components/Atoms/SidebarLink.vue'
import SidebarButton from '@/Components/Atoms/SidebarButton.vue'
import LogoutButton from '@/Components/Atoms/LogoutButton.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['logout'])

// Navigation items based on user role
const navigationItems = computed(() => {
  const baseItems = [
    {
      href: props.user?.has_admin_role ? '/admin/dashboard' : '/employee/dashboard',
      label: 'Dashboard',
      iconPath: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z'
    }
  ]

  // Only add Users and Employees links for employee users (not admin)
  if (!props.user?.has_admin_role) {
    baseItems.push(
      {
        href: '/employee/users',
        label: 'Users',
        iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'
      },
      {
        href: '/employee/employees',
        label: 'Employees',
        iconPath: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
      }
    )
  }

  return baseItems
})

const settingsRoute = computed(() => {
  return props.user?.has_admin_role ? '/admin/settings' : '/employee/settings'
})

const getLinkClass = (href) => {
  const isActive = window.location.pathname === href
  const baseClass = 'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200'
  
  if (isActive) {
    return `${baseClass} bg-indigo-100 text-indigo-700 border-r-2 border-indigo-500`
  }
  
  return `${baseClass} text-gray-600 hover:bg-gray-50 hover:text-gray-900`
}
</script>
