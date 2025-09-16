/**
 * Example usage of the useIdleMonitoring composable
 * This file demonstrates how to use the composable in different scenarios
 */

import { useGlobalIdleMonitoring } from './useIdleMonitoring'

// Example 1: Basic usage in a component
export function useIdleExample() {
    const {
        isIdleMonitoringActive,
        currentWarningCount,
        isWarningModalVisible,
        startIdleMonitoring,
        stopIdleMonitoring,
        on: onIdleEvent
    } = useGlobalIdleMonitoring()

    // Listen to idle events
    onIdleEvent('monitoring:started', (data) => {
        console.log('Idle monitoring started for user:', data.userId)
    })

    onIdleEvent('idle:timeout', (data) => {
        console.log(`Warning ${data.warningCount}/${data.maxWarnings} triggered`)
    })

    onIdleEvent('warning:acknowledged', (data) => {
        console.log('User acknowledged warning, count reset to:', data.warningCount)
    })

    return {
        isIdleMonitoringActive,
        currentWarningCount,
        isWarningModalVisible
    }
}

// Example 2: Admin dashboard usage
export function useAdminIdleControl() {
    const {
        startIdleMonitoring,
        stopIdleMonitoring,
        updateSettings,
        getStatus,
        on: onIdleEvent
    } = useGlobalIdleMonitoring()

    // Monitor all idle events for admin dashboard
    onIdleEvent('monitoring:started', (data) => {
        // Update admin dashboard
        console.log('Admin: Monitoring started for user', data.userId)
    })

    onIdleEvent('idle:timeout', (data) => {
        // Log warning for admin tracking
        console.log('Admin: Warning triggered', data)
    })

    onIdleEvent('logout:forced', (data) => {
        // Log forced logout for admin
        console.log('Admin: User force logged out', data)
    })

    const startMonitoringForUser = (userId, settings) => {
        startIdleMonitoring(userId, settings, true)
    }

    const stopMonitoringForUser = () => {
        stopIdleMonitoring()
    }

    const updateMonitoringSettings = (newSettings) => {
        updateSettings(newSettings)
    }

    const getMonitoringStatus = () => {
        return getStatus()
    }

    return {
        startMonitoringForUser,
        stopMonitoringForUser,
        updateMonitoringSettings,
        getMonitoringStatus
    }
}

// Example 3: Settings page usage
export function useIdleSettings() {
    const {
        currentSettings,
        updateSettings,
        on: onIdleEvent
    } = useGlobalIdleMonitoring()

    // Listen for settings updates
    onIdleEvent('settings:updated', (data) => {
        console.log('Settings updated:', data.settings)
    })

    const updateIdleTimeout = (timeout) => {
        updateSettings({ idle_timeout: timeout })
    }

    const toggleMonitoring = (enabled) => {
        updateSettings({ idle_monitoring_enabled: enabled })
    }

    const updateMaxWarnings = (maxWarnings) => {
        updateSettings({ max_idle_warnings: maxWarnings })
    }

    return {
        currentSettings,
        updateIdleTimeout,
        toggleMonitoring,
        updateMaxWarnings
    }
}

// Example 4: Event logging for analytics
export function useIdleAnalytics() {
    const { on: onIdleEvent } = useGlobalIdleMonitoring()

    // Track all idle events for analytics
    const events = [
        'monitoring:started',
        'monitoring:stopped',
        'idle:timeout',
        'warning:acknowledged',
        'warning:reset',
        'logout:forced',
        'api:warning-success',
        'api:warning-error'
    ]

    events.forEach(eventName => {
        onIdleEvent(eventName, (data) => {
            // Send to analytics service
            console.log(`Analytics: ${eventName}`, data)
            
            // Example: Send to analytics API
            // analytics.track(eventName, data)
        })
    })

    return {
        // Return any analytics-specific methods if needed
    }
}
