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
                    <span>{{ warningCount }}/3</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="h-2 rounded-full transition-all duration-300"
                        :class="warningCount === 2 ? 'bg-orange-500' : 'bg-red-500'"
                        :style="{ width: `${(warningCount / 3) * 100}%` }"
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
                    v-if="warningCount < 3"
                    @click="acknowledgeWarning"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    I'm Still Here
                </button>
                <button
                    v-if="warningCount >= 3"
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
const warningTitle = computed(() => {
    switch (warningCount.value) {
        case 1:
            return '⚠️ First Alert - You appear to be idle'
        case 2:
            return '⚠️ Second Warning - Idle Activity Detected'
        case 3:
            return '🚨 Final Warning - Auto Logout Imminent'
        default:
            return 'Idle Warning'
    }
})

const warningMessage = computed(() => {
    switch (warningCount.value) {
        case 1:
            return 'We noticed you haven\'t been active for a while. This is your first warning.'
        case 2:
            return 'This is your second warning. Continued inactivity will result in automatic logout and a penalty.'
        case 3:
            return 'This is your final warning. You will be automatically logged out and a penalty will be applied.'
        default:
            return 'You appear to be idle.'
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
    
    // Don't reset if modal is showing
    if (showWarningModal.value) {
        return
    }
    
    // Reset warning count when user is active
    if (warningCount.value > 0) {
        warningCount.value = 0
        console.log('🔄 User became active - reset warning count to 0')
    }
    
    // Start new idle timer
    const timeout = (props.initialSettings?.idle_timeout || 5) * 1000
    idleTimer = setTimeout(() => {
        handleIdleTimeout()
    }, timeout)
}

const handleIdleTimeout = async () => {
    // Increment warning count
    warningCount.value++
    
    console.log(`⚠️ Idle timeout detected - Warning ${warningCount.value}/3`)
    
    // Show warning first
    showWarning()
    
    // Call API
    await handleIdleWarningAPI()
}

const showWarning = () => {
    showWarningModal.value = true
    startCountdown()
}

const startCountdown = () => {
    countdown.value = 10
    countdownTimer = setInterval(() => {
        countdown.value--
        
        if (countdown.value <= 0) {
            clearInterval(countdownTimer)
            handleWarningTimeout()
        }
    }, 1000)
}

const handleIdleWarningAPI = async () => {
    try {
        console.log('🔍 Making API call with warning count:', warningCount.value)
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        const response = await fetch('/idle-monitoring/handle-warning', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                warning_count: warningCount.value
            })
        })
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        const data = await response.json()
        console.log('✅ API call successful:', data)
        
        // Only logout if this is the third warning and logout is required
        if (warningCount.value >= 3 && data.logout_required) {
            console.log('Third warning reached - logout required, redirecting...')
            window.location.href = '/login?message=inactivity_logout'
        }
        
    } catch (error) {
        console.error('Error handling warning API:', error)
        if (warningCount.value >= 3) {
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
    console.log('🔍 IdleMonitorSimple mounted with props:', {
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
