import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

export function useIdleMonitoring(userId, settings, isEnabled) {
    // Reactive state
    const showWarningModal = ref(false)
    const warningCount = ref(0)
    const countdown = ref(10)
    const currentSessionId = ref(null)
    
    // Timer references
    let idleTimer = null
    let warningTimer = null
    let countdownTimer = null
    let csrfRefreshTimer = null

    // Computed properties
    const isMonitoringEnabled = computed(() => {
        return isEnabled.value && settings.value?.idle_monitoring_enabled
    })

    const idleTimeout = computed(() => {
        return (settings.value?.idle_timeout || 5) * 1000
    })

    const maxWarnings = computed(() => {
        return settings.value?.max_idle_warnings || 3
    })

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
        if (!isMonitoringEnabled.value) return

        console.log('🚀 Starting idle monitoring...')
        console.log('Current timeout:', idleTimeout.value, 'ms')
        
        // Add event listeners for user activity
        const events = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart', 'keyup', 'mousedown']
        events.forEach(event => {
            document.addEventListener(event, resetIdleTimer)
        })
        
        // Start CSRF refresh
        startCsrfRefresh()
        
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
        clearAllTimers()
        
        // End current session if exists
        if (currentSessionId.value) {
            endIdleSession()
        }
        
        console.log('✅ Idle monitoring stopped')
    }

    const clearAllTimers = () => {
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
        if (csrfRefreshTimer) {
            clearInterval(csrfRefreshTimer)
            csrfRefreshTimer = null
        }
    }

    const resetIdleTimer = () => {
        // Clear existing timers
        clearAllTimers()
        
        // Don't reset if modal is showing (user might be trying to click button)
        if (showWarningModal.value) {
            return
        }
        
        // End current idle session if exists (user became active)
        if (currentSessionId.value) {
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

    const handleIdleTimeout = async () => {
        // Increment warning count
        warningCount.value++
        
        console.log(`⚠️ Idle timeout detected - Warning ${warningCount.value}/${maxWarnings.value}`)
        
        // Show warning first
        showWarning()
        
        // Call API on ALL warnings to create idle sessions
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

    const handleWarningTimeout = async () => {
        // Hide the current modal
        showWarningModal.value = false
        
        // If we haven't reached the third warning yet, continue with next warning
        if (warningCount.value < 3) {
            // Start a new idle timer for the next warning (shorter timeout for subsequent warnings)
            const nextTimeout = Math.max(2000, idleTimeout.value / 2)
            idleTimer = setTimeout(() => {
                handleIdleTimeout()
            }, nextTimeout)
            return
        }
        
        // If we've reached the third warning, wait a moment for API response then logout
        setTimeout(() => {
            window.location.href = '/login?message=inactivity_logout'
        }, 2000)
    }

    const acknowledgeWarning = () => {
        showWarningModal.value = false
        
        // End current session
        if (currentSessionId.value) {
            endIdleSession()
        }
        
        // Reset warning count
        warningCount.value = 0
        
        // Start new idle timer
        resetIdleTimer()
        
        console.log('✅ User acknowledged warning - resetting idle monitoring')
    }

    const forceLogout = () => {
        window.location.href = '/login?message=manual_logout'
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
            
            // Store new session ID for this warning
            if (data.session_id) {
                currentSessionId.value = data.session_id
                console.log('📝 New idle session created for warning', warningCount.value, 'with ID:', currentSessionId.value)
            }
            
            // Only logout if this is the third warning and logout is required
            if (warningCount.value >= 3 && data.logout_required) {
                console.log('Third warning reached - logout required, redirecting...')
                window.location.href = '/login?message=inactivity_logout'
            }
            
        } catch (error) {
            console.error('Error handling warning API:', error)
            if (error.response?.status === 401) {
                window.location.href = '/login?message=inactivity_logout'
            } else if (warningCount.value >= 3) {
                window.location.href = '/login?message=inactivity_logout'
            }
        }
    }

    const endIdleSession = async () => {
        if (!currentSessionId.value) return
        
        try {
            const csrfToken = await ensureFreshCsrfToken()
            if (!csrfToken) return
            
            await axios.post('/api/idle-monitoring/end-session', {
                session_id: currentSessionId.value
            }, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            currentSessionId.value = null
        } catch (error) {
            console.error('Error ending idle session:', error)
            currentSessionId.value = null
        }
    }

    const startCsrfRefresh = () => {
        csrfRefreshTimer = setInterval(async () => {
            await ensureFreshCsrfToken()
        }, 5 * 60 * 1000) // 5 minutes
    }

    const ensureFreshCsrfToken = async () => {
        try {
            let freshToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            if (freshToken) return freshToken
            
            await axios.get('/sanctum/csrf-cookie')
            await new Promise(resolve => setTimeout(resolve, 200))
            
            freshToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            return freshToken
        } catch (error) {
            console.error('Failed to get fresh CSRF token:', error)
            return null
        }
    }

    // Lifecycle
    onMounted(() => {
        if (isMonitoringEnabled.value) {
            startIdleMonitoring()
        }
    })

    onUnmounted(() => {
        stopIdleMonitoring()
    })

    return {
        // State
        showWarningModal,
        warningCount,
        countdown,
        currentSessionId,
        
        // Computed
        isMonitoringEnabled,
        idleTimeout,
        maxWarnings,
        warningTitle,
        warningMessage,
        
        // Methods
        startIdleMonitoring,
        stopIdleMonitoring,
        acknowledgeWarning,
        forceLogout
    }
}
