<template>
    <!-- Test Buttons (for debugging) -->
    <div style="position: fixed; top: 10px; right: 10px; z-index: 10000; display: flex; gap: 5px;">
        <button 
            @click="testIdleWarning"
            style="background: #ff6b6b; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 12px;"
        >
            Test Warning
        </button>
        <button 
            @click="testDatabase"
            style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 12px;"
        >
            Test DB
        </button>
    </div>

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
            
            <button
                @click="dismissWarning"
                style="background: #3498db; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%;"
                onmouseover="this.style.background='#2980b9'"
                onmouseout="this.style.background='#3498db'"
            >
                I'm Still Here
            </button>
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
let csrfRefreshTimer = null
let currentSessionId = null

// Initialize the idle monitor
onMounted(() => {
    console.log('IdleMonitor mounted with props:', props)
    console.log('Initial settings:', props.initialSettings)
    
    if (props.initialSettings && props.initialSettings.idle_monitoring_enabled) {
        idleTimeout.value = props.initialSettings.idle_timeout * 1000
        maxWarnings.value = props.initialSettings.max_idle_warnings
        console.log('Starting idle monitoring with timeout:', idleTimeout.value, 'ms')
        startIdleMonitoring()
        startCsrfRefresh()
    } else {
        console.log('Idle monitoring disabled or no settings provided')
        console.log('Settings check:', {
            hasSettings: !!props.initialSettings,
            monitoringEnabled: props.initialSettings?.idle_monitoring_enabled,
            timeout: props.initialSettings?.idle_timeout
        })
    }
})

onUnmounted(() => {
    stopIdleMonitoring()
    stopCsrfRefresh()
})

