<template>
    <!-- Idle Warning Modal -->
    <div v-if="isWarningModalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <!-- Warning Header -->
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg v-if="currentWarningCount === 1" class="h-8 w-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else-if="currentWarningCount === 2" class="h-8 w-8 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
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
            <div v-if="currentWarningCount > 1" class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Warning Progress</span>
                    <span>{{ currentWarningCount }}/{{ maxWarnings }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="h-2 rounded-full transition-all duration-300"
                        :class="currentWarningCount === 2 ? 'bg-orange-500' : 'bg-red-500'"
                        :style="{ width: `${(currentWarningCount / maxWarnings) * 100}%` }"
                    ></div>
                </div>
            </div>
            
            <!-- Countdown Timer -->
            <div class="mb-6">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">
                        This warning will automatically proceed in {{ currentCountdown }} seconds...
                    </p>
                    <div class="w-full bg-gray-200 rounded-full h-1">
                        <div 
                            class="h-1 rounded-full bg-blue-500 transition-all duration-1000"
                            :style="{ width: `${(currentCountdown / 10) * 100}%` }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <button
                    v-if="currentWarningCount < maxWarnings"
                    @click="acknowledgeWarning"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    I'm Still Here
                </button>
                <button
                    v-if="currentWarningCount >= maxWarnings"
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
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { useGlobalIdleMonitoring } from '@/Composables/useIdleMonitoring'

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

// Use the global idle monitoring composable
const {
    isIdleMonitoringActive,
    isWarningModalVisible,
    currentWarningCount,
    currentCountdown,
    currentUser,
    currentSettings,
    maxWarnings,
    startIdleMonitoring,
    stopIdleMonitoring,
    acknowledgeWarning,
    forceLogout,
    updateSettings,
    isMonitoringUser,
    getGlobalState,
    on: onIdleEvent
} = useGlobalIdleMonitoring()

// Computed properties for UI
const warningTitle = computed(() => {
    switch (currentWarningCount.value) {
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
    switch (currentWarningCount.value) {
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

// Lifecycle hooks
onMounted(() => {
    console.log('🔍 IdleMonitor mounted with props:', {
        userId: props.userId,
        isIdleMonitoringEnabled: props.isIdleMonitoringEnabled,
        initialSettings: props.initialSettings
    })
    
    // Check current monitoring status
    const isCurrentlyMonitoring = isMonitoringUser(props.userId)
    const isCurrentlyRunning = isIdleMonitoringActive.value
    const globalState = getGlobalState()
    
    console.log('🔍 Current monitoring status:', {
        isCurrentlyMonitoring,
        isCurrentlyRunning,
        currentUserId: globalState.currentUserId,
        isRunning: globalState.isRunning
    })
    
    // Only start monitoring if it's not already running for this user
    if (!isCurrentlyMonitoring) {
        console.log('🚀 Starting idle monitoring for user:', props.userId)
        startIdleMonitoring(props.userId, props.initialSettings, props.isIdleMonitoringEnabled)
    } else {
        console.log('🔄 Idle monitoring already running for user:', props.userId)
        // Update settings if they changed
        if (props.initialSettings) {
            updateSettings(props.initialSettings)
        }
    }
    
    // Set up event listeners for debugging/logging
    onIdleEvent('monitoring:started', (data) => {
        console.log('✅ Idle monitoring started:', data)
    })
    
    onIdleEvent('monitoring:stopped', (data) => {
        console.log('🛑 Idle monitoring stopped:', data)
    })
    
    onIdleEvent('idle:timeout', (data) => {
        console.log('⚠️ Idle timeout:', data)
    })
    
    onIdleEvent('warning:acknowledged', (data) => {
        console.log('✅ Warning acknowledged:', data)
    })
    
    onIdleEvent('logout:forced', (data) => {
        console.log('🚪 Force logout:', data)
    })
})

onUnmounted(() => {
    // Don't stop idle monitoring on unmount during navigation
    // The global singleton will persist across page changes
    // The monitoring will continue running in the background
    console.log('🔄 IdleMonitor unmounted - keeping monitoring active for navigation')
})
</script>
