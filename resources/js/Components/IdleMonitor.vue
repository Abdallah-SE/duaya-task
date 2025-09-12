<template>
    <!-- Idle Warning Modal -->
    <div v-if="showWarningModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <!-- Warning Header -->
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg v-if="warningCount === 1" class="h-8 w-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else-if="warningCount === 2" class="h-8 w-8 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else class="h-8 w-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ warningTitle }}
                    </h3>
                </div>
            </div>

            <!-- Warning Message -->
            <div class="mb-4">
                <p class="text-sm text-gray-600">{{ warningMessage }}</p>
            </div>

            <!-- Progress Bar for Multiple Warnings -->
            <div v-if="warningCount > 1" class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Warning Progress</span>
                    <span>{{ warningCount }}/{{ maxWarnings }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="h-2 rounded-full transition-all duration-300"
                        :class="warningCount === 2 ? 'bg-orange-500' : 'bg-red-500'"
                        :style="{ width: `${(warningCount / maxWarnings) * 100}%` }"
                    ></div>
                </div>
            </div>
            
            <!-- Countdown Timer -->
            <div class="mb-6">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">
                        This warning will automatically proceed in {{ countdown }} seconds...
                    </p>
                    <div class="w-full bg-gray-200 rounded-full h-1">
                        <div 
                            class="h-1 rounded-full bg-blue-500 transition-all duration-1000"
                            :style="{ width: `${(countdown / 10) * 100}%` }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <button
                    v-if="warningCount < maxWarnings"
                    @click="acknowledgeWarning"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    I'm Still Here
                </button>
                <button
                    v-if="warningCount >= maxWarnings"
                    @click="forceLogout"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    Logout Now
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
            max_idle_warnings: 3
        })
    },
    isIdleMonitoringEnabled: {
        type: Boolean,
        default: true
    }
})

// Reactive data
const showWarningModal = ref(false)
const warningCount = ref(0)
const countdown = ref(10)

// Timer variables
let idleTimer = null
let countdownTimer = null

// Computed properties
const maxWarnings = computed(() => {
    return 3 // Fixed to 3 as per task requirements
})

// Computed properties
const warningTitle = computed(() => {
    switch (warningCount.value) {
        case 1:
            return '⚠️ Alert - Inactivity Detected'
        case 2:
            return '⚠️ Warning - Inactivity Detected'
        case 3:
            return '🚨 Auto Logout - Inactivity Detected'
        default:
            return 'Inactivity Alert'
    }
})

const warningMessage = computed(() => {
    switch (warningCount.value) {
        case 1:
            return 'You have been inactive for a while. This is your first alert.'
        case 2:
            return 'You have been inactive again. This is your second warning. Continued inactivity will result in automatic logout and penalty.'
        case 3:
            return 'You have been inactive for the third time. You will be automatically logged out and a penalty will be applied.'
        default:
            return 'Inactivity detected.'
    }
})

// Methods
const startIdleMonitoring = () => {
    if (!props.isIdleMonitoringEnabled) {
        console.log('Idle monitoring disabled')
        return
    }

    console.log('🚀 Starting idle monitoring...')
    console.log('Idle timeout:', props.initialSettings?.idle_timeout, 'seconds')
    
    // Add event listeners for user activity
    const events = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart', 'keyup', 'mousedown']
    events.forEach(event => {
        document.addEventListener(event, resetIdleTimer)
    })
    
    // Start the initial timer
    resetIdleTimer()
    console.log('✅ Idle monitoring started')
}

const stopIdleMonitoring = () => {
    console.log('🛑 Stopping idle monitoring...')
    
    // Remove event listeners
    const events = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart', 'keyup', 'mousedown']
    events.forEach(event => {
        document.removeEventListener(event, resetIdleTimer)
    })
    
    // Clear timers
    if (idleTimer) {
        clearTimeout(idleTimer)
        idleTimer = null
    }
    if (countdownTimer) {
        clearInterval(countdownTimer)
        countdownTimer = null
    }
    
    console.log('✅ Idle monitoring stopped')
}

const resetIdleTimer = () => {
    // Clear existing timers
    if (idleTimer) {
        clearTimeout(idleTimer)
        idleTimer = null
    }
    if (countdownTimer) {
        clearInterval(countdownTimer)
        countdownTimer = null
    }
    
    // If modal is showing, restart the countdown timer
    // User needs to explicitly click "I'm Still Here" to dismiss the modal
    if (showWarningModal.value) {
        console.log('🔄 User moved mouse while modal is showing - restarting countdown')
        startCountdown()
        return
    }
    
    // Only reset warning count if no modal is showing and user is truly active
    // Don't reset during warning sequence
    if (warningCount.value > 0 && !showWarningModal.value) {
        warningCount.value = 0
        console.log('🔄 User became active - reset warning count to 0')
    }
    
    // Only start new idle timer if no warning modal is showing
    if (!showWarningModal.value) {
        // Start new idle timer - use timeout from idle_settings table
        const timeout = (props.initialSettings?.idle_timeout || 5) * 1000
        console.log('Setting idle timeout to:', timeout, 'ms (', props.initialSettings?.idle_timeout, 'seconds from idle_settings)')
        idleTimer = setTimeout(() => {
            handleIdleTimeout()
        }, timeout)
    }
}

