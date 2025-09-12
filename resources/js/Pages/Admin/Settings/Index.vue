<template>
    <AppLayout :user="user">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Idle Monitoring Settings
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Configure global idle timeout settings and role-based monitoring controls
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button
                        @click="resetToDefaults"
                        :disabled="processing"
                        class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        Reset to Defaults
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Global Settings -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Global Idle Settings
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">
                            These settings apply to all users as default values
                        </p>
                        
                        <!-- Idle Timeout Input with Save Button -->
                        <div class="max-w-md">
                            <label for="idle-timeout" class="block text-sm font-medium text-gray-700 mb-2">
                                Idle Timeout (seconds)
                            </label>
                            <div class="flex space-x-3">
                                <div class="flex-1">
                                    <input
                                        :id="'idle-timeout'"
                                        v-model="idleTimeoutInput"
                                        type="number"
                                        min="1"
                                        max="300"
                                        step="1"
                                        :disabled="timeoutUpdating"
                                        placeholder="Enter timeout in seconds"
                                        class="block w-full px-4 py-3 text-lg font-semibold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed disabled:border-gray-200"
                                    />
                                </div>
                                <div class="flex-shrink-0">
                                    <button
                                        @click="saveTimeout"
                                        :disabled="timeoutUpdating || !isTimeoutChanged"
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-400"
                                    >
                                        <svg v-if="timeoutUpdating" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ timeoutUpdating ? 'Saving...' : 'Save' }}
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <p class="text-sm text-gray-500">
                                    <span v-if="timeoutUpdating">Saving timeout setting...</span>
                                    <span v-else>How long to wait before showing the first inactivity alert</span>
                                </p>
                                <div class="flex items-center space-x-2" v-if="timeoutUpdating">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Saving...
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role-based Settings -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Role-based Monitoring Control
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Enable or disable idle monitoring for specific user roles
                        </p>
                        
                        <div class="space-y-4">
                            <div
                                v-for="role in roles"
                                :key="role.id"
                                class="flex items-center justify-between p-3 border border-gray-200 rounded-md"
                            >
                                <div>
                                    <label :for="`role_${role.id}`" class="text-sm font-medium text-gray-900">
                                        {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }} Role
                                    </label>
                                    <p class="text-xs text-gray-500">
                                        {{ role.name === 'admin' ? 'Administrators' : 
                                           role.name === 'employee' ? 'Employees' : 'Other users' }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="roleForm.role_settings[role.name] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        <svg v-if="roleForm.role_settings[role.name]" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <svg v-else class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ roleForm.role_settings[role.name] ? 'Enabled' : 'Disabled' }}
                                    </span>
                                    <input
                                        :id="`role_${role.id}`"
                                        v-model="roleForm.role_settings[role.name]"
                                        @change="updateRoleSetting(role)"
                                        type="checkbox"
                                        :disabled="processing"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded disabled:opacity-50"
                                    />
                                </div>
                            </div>

                            <!-- Info Note -->
                            <div class="pt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-700">
                                        Role settings are updated immediately when you toggle the checkboxes above.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useToastNotifications } from '@/Composables/useToast.js'

const props = defineProps({
    user: Object,
    globalSettings: Object,
    roleSettings: Object,
    roles: Array,
})

const processing = ref(false)
const timeoutUpdating = ref(false)

// Toast notifications
const { showSuccess, showError } = useToastNotifications()

// Idle timeout input
const idleTimeoutInput = ref(props.globalSettings.idle_timeout)

// Computed property to check if timeout has changed
const isTimeoutChanged = computed(() => {
    return parseInt(idleTimeoutInput.value) !== props.globalSettings.idle_timeout
})

// Role settings form
const roleForm = reactive({
    role_settings: { ...props.roleSettings }
})

// Save timeout function with promise
const saveTimeout = async () => {
    const newTimeout = parseInt(idleTimeoutInput.value)
    
    // Validate input
    if (isNaN(newTimeout) || newTimeout < 1 || newTimeout > 300) {
        showError('Idle timeout must be between 1 and 300 seconds.')
        idleTimeoutInput.value = props.globalSettings.idle_timeout
        return
    }
    
    // Don't update if value hasn't changed
    if (newTimeout === props.globalSettings.idle_timeout) {
        return
    }
    
    // Set loading state
    timeoutUpdating.value = true
    
    try {
        // Create a promise that resolves when the Inertia request completes
        await new Promise((resolve, reject) => {
            router.patch('/admin/settings/global/timeout', {
                idle_timeout: newTimeout
            }, {
                onSuccess: () => {
                    showSuccess(`Idle timeout updated to ${newTimeout} seconds`)
                    // Update the global settings reference
                    props.globalSettings.idle_timeout = newTimeout
                    resolve()
                },
                onError: (errors) => {
                    console.error('Failed to update idle timeout:', errors)
                    showError('Failed to update idle timeout. Please try again.')
                    // Reset to original value
                    idleTimeoutInput.value = props.globalSettings.idle_timeout
                    reject(errors)
                }
            })
        })
    } catch (error) {
        console.error('Save timeout error:', error)
    } finally {
        // Always re-enable the input
        timeoutUpdating.value = false
    }
}


const updateRoleSetting = (role) => {
    processing.value = true
    
    // Update individual role setting immediately
    router.patch('/admin/settings/roles/toggle', {
        role_id: role.id,
        enabled: roleForm.role_settings[role.name]
    }, {
        onSuccess: () => {
            // Update successful - show success toast
            const status = roleForm.role_settings[role.name] ? 'enabled' : 'disabled'
            showSuccess(`Idle monitoring ${status} for ${role.name} role`)
        },
        onError: (errors) => {
            console.error('Failed to update role setting:', errors)
            showError('Failed to update role setting. Please try again.')
            // Revert the checkbox state on error
            roleForm.role_settings[role.name] = !roleForm.role_settings[role.name]
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

const resetToDefaults = () => {
    if (confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
        processing.value = true
        
        router.post('/admin/settings/reset', {}, {
            onFinish: () => {
                processing.value = false
            }
        })
    }
}
</script>
