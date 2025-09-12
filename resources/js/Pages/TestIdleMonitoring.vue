<template>
    <AppLayout 
        :user="user"
        :user-settings="userSettings"
        :initial-settings="initialSettings"
        :can-control-idle-monitoring="canControlIdleMonitoring"
        :is-idle-monitoring-enabled="isIdleMonitoringEnabled"
    >
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Idle Monitoring Test Page</h1>
                <p class="mt-2 text-gray-600">
                    This page tests the idle monitoring system across all pages for employees.
                </p>
            </div>

            <!-- Test Information -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Test Configuration</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-medium text-gray-700">User Information</h3>
                        <p class="text-sm text-gray-600">Name: {{ user.name }}</p>
                        <p class="text-sm text-gray-600">Email: {{ user.email }}</p>
                        <p class="text-sm text-gray-600">Roles: {{ testData.user_roles.join(', ') }}</p>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-700">Idle Settings</h3>
                        <p class="text-sm text-gray-600">Timeout: {{ testData.idle_timeout }} seconds</p>
                        <p class="text-sm text-gray-600">Max Warnings: {{ testData.max_warnings }}</p>
                        <p class="text-sm text-gray-600">
                            Monitoring: 
                            <span :class="testData.monitoring_enabled ? 'text-green-600' : 'text-red-600'">
                                {{ testData.monitoring_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Test Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-blue-900 mb-4">Test Instructions</h2>
                <div class="space-y-3 text-blue-800">
                    <p><strong>Step 1:</strong> Stop moving your mouse and keyboard for {{ testData.idle_timeout }} seconds</p>
                    <p><strong>Step 2:</strong> You should see a "First Alert" warning modal</p>
                    <p><strong>Step 3:</strong> Wait for the countdown to finish (10 seconds) or click "I'm Still Here"</p>
                    <p><strong>Step 4:</strong> If you don't interact, you'll get a "Second Warning" after another {{ testData.idle_timeout }} seconds</p>
                    <p><strong>Step 5:</strong> If you still don't interact, you'll get a "Final Warning" and then be automatically logged out</p>
                </div>
            </div>

            <!-- Test Controls -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Test Controls</h2>
                <div class="space-y-4">
                    <div>
                        <button
                            @click="simulateActivity"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            Simulate Activity (Reset Timer)
                        </button>
                        <p class="text-sm text-gray-600 mt-1">Click this to reset the idle timer</p>
                    </div>
                    <div>
                        <button
                            @click="checkIdleStatus"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Check Idle Status
                        </button>
                        <p class="text-sm text-gray-600 mt-1">Check current idle monitoring status</p>
                    </div>
                </div>
            </div>

            <!-- Status Display -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Status</h2>
                <div class="space-y-2">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Last Activity:</span> {{ lastActivity }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Idle Monitoring:</span> 
                        <span :class="isMonitoringActive ? 'text-green-600' : 'text-red-600'">
                            {{ isMonitoringActive ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Warning Count:</span> {{ warningCount }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    initialSettings: Object,
    canControlIdleMonitoring: Boolean,
    isIdleMonitoringEnabled: Boolean,
    testData: Object
})

// Local state
const lastActivity = ref('Just now')
const isMonitoringActive = ref(true)
const warningCount = ref(0)

// Update last activity timestamp
const updateLastActivity = () => {
    lastActivity.value = new Date().toLocaleTimeString()
}

// Simulate user activity
const simulateActivity = () => {
    updateLastActivity()
    // Trigger a mousemove event to reset the idle timer
    const event = new MouseEvent('mousemove', {
        view: window,
        bubbles: true,
        cancelable: true,
        clientX: Math.random() * window.innerWidth,
        clientY: Math.random() * window.innerHeight
    })
    document.dispatchEvent(event)
}

// Check idle status
const checkIdleStatus = () => {
    updateLastActivity()
    console.log('Idle monitoring status check:', {
        isEnabled: props.isIdleMonitoringEnabled,
        settings: props.userSettings,
        user: props.user
    })
}

// Listen for idle warning events
onMounted(() => {
    // Listen for custom idle warning events
    window.addEventListener('idle-warning', (event) => {
        warningCount.value = event.detail.warningCount
        console.log('Idle warning received:', event.detail)
    })
    
    // Listen for user activity
    const events = ['mousemove', 'keydown', 'click', 'scroll']
    events.forEach(event => {
        document.addEventListener(event, updateLastActivity)
    })
    
    updateLastActivity()
})
</script>
