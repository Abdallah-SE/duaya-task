<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Mobile Header -->
        <div class="lg:hidden bg-white shadow-sm border-b">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <button @click="toggleSidebar" 
                                class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="ml-2 text-xl font-semibold text-gray-900">Duaya Task</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ user?.name || 'User' }}</span>
                        <LogoutButton />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar and Main Content -->
        <div class="flex h-screen">
            <!-- Sidebar -->
            <Sidebar 
                :user="user" 
                :is-open="sidebarOpen" 
                @close="closeSidebar" 
            />

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">
                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50">
                    <div class="py-6">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <slot />
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- Global Idle Monitoring Component for All Pages -->
        <IdleMonitor 
            v-if="user?.id && shouldShowIdleMonitor"
            :user-id="user.id"
            :initial-settings="initialSettings || userSettings"
            :is-idle-monitoring-enabled="isIdleMonitoringEnabled"
        />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useInertiaAuth } from '@/Composables/useInertiaAuth'
import IdleMonitor from '@/Components/IdleMonitor.vue'
import Sidebar from '@/Components/Organisms/Sidebar.vue'
import LogoutButton from '@/Components/Atoms/LogoutButton.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    initialSettings: Object,
    canControlIdleMonitoring: Boolean,
    isIdleMonitoringEnabled: {
        type: Boolean,
        default: true // Default to true if not provided
    }
})

// Sidebar state - open by default on desktop, closed on mobile
const sidebarOpen = ref(true)

// Computed property to determine if idle monitoring should be shown
const shouldShowIdleMonitor = computed(() => {
    // Only show idle monitoring for employee users, not admin users
    const isEmployee = props.user?.role_names?.includes('employee')
    const shouldShow = !!(props.user?.id && props.isIdleMonitoringEnabled && isEmployee)
    console.log('🔍 AppLayout shouldShowIdleMonitor:', {
        userId: props.user?.id,
        isIdleMonitoringEnabled: props.isIdleMonitoringEnabled,
        isEmployee: isEmployee,
        shouldShow
    })
    return shouldShow
})

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
    sidebarOpen.value = false
}

const { smartLogout } = useInertiaAuth()

const logout = async () => {
    try {
        // Use the smart logout function that automatically determines the correct logout method
        await smartLogout({
            onSuccess: () => {
                // User will be redirected automatically based on their role
                console.log('Logout successful')
            },
            onError: (errors) => {
                console.error('Logout error:', errors)
                // Fallback to role-based logout if Inertia fails
                const user = useInertiaAuth().user.value
                if (user?.role_names?.includes('employee')) {
                    window.location.href = '/employee/logout'
                } else {
                    window.location.href = '/admin/logout'
                }
            }
        })
    } catch (error) {
        console.error('Logout failed:', error)
        // Fallback to role-based logout if Inertia fails
        const user = useInertiaAuth().user.value
        if (user?.role_names?.includes('employee')) {
            window.location.href = '/employee/logout'
        } else {
            window.location.href = '/admin/logout'
        }
    }
}
</script>

