import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import axios from 'axios'

/**
 * @typedef {Object} IdleSettings
 * @property {number} idle_timeout - Timeout in seconds before showing warning
 * @property {boolean} idle_monitoring_enabled - Whether monitoring is enabled
 * @property {number} max_idle_warnings - Maximum number of warnings before logout
 */

/**
 * @typedef {Object} IdleStatus
 * @property {boolean} isRunning - Whether monitoring is currently active
 * @property {number|null} currentUserId - ID of the user being monitored
 * @property {number} warningCount - Current warning count
 * @property {boolean} showWarningModal - Whether warning modal is visible
 * @property {number} countdown - Current countdown value
 * @property {IdleSettings|null} settings - Current monitoring settings
 */

/**
 * Event-driven idle monitoring composable
 * Provides centralized state management for idle monitoring across the application
 * 
 * @returns {Object} Composable API with state, methods, and event system
 */
export function useIdleMonitoring() {
    // Global singleton state - shared across all instances
    const globalState = reactive({
        isRunning: false,
        showWarningModal: false,
        warningCount: 0,
        countdown: 10,
        currentUserId: null,
        currentSettings: null,
        idleTimer: null,
        countdownTimer: null,
        eventListeners: [],
        isInitialized: false,
        inAlertSequence: false
    })

    // Event system for state changes
    const eventListeners = new Map()
    
    /**
     * Event emitter for idle monitoring events
     */
    const eventEmitter = {
        on(event, callback) {
            if (!eventListeners.has(event)) {
                eventListeners.set(event, new Set())
            }
            eventListeners.get(event).add(callback)
            
            // Return unsubscribe function
            return () => {
                const listeners = eventListeners.get(event)
                if (listeners) {
                    listeners.delete(callback)
                    if (listeners.size === 0) {
                        eventListeners.delete(event)
                    }
                }
            }
        },
        
        emit(event, data = null) {
            const listeners = eventListeners.get(event)
            if (listeners) {
                listeners.forEach(callback => {
                    try {
                        callback(data)
                    } catch (error) {
                        console.error(`Error in event listener for ${event}:`, error)
                    }
                })
            }
        },
        
        off(event, callback) {
            const listeners = eventListeners.get(event)
            if (listeners) {
                listeners.delete(callback)
            }
        },
        
        clear() {
            eventListeners.clear()
        }
    }

    // Computed properties
    const maxWarnings = computed(() => 3) // Fixed to 3 as per task requirements
    
    const isIdleMonitoringActive = computed(() => globalState.isRunning)
    const isWarningModalVisible = computed(() => globalState.showWarningModal)
    const currentWarningCount = computed(() => globalState.warningCount)
    const currentCountdown = computed(() => globalState.countdown)
    const currentUser = computed(() => globalState.currentUserId)
    const currentSettings = computed(() => globalState.currentSettings)

    /**
     * Start idle monitoring for a specific user
     * @param {number} userId - The user ID to monitor
     * @param {IdleSettings} settings - Idle monitoring settings
     * @param {boolean} [isEnabled=true] - Whether monitoring is enabled
     * @fires monitoring:started - When monitoring starts successfully
     * @fires monitoring:disabled - When monitoring is disabled
     * @fires monitoring:already-running - When monitoring is already running for the user
     * @fires monitoring:error - When an error occurs during startup
     */
    const startIdleMonitoring = (userId, settings, isEnabled = true) => {
        if (!isEnabled) {
            console.log('❌ Idle monitoring disabled for user:', userId)
            eventEmitter.emit('monitoring:disabled', { userId })
            return
        }

        // Check if already running for this user
        if (globalState.isRunning && globalState.currentUserId === userId) {
            console.log('🔄 Idle monitoring already running for user:', userId)
            eventEmitter.emit('monitoring:already-running', { userId })
            return
        }
        
        // If already initialized and running, don't start again
        if (globalState.isInitialized && globalState.isRunning) {
            console.log('🔄 Idle monitoring already initialized and running')
            return
        }

        // If running for different user, stop first
        if (globalState.isRunning && globalState.currentUserId !== userId) {
            console.log('🔄 Stopping idle monitoring for different user:', globalState.currentUserId)
            stopIdleMonitoring()
        }

        try {
            console.log('🚀 Starting idle monitoring for user:', userId)
            console.log('Idle timeout:', settings?.idle_timeout, 'seconds')
            
            // Update global state
            globalState.isRunning = true
            globalState.currentUserId = userId
            globalState.currentSettings = settings
            globalState.isInitialized = true
            
            // Add event listeners for user activity with passive option for better performance
            const events = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart', 'keyup', 'mousedown']
            const eventHandler = resetIdleTimer
            
            events.forEach(event => {
                document.addEventListener(event, eventHandler, { passive: true })
                globalState.eventListeners.push({ event, handler: eventHandler })
            })
            
            // Start the initial timer
            resetIdleTimer()
            
            // Emit events
            eventEmitter.emit('monitoring:started', { userId, settings })
            console.log('✅ Idle monitoring started for user:', userId)
        } catch (error) {
            console.error('❌ Failed to start idle monitoring:', error)
            globalState.isRunning = false
            globalState.currentUserId = null
            eventEmitter.emit('monitoring:error', { error, userId })
        }
    }

    /**
     * Stop idle monitoring and clean up resources
     * @fires monitoring:stopped - When monitoring stops successfully
     * @fires monitoring:error - When an error occurs during cleanup
     */
    const stopIdleMonitoring = () => {
        try {
            console.log('🛑 Stopping idle monitoring for user:', globalState.currentUserId)
            
            // Reset global state
            globalState.isRunning = false
            globalState.currentUserId = null
            globalState.currentSettings = null
            globalState.isInitialized = false
            globalState.inAlertSequence = false
            
            // Remove event listeners
            globalState.eventListeners.forEach(({ event, handler }) => {
                document.removeEventListener(event, handler)
            })
            globalState.eventListeners = []
            
            // Clear timers safely
            if (globalState.idleTimer) {
                clearTimeout(globalState.idleTimer)
                globalState.idleTimer = null
            }
            if (globalState.countdownTimer) {
                clearInterval(globalState.countdownTimer)
                globalState.countdownTimer = null
            }
            
            // Reset warning state
            globalState.showWarningModal = false
            globalState.warningCount = 0
            globalState.countdown = 10
            
            // Emit events
            eventEmitter.emit('monitoring:stopped', { userId: globalState.currentUserId })
            console.log('✅ Idle monitoring stopped')
        } catch (error) {
            console.error('❌ Error stopping idle monitoring:', error)
            eventEmitter.emit('monitoring:error', { error })
        }
    }

    /**
     * Reset idle timer when user activity is detected
     */
    const resetIdleTimer = () => {
        // Only process if this is the current user
        if (!globalState.isRunning) {
            return
        }

        // Clear existing timers
        if (globalState.idleTimer) {
            clearTimeout(globalState.idleTimer)
            globalState.idleTimer = null
        }
        if (globalState.countdownTimer) {
            clearInterval(globalState.countdownTimer)
            globalState.countdownTimer = null
        }
        
        // If modal is showing, restart the countdown timer
        if (globalState.showWarningModal) {
            console.log('🔄 User moved mouse while modal is showing - restarting countdown')
            startCountdown()
            return
        }
        
        // Only reset warning count if user is truly active and not in alert sequence
        if (globalState.warningCount > 0 && !globalState.showWarningModal && !globalState.inAlertSequence) {
            globalState.warningCount = 0
            console.log('🔄 User became active - reset warning count to 0')
            eventEmitter.emit('warning:reset', { warningCount: globalState.warningCount })
        }
        
        // Only start new idle timer if no warning modal is showing
        if (!globalState.showWarningModal) {
            try {
                const timeoutSeconds = Math.max(1, Math.min(300, globalState.currentSettings?.idle_timeout || 5))
                const timeout = timeoutSeconds * 1000
                console.log('Setting idle timeout to:', timeout, 'ms (', timeoutSeconds, 'seconds from idle_settings)')
                
                globalState.idleTimer = setTimeout(() => {
                    handleIdleTimeout()
                }, timeout)
                
                eventEmitter.emit('timer:reset', { timeoutSeconds })
            } catch (error) {
                console.error('❌ Error setting idle timer:', error)
                eventEmitter.emit('monitoring:error', { error })
            }
        }
    }

    /**
     * Handle idle timeout - show warning
     */
    const handleIdleTimeout = async () => {
        if (!globalState.isRunning) {
            return
        }

        // Prevent multiple simultaneous calls
        if (globalState.showWarningModal) {
            console.log('⚠️ Warning modal already showing, ignoring duplicate timeout')
            return
        }
        
        // Increment warning count
        globalState.warningCount++
        
        // Set alert sequence flag on first alert
        if (globalState.warningCount === 1) {
            globalState.inAlertSequence = true
        }
        
        console.log(`⚠️ Idle timeout detected - Alert ${globalState.warningCount}/${maxWarnings.value}`)
        console.log(`⏰ User was idle for ${globalState.currentSettings?.idle_timeout || 5} seconds`)
        
        // Show warning first
        showWarning()
        
        // Call API to store session in database
        await handleIdleWarningAPI()
        
        // Emit events
        eventEmitter.emit('idle:timeout', { 
            warningCount: globalState.warningCount, 
            maxWarnings: maxWarnings.value 
        })
    }

    /**
     * Show warning modal
     */
    const showWarning = () => {
        globalState.showWarningModal = true
        globalState.countdown = 10
        startCountdown()
        
        eventEmitter.emit('warning:shown', { 
            warningCount: globalState.warningCount,
            countdown: globalState.countdown 
        })
    }

    /**
     * Start countdown timer
     */
    const startCountdown = () => {
        if (globalState.countdownTimer) {
            clearInterval(globalState.countdownTimer)
        }
        
        globalState.countdownTimer = setInterval(() => {
            globalState.countdown--
            
            eventEmitter.emit('countdown:tick', { countdown: globalState.countdown })
            
            if (globalState.countdown <= 0) {
                handleWarningTimeout()
            }
        }, 1000)
    }

    /**
     * Handle warning timeout
     */
    const handleWarningTimeout = () => {
        if (!globalState.isRunning) {
            return
        }

        // Hide the current modal
        globalState.showWarningModal = false
        
        console.log('⏰ Alert timeout - user did not respond to alert', globalState.warningCount, 'of', maxWarnings.value)
        
        // If this is the 3rd alert, trigger auto logout
        if (globalState.warningCount >= maxWarnings.value) {
            console.log('🚪 Third alert timeout - triggering auto logout and penalty')
            globalState.inAlertSequence = false // Clear alert sequence flag
            eventEmitter.emit('warning:timeout', { 
                warningCount: globalState.warningCount,
                maxWarnings: maxWarnings.value 
            })
            // Force logout will be called by the component
            return
        }
        
        // If we haven't reached the max warnings yet, wait for another idle timeout
        console.log(`⏳ Alert ${globalState.warningCount} dismissed - waiting for next idle timeout...`)
        
        // Start idle timer for next alert (don't show warning immediately)
        resetIdleTimer()
    }

    /**
     * Handle idle warning API call
     */
    const handleIdleWarningAPI = async () => {
        try {
            const response = await axios.post('/idle-monitoring/handle-warning', {
                warning_count: globalState.warningCount
            })
            
            console.log('✅ Idle warning API called successfully:', response.data)
            eventEmitter.emit('api:warning-success', { 
                response: response.data, 
                warningCount: globalState.warningCount 
            })
        } catch (error) {
            console.error('❌ Failed to call idle warning API:', error)
            
            // Check if this is a 401 response (logout required)
            if (error.response?.status === 401 && error.response?.data?.logout_required) {
                console.log('🚪 Logout required - redirecting to login')
                eventEmitter.emit('logout:required', { 
                    reason: 'third_warning', 
                    warningCount: globalState.warningCount 
                })
                // Redirect to login page
                window.location.href = '/login?message=inactivity_logout'
                return
            }
            
            eventEmitter.emit('api:warning-error', { error, warningCount: globalState.warningCount })
        }
    }

    /**
     * Acknowledge warning - user clicked "I'm Still Here"
     * Resets warning count and restarts idle monitoring
     * @fires warning:acknowledged - When user acknowledges the warning
     */
    const acknowledgeWarning = () => {
        if (!globalState.isRunning || !globalState.showWarningModal) {
            return
        }

        globalState.showWarningModal = false
        globalState.warningCount = 0
        globalState.countdown = 10
        globalState.inAlertSequence = false // Clear alert sequence flag
        
        // Clear countdown timer
        if (globalState.countdownTimer) {
            clearInterval(globalState.countdownTimer)
            globalState.countdownTimer = null
        }
        
        // Reset idle timer
        resetIdleTimer()
        
        eventEmitter.emit('warning:acknowledged', { 
            warningCount: globalState.warningCount 
        })
        
        console.log('✅ Warning acknowledged - resetting idle monitoring')
    }

    /**
     * Force logout - user clicked "Logout Now" or max warnings reached
     * Redirects to appropriate logout route based on user role
     * @fires logout:forced - When force logout is initiated
     * @fires logout:error - When logout fails
     */
    const forceLogout = async () => {
        if (!globalState.isRunning) {
            return
        }

        try {
            console.log('🚪 Force logout initiated for user:', globalState.currentUserId)
            
            // Stop monitoring first
            stopIdleMonitoring()
            
            // Determine logout route based on current user context
            const user = globalState.currentUser
            const logoutUrl = user?.role_names?.includes('employee') ? '/employee/logout' : '/admin/logout'
            
            eventEmitter.emit('logout:forced', { 
                userId: globalState.currentUserId,
                reason: 'manual_logout' 
            })
            
            // Redirect to logout
            window.location.href = logoutUrl
            
        } catch (error) {
            console.error('❌ Failed to force logout:', error)
            eventEmitter.emit('logout:error', { error })
            
            // Fallback - redirect to role-based logout
            const user = globalState.currentUser
            const fallbackUrl = user?.role_names?.includes('employee') ? '/employee/logout' : '/admin/logout'
            window.location.href = fallbackUrl
        }
    }

    /**
     * Update settings for current monitoring session
     * @param {Partial<IdleSettings>} newSettings - New settings to merge with current settings
     * @fires settings:updated - When settings are updated
     */
    const updateSettings = (newSettings) => {
        if (globalState.isRunning) {
            globalState.currentSettings = { ...globalState.currentSettings, ...newSettings }
            eventEmitter.emit('settings:updated', { settings: globalState.currentSettings })
        }
    }

    /**
     * Get current monitoring status
     * @returns {IdleStatus} Current monitoring status
     */
    const getStatus = () => ({
        isRunning: globalState.isRunning,
        currentUserId: globalState.currentUserId,
        warningCount: globalState.warningCount,
        showWarningModal: globalState.showWarningModal,
        countdown: globalState.countdown,
        settings: globalState.currentSettings
    })

    /**
     * Check if monitoring is running for a specific user
     * @param {number} userId - User ID to check
     * @returns {boolean} Whether monitoring is running for the user
     */
    const isMonitoringUser = (userId) => {
        return globalState.isRunning && globalState.currentUserId === userId
    }

    const getGlobalState = () => {
        return globalState
    }

    // Cleanup on unmount
    onUnmounted(() => {
        stopIdleMonitoring()
        eventEmitter.clear()
    })

    return {
        // State (readonly)
        isIdleMonitoringActive,
        isWarningModalVisible,
        currentWarningCount,
        currentCountdown,
        currentUser,
        currentSettings,
        maxWarnings,

        // Methods
        startIdleMonitoring,
        stopIdleMonitoring,
        acknowledgeWarning,
        forceLogout,
        updateSettings,
        getStatus,
        isMonitoringUser,
        getGlobalState,

        // Event system
        on: eventEmitter.on,
        off: eventEmitter.off,
        emit: eventEmitter.emit
    }
}

/**
 * Global instance for app-wide idle monitoring
 * This ensures only one instance exists across the entire application
 * 
 * @returns {Object} The global idle monitoring composable instance
 */
let globalIdleInstance = null

export function useGlobalIdleMonitoring() {
    if (!globalIdleInstance) {
        globalIdleInstance = useIdleMonitoring()
    }
    return globalIdleInstance
}
