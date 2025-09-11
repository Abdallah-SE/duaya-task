<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div :class="[
      'fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
      isOpen ? 'translate-x-0' : '-translate-x-full'
    ]">
      <!-- Sidebar Header -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <div class="ml-3">
            <h2 class="text-lg font-semibold text-gray-900">Duaya Task</h2>
            <p class="text-xs text-gray-500">Activity Monitor</p>
          </div>
        </div>
        <!-- Close button for mobile -->
        <button @click="$emit('close')" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- User Info -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-indigo-600">
                {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
              </span>
            </div>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-900">{{ user?.name || 'User' }}</p>
            <p class="text-xs text-gray-500">{{ user?.email || 'user@example.com' }}</p>
            <div v-if="user?.employee" class="mt-1">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                {{ user.employee.job_title }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="mt-6 px-3">
        <div class="space-y-1">
          <!-- Dashboard -->
          <Link :href="dashboardRoute" 
                :class="[
                  'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                  isActiveRoute(dashboardRoute) 
                    ? 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-500' 
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                ]">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
            </svg>
            Dashboard
          </Link>

          <!-- Users Management -->
          <Link :href="usersRoute" 
                :class="[
                  'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                  isActiveRoute(usersRoute) 
                    ? 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-500' 
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                ]">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            Users
          </Link>

          <!-- Employees Management -->
          <Link :href="employeesRoute" 
                :class="[
                  'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                  isActiveRoute(employeesRoute) 
                    ? 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-500' 
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                ]">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Employees
          </Link>

          <!-- Divider -->
          <div class="border-t border-gray-200 my-4"></div>

          <!-- Settings (if needed) -->
          <Link href="#" 
                class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
          </Link>

          <!-- Logout -->
          <button @click="logout" 
                  class="group flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
          </button>
        </div>
      </nav>
    </div>

    <!-- Mobile overlay -->
    <div v-if="isOpen" 
         @click="$emit('close')" 
         class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

// Determine routes based on user role
const dashboardRoute = computed(() => {
  return props.user?.hasRole?.('admin') ? '/admin/dashboard' : '/employee/dashboard'
})

const usersRoute = computed(() => {
  return props.user?.hasRole?.('admin') ? '/admin/users' : '/employee/users'
})

const employeesRoute = computed(() => {
  return props.user?.hasRole?.('admin') ? '/admin/employees' : '/employee/employees'
})

// Check if current route is active
const isActiveRoute = (route) => {
  return window.location.pathname === route
}

// Logout function
const logout = () => {
  router.post('/logout')
}
</script>