// Start idle monitoring
const startIdleMonitoring = () => {
    console.log('Starting idle monitoring...')
    
    // Add event listeners for user activity
    document.addEventListener('mousemove', resetIdleTimer)
    document.addEventListener('keydown', resetIdleTimer)
    document.addEventListener('scroll', resetIdleTimer)
    document.addEventListener('click', resetIdleTimer)
    document.addEventListener('touchstart', resetIdleTimer)
    
    console.log('Event listeners added, starting initial timer...')
    
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

// Dismiss warning when user clicks "I'm Still Here"
const dismissWarning = () => {
    console.log('User dismissed warning, resetting everything')
    
    // Clear all timers
    if (idleTimer) {
        clearTimeout(idleTimer)
    }
    if (warningTimer) {
        clearTimeout(warningTimer)
    }
    
    // Hide modal and reset warning count
    showWarningModal.value = false
    warningCount.value = 0
    
    // End current idle session if exists
    if (currentSessionId) {
        endIdleSession()
    }
    
    // Start fresh idle timer
    console.log('Starting fresh idle timer after user dismissal')
    idleTimer = setTimeout(() => {
        console.log('Idle timeout triggered!')
        handleIdleTimeout()
    }, idleTimeout.value)
}

// Reset the idle timer
const resetIdleTimer = () => {
    console.log('resetIdleTimer called, showWarningModal:', showWarningModal.value, 'warningCount:', warningCount.value)
    
    // Clear existing timers
    if (idleTimer) {
        clearTimeout(idleTimer)
        idleTimer = null
        console.log('Cleared existing idle timer')
    }
    if (warningTimer) {
        clearTimeout(warningTimer)
        warningTimer = null
        console.log('Cleared existing warning timer')
    }
    
    // Only hide warning modal if user clicked "I'm Still Here" button
    // Don't hide it just because user moved mouse - they might be trying to click the button
    if (showWarningModal.value) {
        console.log('Warning modal is showing, not resetting timer')
        return // Don't reset the timer if modal is showing
    }
    
    // End current idle session if exists
    if (currentSessionId) {
        console.log('Ending current idle session:', currentSessionId)
        endIdleSession()
    }
    
    // Reset warning count when user is active
    if (warningCount.value > 0) {
        console.log('Resetting warning count from', warningCount.value, 'to 0')
        warningCount.value = 0
    }
    
    // Start new idle timer
    console.log('Starting new idle timer with timeout:', idleTimeout.value, 'ms')
    idleTimer = setTimeout(() => {
        console.log('Idle timer expired, calling handleIdleTimeout')
        handleIdleTimeout()
    }, idleTimeout.value)
}

// Handle idle timeout
const handleIdleTimeout = async () => {
    console.log('handleIdleTimeout called, current warning count:', warningCount.value)
    
    // Increment warning count
    warningCount.value++
    console.log('Incrementing warning count to:', warningCount.value)
    
    // Start idle session only on first warning
    if (warningCount.value === 1) {
        await startIdleSessionWithRetry()
    }
    
    // Show warning
    showWarning()
}

// Show warning modal
const showWarning = () => {
    console.log('showWarning called, warningCount:', warningCount.value)
    
    if (warningCount.value === 1) {
        warningTitle.value = '⚠️ First Alert - You appear to be idle'
        warningMessage.value = 'We noticed you haven\'t been active for a while. Please confirm you\'re still here to continue your session.'
    } else if (warningCount.value === 2) {
        warningTitle.value = '⚠️ Second Warning - Idle Activity Detected'
        warningMessage.value = 'This is your second warning. Continued inactivity will result in automatic logout and a penalty.'
    } else if (warningCount.value >= 3) {
        warningTitle.value = '🚨 Final Warning - Auto Logout Imminent'
        warningMessage.value = 'This is your final warning. You will be automatically logged out if you don\'t respond.'
    }
    
    console.log('Setting showWarningModal to true')
    showWarningModal.value = true
    console.log('showWarningModal value after setting:', showWarningModal.value)
    console.log('Modal should be visible now')
    
    // Set timer for automatic action if user doesn't respond
    // Give user 10 seconds to respond to each warning
    warningTimer = setTimeout(() => {
        handleWarningTimeout()
    }, 10000) // 10 seconds to respond
}

// Handle warning timeout (user didn't respond)
const handleWarningTimeout = async () => {
    console.log('handleWarningTimeout called, warningCount:', warningCount.value)
    
    // Hide the current modal
    showWarningModal.value = false
    
    // If we haven't reached the third warning yet, continue with next warning
    if (warningCount.value < 3) {
        console.log('Continuing with next warning...')
        // Start a new idle timer for the next warning (shorter timeout for subsequent warnings)
        const nextTimeout = Math.max(1000, idleTimeout.value / 2) // At least 1 second, or half the original timeout
        console.log('Starting next warning timer with timeout:', nextTimeout, 'ms')
        idleTimer = setTimeout(() => {
            handleIdleTimeout()
        }, nextTimeout)
        return
    }
    
    // If we've reached the third warning, apply penalty and logout
    try {
        console.log('Third warning reached, applying penalty and logging out...')
        console.log('Warning count:', warningCount.value, 'Session ID:', currentSessionId)
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        const response = await axios.post('/api/idle-monitoring/handle-warning', {
            warning_count: warningCount.value,
            session_id: currentSessionId
        }, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        
        console.log('Handle warning response:', response.data)
        
        if (response.data.logout_required) {
            console.log('Logout required, redirecting...')
            window.location.href = '/login?message=inactivity_logout'
        } else {
            // This shouldn't happen if we've reached max warnings
            console.log('Unexpected response, redirecting anyway...')
            window.location.href = '/login?message=inactivity_logout'
        }
        
    } catch (error) {
        console.error('Error handling warning:', error)
        if (error.response?.status === 401) {
            // User was logged out by server - force redirect
            console.log('User logged out by server (401)')
            window.location.href = '/login?message=inactivity_logout'
        } else {
            // Other error - still try to redirect to be safe
            console.error('Unexpected error during idle monitoring:', error)
            window.location.href = '/login?message=error'
        }
    }
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
        console.log('Idle session ended:', currentSessionId)
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

// Test function to manually trigger idle warning
const testIdleWarning = () => {
    console.log('Manually triggering idle warning for testing...')
    // Clear any existing timers
    if (idleTimer) {
        clearTimeout(idleTimer)
    }
    if (warningTimer) {
        clearTimeout(warningTimer)
    }
    // Trigger the idle timeout
    handleIdleTimeout()
}

// Test function to test database operations
const testDatabase = async () => {
    console.log('Testing database operations...')
    try {
        const csrfToken = await ensureFreshCsrfToken()
        if (!csrfToken) {
            throw new Error('Could not get CSRF token')
        }
        
        const response = await axios.get('/api/idle-monitoring/test-db', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        console.log('Database test response:', response.data)
        alert('Database test successful! Check console for details.')
    } catch (error) {
        console.error('Database test failed:', error)
        alert('Database test failed! Check console for details.')
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
            console.log('Using existing CSRF token:', freshToken)
            return freshToken
        }
        
        // If not available, refresh the cookie
        await axios.get('/sanctum/csrf-cookie')
        console.log('Fresh CSRF cookie obtained')
        
        // Wait a bit for the cookie to be set
        await new Promise(resolve => setTimeout(resolve, 200))
        
        // Try to get the fresh token
        freshToken = getCsrfToken()
        if (freshToken) {
            console.log('Fresh CSRF token retrieved from cookie:', freshToken)
            return freshToken
        }
        
        // Last resort: make a request to get the token
        freshToken = await getCsrfTokenFromRequest()
        if (freshToken) {
            console.log('Fresh CSRF token retrieved from request:', freshToken)
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
    getSettings,
    testIdleWarning
})
</script>