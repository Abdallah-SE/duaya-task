<template>
    <!-- Idle Warning Modal -->
    <div v-if="showWarningModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <!-- Warning Icon and Title -->
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
import { ref, onMounted, onUnmounted, computed } from 'vue'
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
const warningTitle = ref('')
const warningMessage = ref('')
const maxWarnings = ref(3)
const idleTimeout = ref(5000) // 5 seconds in milliseconds
const countdown = ref(10) // Countdown for automatic warning progression

// Timer variables
let idleTimer = null
let warningTimer = null
let countdownTimer = null
let csrfRefreshTimer = null
let currentSessionId = null

// Computed properties
const isMonitoringEnabled = computed(() => {
    return props.isIdleMonitoringEnabled && props.initialSettings.idle_monitoring_enabled
})

// Initialize the idle monitor
onMounted(() => {
    if (isMonitoringEnabled.value) {
        idleTimeout.value = props.initialSettings.idle_timeout * 1000
        maxWarnings.value = props.initialSettings.max_idle_warnings
        startIdleMonitoring()
        startCsrfRefresh()
        console.log('🚀 Global Idle Monitor initialized for user:', props.userId)
    }
})

onUnmounted(() => {
    stopIdleMonitoring()
    stopCsrfRefresh()
})

// Start idle monitoring
const startIdleMonitoring = () => {
    console.log('🚀 Starting global idle monitoring...')
    console.log('Current timeout:', idleTimeout.value, 'ms')
    
    // Add event listeners for user activity
    document.addEventListener('mousemove', resetIdleTimer)
    document.addEventListener('keydown', resetIdleTimer)
    document.addEventListener('scroll', resetIdleTimer)
    document.addEventListener('click', resetIdleTimer)
    document.addEventListener('touchstart', resetIdleTimer)
    document.addEventListener('keyup', resetIdleTimer)
    document.addEventListener('mousedown', resetIdleTimer)
    
    console.log('✅ Event listeners added, starting initial timer...')
    
    // Start the initial timer
    resetIdleTimer()
    console.log('✅ Initial timer started')
}

// Stop idle monitoring
const stopIdleMonitoring = () => {
    // Remove event listeners
    document.removeEventListener('mousemove', resetIdleTimer)
    document.removeEventListener('keydown', resetIdleTimer)
    document.removeEventListener('scroll', resetIdleTimer)
    document.removeEventListener('click', resetIdleTimer)
    document.removeEventListener('touchstart', resetIdleTimer)
    document.removeEventListener('keyup', resetIdleTimer)
    document.removeEventListener('mousedown', resetIdleTimer)
    
    // Clear timers
    if (idleTimer) {
        clearTimeout(idleTimer)
        idleTimer = null
    }
    if (warningTimer) {
        clearTimeout(warningTimer)
        warningTimer = null
    }
    if (countdownTimer) {
        clearInterval(countdownTimer)
        countdownTimer = null
    }
    
    // End current session if exists
    if (currentSessionId) {
        endIdleSession()
    }
}

// Start CSRF token refresh
const startCsrfRefresh = () => {
    console.log('Starting CSRF token refresh...')
    // Refresh CSRF token every 5 minutes
    csrfRefreshTimer = setInterval(async () => {
        await ensureFreshCsrfToken()
    }, 5 * 60 * 1000) // 5 minutes
}

// Stop CSRF token refresh
const stopCsrfRefresh = () => {
    if (csrfRefreshTimer) {
        clearInterval(csrfRefreshTimer)
        csrfRefreshTimer = null
        console.log('Stopped CSRF token refresh')
    }
}

// Start countdown for automatic warning progression
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

// Handle idle warning API call
const handleIdleWarningAPI = async () => {
    try {
        console.log('🔍 Making API call with warning count:', warningCount.value)
        
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        console.log('🔍 CSRF Token:', csrfToken ? 'Found' : 'Not found')
        
        // Use web route for better CSRF handling
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
        
        // Store new session ID for this warning (each warning creates a new session)
        if (data.session_id) {
            currentSessionId = data.session_id
            console.log('📝 New idle session created for warning', warningCount.value, 'with ID:', currentSessionId)
        }
        
        // Only logout if this is the third warning and logout is required
        if (warningCount.value >= 3 && data.logout_required) {
            console.log('Third warning reached - logout required, redirecting...')
            console.log('Penalty created:', data.penalty_id)
            window.location.href = '/login?message=inactivity_logout'
        }
        
    } catch (error) {
        console.error('Error handling warning API:', error)
        if (error.response?.status === 401) {
            // User was logged out by server - force redirect
            console.log('User logged out by server (401)')
            window.location.href = '/login?message=inactivity_logout'
        } else {
            // Other error - log but don't redirect unless it's the third warning
            console.error('Unexpected error during idle monitoring:', error)
            if (warningCount.value >= 3) {
                window.location.href = '/login?message=inactivity_logout'
            }
        }
    }
}

