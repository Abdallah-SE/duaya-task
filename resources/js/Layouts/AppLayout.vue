<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Duaya Task - Activity Monitor</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">{{ user.name }}</span>
                        <span v-if="user.employee" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ user.employee.department }} - {{ user.employee.job_title }}
                        </span>
                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ user.role }}
                        </span>
                        <button @click="logout" 
                                class="text-sm text-gray-500 hover:text-gray-700">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <slot />
        </main>

        <!-- Idle Monitoring Component -->
        <IdleMonitor 
            :user-id="user.id"
            :initial-settings="userSettings"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import IdleMonitor from '@/Components/IdleMonitor.vue'

const props = defineProps({
    user: Object,
    userSettings: Object
})

// Removed roleBadgeClass since we're not using roles anymore

const logout = () => {
    router.post('/logout')
}

// Remove the handlePenaltyApplied function as it's now handled in the IdleMonitor component
</script>

