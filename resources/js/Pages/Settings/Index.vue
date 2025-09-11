<template>
    <AppLayout :user="user">
        <div class="max-w-4xl mx-auto">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Role-based Monitoring Control
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Enable or disable idle monitoring for specific user roles
                    </p>
                </div>
            </div>

            <!-- Global Idle Settings -->
            <div class="mt-8 bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Global Idle Monitoring Settings
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">
                        <span v-if="canControlIdleMonitoring">
                            Configure global idle timeout settings for all users
                        </span>
                        <span v-else>
                            View current global idle monitoring settings (read-only)
                        </span>
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Idle Timeout -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Idle Timeout
                            </label>
                            <div class="flex items-center space-x-3">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ globalSettings.idle_timeout }} seconds
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ Math.floor(globalSettings.idle_timeout / 60) }} minutes {{ globalSettings.idle_timeout % 60 }} seconds
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Timeout
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Max Warnings -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Maximum Warnings
                            </label>
                            <div class="flex items-center space-x-3">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ globalSettings.max_idle_warnings }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ globalSettings.max_idle_warnings === 1 ? 'warning' : 'warnings' }} before logout
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Warnings
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Settings Table -->
            <div class="mt-8 bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Role-based Idle Monitoring Settings
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">
                        <span v-if="canControlIdleMonitoring">
                            Enable or disable idle monitoring for specific user roles
                        </span>
                        <span v-else>
                            View current idle monitoring settings for user roles (read-only)
                        </span>
                    </p>
                    
                    
                    <!-- Simple Role Settings List -->
                    <div class="space-y-4">
                        <div
                            v-for="role in reactiveRoleSettings"
                            :key="role.role_id"
                            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                        >
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">
                                                {{ role.role_display_name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">
                                            {{ role.role_display_name }} Role
                                        </h4>
                                        <p class="text-xs text-gray-500">
                                            {{ role.role_name === 'admin' ? 'Administrators' : 
                                               role.role_name === 'employee' ? 'Employees' : 'Other users' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <!-- Status Badge -->
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="role.idle_monitoring_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    <svg v-if="role.idle_monitoring_enabled" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <svg v-else class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ role.idle_monitoring_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                                
                                <!-- Checkbox -->
                                <input
                                    :id="`role_${role.role_id}`"
                                    :checked="role.idle_monitoring_enabled"
                                    @change="toggleRoleMonitoring(role)"
                                    type="checkbox"
                                    :disabled="!canControlIdleMonitoring || toggleLoading"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded disabled:opacity-50"
                                    :class="canControlIdleMonitoring ? 'cursor-pointer' : 'bg-gray-50 cursor-not-allowed'"
                                />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Note about role settings -->
                    <div class="mt-6 p-4 rounded-lg" :class="canControlIdleMonitoring ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200'">
                        <div class="flex items-center">
                            <svg v-if="canControlIdleMonitoring" class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <svg v-else class="h-5 w-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm" :class="canControlIdleMonitoring ? 'text-blue-700' : 'text-gray-600'">
                                <span v-if="canControlIdleMonitoring">
                                    Role settings are updated immediately when you toggle the checkboxes above.
                                </span>
                                <span v-else>
                                    You can view the current role settings above. Only administrators can modify these settings.
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Penalties -->
            <div v-if="userPenalties.length > 0" class="mt-8 bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Your Penalties
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="penalty in userPenalties"
                            :key="penalty.id"
                            class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-md"
                        >
                            <div>
                                <p class="text-sm font-medium text-red-800">{{ penalty.reason }}</p>
                                <p class="text-sm text-red-600">Count: {{ penalty.count }}</p>
                            </div>
                            <div class="text-sm text-red-600">
                                {{ formatDate(penalty.date) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useToastNotifications } from '@/Composables/useToast.js'

const props = defineProps({
    user: Object,
    userPenalties: Array,
    canControlIdleMonitoring: Boolean,
    roleSettings: Array,
    globalSettings: Object,
})

const processing = ref(false)
const toggleLoading = ref(false)

// Make roleSettings reactive for immediate updates
const reactiveRoleSettings = ref([...props.roleSettings])

// Toast notifications
const { showSuccess, showError } = useToastNotifications()


// Role settings handlers
const toggleRoleMonitoring = (role) => {
    if (!props.canControlIdleMonitoring) {
        alert('You do not have permission to modify role settings.')
        return
    }
    
    toggleLoading.value = true
    
    // Update the role monitoring status immediately
    router.patch('/settings/roles/toggle', {
        role_id: role.role_id,
        enabled: !role.idle_monitoring_enabled
    }, {
        onSuccess: () => {
            // Update the reactive data immediately without page reload
            const roleIndex = reactiveRoleSettings.value.findIndex(r => r.role_id === role.role_id)
            if (roleIndex !== -1) {
                // Update the role setting in the reactive array
                reactiveRoleSettings.value[roleIndex].idle_monitoring_enabled = !role.idle_monitoring_enabled
                // Show success toast
                const status = reactiveRoleSettings.value[roleIndex].idle_monitoring_enabled ? 'enabled' : 'disabled'
                showSuccess(`Idle monitoring ${status} for ${role.role_display_name} role`)
            }
        },
        onError: (errors) => {
            console.error('Failed to toggle role monitoring:', errors)
            showError('Failed to update role monitoring. Please try again.')
        },
        onFinish: () => {
            toggleLoading.value = false
        }
    })
}


const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}
</script>

