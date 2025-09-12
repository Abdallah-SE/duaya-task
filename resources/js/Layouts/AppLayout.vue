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
                        <button @click="logout" 
                                class="text-sm text-gray-500 hover:text-gray-700">
                            Logout
                        </button>
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
            :can-control-idle-monitoring="canControlIdleMonitoring"
            :is-idle-monitoring-enabled="isIdleMonitoringEnabled"
        />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import IdleMonitor from '@/Components/IdleMonitor.vue'
import Sidebar from '@/Components/Organisms/Sidebar.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    initialSettings: Object,
    canControlIdleMonitoring: Boolean,
    isIdleMonitoringEnabled: Boolean
})

// Sidebar state
const sidebarOpen = ref(false)

// Computed property to determine if idle monitoring should be shown
const shouldShowIdleMonitor = computed(() => {
    // Show idle monitoring for all authenticated users
    // The actual monitoring will be controlled by role settings
    return props.user?.id && props.isIdleMonitoringEnabled
})

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
    sidebarOpen.value = false
}

const logout = () => {
    router.post('/logout')
}
</script>