const handleIdleTimeout = async () => {
    // Prevent multiple simultaneous calls
    if (showWarningModal.value) {
        console.log('⚠️ Warning modal already showing, ignoring duplicate timeout')
        return
    }
    
    // Increment warning count
    warningCount.value++
    
    console.log(`⚠️ Idle timeout detected - Warning ${warningCount.value}/${maxWarnings.value}`)
    console.log('Current warning count:', warningCount.value, 'Max warnings:', maxWarnings.value)
    console.log('Is this the third warning?', warningCount.value === 3)
    
    // Show warning first
    showWarning()
    
    // Call API
    await handleIdleWarningAPI()
}

const handleWarningTimeout = () => {
    // Hide the current modal
    showWarningModal.value = false
    
    console.log('Warning timeout - current count:', warningCount.value, 'max:', maxWarnings.value)
    
    // If we haven't reached the max warnings yet, show next warning immediately
    if (warningCount.value < maxWarnings.value) {
        console.log('Progressing to next warning:', warningCount.value + 1)
        // Increment warning count and show next warning
        warningCount.value++
        console.log('New warning count:', warningCount.value, 'Is third?', warningCount.value === 3)
        showWarning()
        handleIdleWarningAPI()
        return
    }
    
    // If we've reached the max warnings, the backend will handle logout
    // Don't do anything here - let the API response handle it
    console.log('Max warnings reached - backend will handle logout')
}

const showWarning = () => {
    showWarningModal.value = true
    startCountdown()
}

const startCountdown = () => {
    // Clear any existing countdown timer
    if (countdownTimer) {
        clearInterval(countdownTimer)
        countdownTimer = null
    }
    
    countdown.value = 10
    countdownTimer = setInterval(() => {
        countdown.value--
        
        if (countdown.value <= 0) {
            clearInterval(countdownTimer)
            countdownTimer = null
            handleWarningTimeout()
        }
    }, 1000)
}

const handleIdleWarningAPI = async () => {
    try {
        console.log('🔍 Making API call with warning count:', warningCount.value)
        console.log('🔍 Max warnings:', maxWarnings.value)
        console.log('🔍 Is third warning?', warningCount.value >= 3)
        
        // Use axios with proper CSRF handling
        const response = await axios.post('/api/idle-monitoring/handle-warning', {
            warning_count: warningCount.value
        }, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        
        console.log('✅ API call successful:', response.data)
        
        const data = response.data
        
        // Only logout if this is the max warning and logout is required
        console.log('Checking logout conditions:', {
            warningCount: warningCount.value,
            maxWarnings: maxWarnings.value,
            logoutRequired: data?.logout_required,
            shouldLogout: warningCount.value >= maxWarnings.value && data?.logout_required
        })
        
        if (warningCount.value >= maxWarnings.value && data?.logout_required) {
            console.log('Max warning reached - logout required, redirecting...')
            window.location.href = '/login?message=inactivity_logout'
        }
        
    } catch (error) {
        console.error('❌ API call failed:', error)
        
        // Handle 401 error (user logged out)
        if (error.response?.status === 401) {
            console.log('User logged out by server (401) - redirecting to login')
            window.location.replace('/login?message=inactivity_logout')
            return
        }
        
        // Handle 419 error (CSRF token mismatch)
        if (error.response?.status === 419) {
            console.error('❌ CSRF token mismatch (419) - trying to refresh page')
            window.location.reload()
            return
        }
        
        // Only redirect on max warning, not on network errors
        if (warningCount.value >= maxWarnings.value) {
            console.log('Max warning reached - redirecting due to error')
            window.location.href = '/login?message=inactivity_logout'
        }
    }
}

const acknowledgeWarning = () => {
    showWarningModal.value = false
    
    // Reset warning count
        warningCount.value = 0
    
    // Start new idle timer
    resetIdleTimer()
    
    console.log('✅ User acknowledged warning - resetting idle monitoring')
}

const forceLogout = () => {
    window.location.href = '/login?message=manual_logout'
}

// Lifecycle
onMounted(() => {
    console.log('🔍 IdleMonitor mounted with props:', {
        userId: props.userId,
        isIdleMonitoringEnabled: props.isIdleMonitoringEnabled,
        initialSettings: props.initialSettings,
        idleTimeout: props.initialSettings?.idle_timeout,
        maxWarnings: props.initialSettings?.max_idle_warnings
    })
    
    if (props.isIdleMonitoringEnabled) {
        console.log('✅ Starting idle monitoring...')
        startIdleMonitoring()
    } else {
        console.log('❌ Idle monitoring disabled:', {
            isIdleMonitoringEnabled: props.isIdleMonitoringEnabled
        })
    }
})

onUnmounted(() => {
            stopIdleMonitoring()
})

// Expose methods for parent component
defineExpose({
    startIdleMonitoring,
    stopIdleMonitoring
})
</script>
