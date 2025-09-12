<template>
    <!-- Idle Warning Modal -->
    <div v-if="showWarningModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <h2 style="color: #e67e22; margin-bottom: 15px; font-size: 24px;">⚠️ {{ warningTitle }}</h2>
            <p style="color: #333; margin-bottom: 20px; font-size: 16px;">{{ warningMessage }}</p>
            
            <div v-if="warningCount > 1" style="margin-bottom: 20px;">
                <div style="background: #f0f0f0; height: 10px; border-radius: 5px; overflow: hidden;">
                    <div style="background: #e67e22; height: 100%; transition: width 0.3s;" :style="{ width: `${(warningCount / maxWarnings) * 100}%` }"></div>
                </div>
                <p style="text-align: center; margin-top: 5px; color: #666; font-size: 14px;">{{ warningCount }}/{{ maxWarnings }} Warnings</p>
            </div>
            
            <div style="text-align: center; color: #666; font-size: 14px;">
                <p>This warning will automatically proceed in {{ countdown }} seconds...</p>
                <div style="margin-top: 10px;">
                    <div style="background: #f0f0f0; height: 4px; border-radius: 2px; overflow: hidden;">
                        <div style="background: #e67e22; height: 100%; transition: width 1s linear;" :style="{ width: `${(countdown / 10) * 100}%` }"></div>
                    </div>
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
    },
    canControlIdleMonitoring: {
        type: Boolean,
        default: false
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
const maxWarnings = ref(2)
const idleTimeout = ref(5000) // 5 seconds in milliseconds
const countdown = ref(10) // Countdown for automatic warning progression

// Timer variables
let idleTimer = null
let warningTimer = null
let csrfRefreshTimer = null
let currentSessionId = null

// Initialize the idle monitor
onMounted(() => {
    // Check if monitoring is enabled (role setting only)
    const isMonitoringEnabled = props.isIdleMonitoringEnabled
    
    if (isMonitoringEnabled) {
        idleTimeout.value = props.initialSettings.idle_timeout * 1000
        maxWarnings.value = props.initialSettings.max_idle_warnings
        startIdleMonitoring()
        startCsrfRefresh()
    }
})

onUnmounted(() => {
    stopIdleMonitoring()
    stopCsrfRefresh()
})

// Start idle monitoring
const startIdleMonitoring = () => {
    console.log('🚀 Starting idle monitoring...')
    console.log('Current timeout:', idleTimeout.value, 'ms')
    
    // Add event listeners for user activity
    document.addEventListener('mousemove', resetIdleTimer)
    document.addEventListener('keydown', resetIdleTimer)
    document.addEventListener('scroll', resetIdleTimer)
    document.addEventListener('click', resetIdleTimer)
    document.addEventListener('touchstart', resetIdleTimer)
    
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
    const countdownInterval = setInterval(() => {
        countdown.value--
        
        if (countdown.value <= 0) {
            clearInterval(countdownInterval)
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
    
    // Show warning first
    showWarning()
    
    // Call API on ALL warnings to create idle sessions (but don't wait for response)
    handleIdleWarningAPI()
}

// Show warning modal
const showWarning = () => {
    if (warningCount.value === 1) {
        warningTitle.value = '⚠️ First Alert - You appear to be idle'
        warningMessage.value = 'We noticed you haven\'t been active for a while. This warning will automatically proceed.'
    } else if (warningCount.value === 2) {
        warningTitle.value = '⚠️ Second Warning - Idle Activity Detected'
        warningMessage.value = 'This is your second warning. Continued inactivity will result in automatic logout and a penalty.'
    } else if (warningCount.value >= 3) {
        warningTitle.value = '🚨 Final Warning - Auto Logout Imminent'
        warningMessage.value = 'This is your final warning. You will be automatically logged out.'
    }
    
    showWarningModal.value = true
    
    // Start countdown
    countdown.value = 10
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

// Get current settings
const getSettings = async () => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        const response = await axios.get('/api/idle-monitoring/settings', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
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

// Start idle session with retry logic
const startIdleSessionWithRetry = async (retryCount = 0) => {
    const maxRetries = 3
    
    try {
        // Get fresh CSRF token
        const csrfToken = await ensureFreshCsrfToken()
        if (!csrfToken) {
            throw new Error('Could not get CSRF token')
        }
        
        console.log('CSRF Token:', csrfToken)
        
        // Start idle session
        console.log('Starting idle session... (attempt', retryCount + 1, ')')
        const response = await axios.post('/api/idle-monitoring/start-session', {}, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        currentSessionId = response.data.session_id
        console.log('Idle session started with ID:', currentSessionId)
        console.log('Full response:', response.data)
    } catch (error) {
        console.error('Error starting idle session (attempt', retryCount + 1, '):', error)
        console.error('Error response:', error.response?.data)
        
        // If CSRF error and we haven't exceeded max retries, try again
        if (error.response?.status === 419 && retryCount < maxRetries) {
            console.log('CSRF token mismatch, retrying... (attempt', retryCount + 2, ')')
            await new Promise(resolve => setTimeout(resolve, 1000)) // Wait 1 second before retry
            await startIdleSessionWithRetry(retryCount + 1)
        } else {
            console.error('Failed to start idle session after', retryCount + 1, 'attempts')
        }
    }
}

// Expose methods for parent component
defineExpose({
    startIdleMonitoring,
    stopIdleMonitoring,
    getSettings
})
</script>