// Reset the idle timer
const resetIdleTimer = () => {
    // Clear existing timers
    if (idleTimer) {
        clearTimeout(idleTimer)
        idleTimer = null
    }
    if (warningTimer) {
        clearTimeout(warningTimer)
        warningTimer = null
    }
    if (countdownTimer) {
        clearInterval(countdownTimer)
        countdownTimer = null
    }
    
    // Only hide warning modal if user clicked "I'm Still Here" button
    // Don't hide it just because user moved mouse - they might be trying to click the button
    if (showWarningModal.value) {
        return // Don't reset the timer if modal is showing
    }
    
    // End current idle session if exists (user became active)
    if (currentSessionId) {
        endIdleSession()
    }
    
    // Reset warning count when user is active
    if (warningCount.value > 0) {
        warningCount.value = 0
        console.log('🔄 User became active - reset warning count to 0')
    }
    
    // Start new idle timer
    idleTimer = setTimeout(() => {
        handleIdleTimeout()
    }, idleTimeout.value)
}

// Handle idle timeout
const handleIdleTimeout = async () => {
    // Increment warning count
    warningCount.value++
    
    console.log(`⚠️ Idle timeout detected - Warning ${warningCount.value}/${maxWarnings.value}`)
    
    // Show warning first
    showWarning()
    
    // Call API on ALL warnings to create idle sessions (but don't wait for response)
    handleIdleWarningAPI()
}

// Show warning modal
const showWarning = () => {
    if (warningCount.value === 1) {
        warningTitle.value = '⚠️ First Alert - You appear to be idle'
        warningMessage.value = 'We noticed you haven\'t been active for a while. This is your first warning.'
    } else if (warningCount.value === 2) {
        warningTitle.value = '⚠️ Second Warning - Idle Activity Detected'
        warningMessage.value = 'This is your second warning. Continued inactivity will result in automatic logout and a penalty.'
    } else if (warningCount.value >= 3) {
        warningTitle.value = '🚨 Final Warning - Auto Logout Imminent'
        warningMessage.value = 'This is your final warning. You will be automatically logged out and a penalty will be applied.'
    }
    
    showWarningModal.value = true
    
    // Start countdown
    startCountdown()
}

// Handle warning timeout (automatic progression)
const handleWarningTimeout = async () => {
    // Hide the current modal
    showWarningModal.value = false
    
    // If we haven't reached the third warning yet, continue with next warning
    if (warningCount.value < 3) {
        // Start a new idle timer for the next warning (shorter timeout for subsequent warnings)
        const nextTimeout = Math.max(2000, idleTimeout.value / 2) // At least 2 seconds, or half the original timeout
        idleTimer = setTimeout(() => {
            handleIdleTimeout()
        }, nextTimeout)
        return
    }
    
    // If we've reached the third warning, wait a moment for API response then logout
    setTimeout(() => {
        window.location.href = '/login?message=inactivity_logout'
    }, 2000) // Wait 2 seconds for API response
}

// Acknowledge warning (user clicked "I'm Still Here")
const acknowledgeWarning = () => {
    showWarningModal.value = false
    
    // End current session
    if (currentSessionId) {
        endIdleSession()
    }
    
    // Reset warning count
    warningCount.value = 0
    
    // Start new idle timer
    resetIdleTimer()
    
    console.log('✅ User acknowledged warning - resetting idle monitoring')
}

// Force logout (user clicked "Logout Now" on final warning)
const forceLogout = () => {
    window.location.href = '/login?message=manual_logout'
}

// End idle session
const endIdleSession = async () => {
    if (!currentSessionId) return
    
    try {
        const csrfToken = await ensureFreshCsrfToken()
        if (!csrfToken) {
            console.warn('Could not get CSRF token for ending session')
            currentSessionId = null
            return
        }
        
        await axios.post('/api/idle-monitoring/end-session', {
            session_id: currentSessionId
        }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        currentSessionId = null
    } catch (error) {
        console.error('Error ending idle session:', error)
        // Don't block the flow if ending session fails
        currentSessionId = null
    }
}

// Helper function to get CSRF token from cookie
const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (token) {
        return token
    }
    
    // Fallback: try to get from cookie
    const cookies = document.cookie.split(';')
    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=')
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value)
        }
    }
    
    return null
}

// Alternative method to get CSRF token by making a request
const getCsrfTokenFromRequest = async () => {
    try {
        const response = await axios.get('/dashboard', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        
        // Parse the response to extract CSRF token from meta tag
        const parser = new DOMParser()
        const doc = parser.parseFromString(response.data, 'text/html')
        const metaTag = doc.querySelector('meta[name="csrf-token"]')
        if (metaTag) {
            return metaTag.getAttribute('content')
        }
    } catch (error) {
        console.error('Error getting CSRF token from request:', error)
    }
    return null
}

// Helper function to ensure fresh CSRF token
const ensureFreshCsrfToken = async () => {
    try {
        // First try to get from current page
        let freshToken = getCsrfToken()
        if (freshToken) {
            return freshToken
        }
        
        // If not available, refresh the cookie
        await axios.get('/sanctum/csrf-cookie')
        
        // Wait a bit for the cookie to be set
        await new Promise(resolve => setTimeout(resolve, 200))
        
        // Try to get the fresh token
        freshToken = getCsrfToken()
        if (freshToken) {
            return freshToken
        }
        
        // Last resort: make a request to get the token
        freshToken = await getCsrfTokenFromRequest()
        if (freshToken) {
            return freshToken
        }
        
        console.warn('Could not get CSRF token by any method')
    } catch (error) {
        console.error('Failed to get fresh CSRF token:', error)
    }
    return null
}

// Expose methods for parent component
defineExpose({
    startIdleMonitoring,
    stopIdleMonitoring
})
</script>
