<template>
    <!-- Idle Warning Modal -->
    <div v-if="showWarningModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ warningTitle }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ warningMessage }}
                                </p>
                                <div v-if="warningCount > 1" class="mt-2">
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" :style="{ width: `${(warningCount / maxWarnings) * 100}%` }"></div>
                                        </div>
                                        <span class="ml-2 text-xs text-gray-500">{{ warningCount }}/{{ maxWarnings }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        type="button"
                        @click="resetIdleTimer"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        I'm Still Here
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

// Props
const props = defineProps({
    userId: {
        type: Number,
        required: true
    },
    initialSettings: {
        type: Object,
        default: () => ({
            idle_timeout: 5,
            idle_monitoring_enabled: true,
            max_idle_warnings: 2
        })
    }
})

// Reactive data
const showWarningModal = ref(false)
const warningCount = ref(0)
const warningTitle = ref('')
const warningMessage = ref('')
const maxWarnings = ref(2)
const idleTimeout = ref(5000) // 5 seconds in milliseconds

// Timer variables
let idleTimer = null
let warningTimer = null
let currentSessionId = null

// Initialize the idle monitor
onMounted(() => {
    if (props.initialSettings.idle_monitoring_enabled) {
        idleTimeout.value = props.initialSettings.idle_timeout * 1000
        maxWarnings.value = props.initialSettings.max_idle_warnings
        startIdleMonitoring()
    }
})

onUnmounted(() => {
    stopIdleMonitoring()
})

// Start idle monitoring
const startIdleMonitoring = () => {
    // Add event listeners for user activity
    document.addEventListener('mousemove', resetIdleTimer)
    document.addEventListener('keydown', resetIdleTimer)
    document.addEventListener('scroll', resetIdleTimer)
    document.addEventListener('click', resetIdleTimer)
    document.addEventListener('touchstart', resetIdleTimer)
    
    // Start the initial timer
    resetIdleTimer()
}

// Stop idle monitoring
const stopIdleMonitoring = () => {
    // Remove event listeners
    document.removeEventListener('mousemove', resetIdleTimer)
    document.removeEventListener('keydown', resetIdleTimer)
    document.removeEventListener('scroll', resetIdleTimer)
    document.removeEventListener('click', resetIdleTimer)
    document.removeEventListener('touchstart', resetIdleTimer)
    
    // Clear timers
    if (idleTimer) {
        clearTimeout(idleTimer)
        idleTimer = null
    }
    if (warningTimer) {
        clearTimeout(warningTimer)
        warningTimer = null
    }
    
    // End current session if exists
    if (currentSessionId) {
        endIdleSession()
    }
}

// Reset the idle timer
const resetIdleTimer = () => {
    // Clear existing timers
    if (idleTimer) {
        clearTimeout(idleTimer)
    }
    if (warningTimer) {
        clearTimeout(warningTimer)
    }
    
    // Hide warning modal if showing
    if (showWarningModal.value) {
        showWarningModal.value = false
        warningCount.value = 0
    }
    
    // End current idle session if exists
    if (currentSessionId) {
        endIdleSession()
    }
    
    // Start new idle timer
    idleTimer = setTimeout(() => {
        handleIdleTimeout()
    }, idleTimeout.value)
}

// Handle idle timeout
const handleIdleTimeout = async () => {
    try {
        // Start idle session
        const response = await axios.post('/api/idle-monitoring/start-session')
        currentSessionId = response.data.session_id
        
        // Show warning
        warningCount.value++
        showWarning()
        
    } catch (error) {
        console.error('Error starting idle session:', error)
    }
}

// Show warning modal
const showWarning = () => {
    if (warningCount.value === 1) {
        warningTitle.value = 'You appear to be idle'
        warningMessage.value = 'We noticed you haven\'t been active for a while. Please confirm you\'re still here to continue your session.'
    } else if (warningCount.value === 2) {
        warningTitle.value = 'Second Warning - Idle Activity Detected'
        warningMessage.value = 'This is your second warning. Continued inactivity will result in automatic logout and a penalty.'
    } else {
        warningTitle.value = 'Final Warning - Auto Logout Imminent'
        warningMessage.value = 'This is your final warning. You will be automatically logged out if you don\'t respond.'
    }
    
    showWarningModal.value = true
    
    // Set timer for automatic action if user doesn't respond
    warningTimer = setTimeout(() => {
        handleWarningTimeout()
    }, 10000) // 10 seconds to respond
}

// Handle warning timeout (user didn't respond)
const handleWarningTimeout = async () => {
    try {
        const response = await axios.post('/api/idle-monitoring/handle-warning', {
            warning_count: warningCount.value,
            session_id: currentSessionId
        })
        
        if (response.data.logout_required) {
            // User will be logged out
            router.visit('/login', {
                method: 'get',
                onSuccess: () => {
                    // Show logout message
                    alert('You have been logged out due to inactivity. A penalty has been applied.')
                }
            })
        } else {
            // Continue with next warning
            warningCount.value++
            showWarning()
        }
        
    } catch (error) {
        console.error('Error handling warning:', error)
        if (error.response?.status === 401) {
            // User was logged out
            router.visit('/login')
        }
    }
}

// End idle session
const endIdleSession = async () => {
    if (!currentSessionId) return
    
    try {
        await axios.post('/api/idle-monitoring/end-session', {
            session_id: currentSessionId
        })
        currentSessionId = null
    } catch (error) {
        console.error('Error ending idle session:', error)
    }
}

// Get current settings
const getSettings = async () => {
    try {
        const response = await axios.get('/api/idle-monitoring/settings')
        const settings = response.data
        
        idleTimeout.value = settings.timeout_milliseconds
        maxWarnings.value = settings.max_idle_warnings
        
        if (!settings.idle_monitoring_enabled) {
            stopIdleMonitoring()
        }
    } catch (error) {
        console.error('Error getting settings:', error)
    }
}

// Expose methods for parent component
defineExpose({
    startIdleMonitoring,
    stopIdleMonitoring,
    getSettings
})
</script>