<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-md w-full mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Too Many Requests</h1>
                
                <!-- Description -->
                <p class="text-gray-600 mb-6">
                    You've made too many requests too quickly. Please slow down and try again in a moment.
                </p>

                <!-- Rate Limit Info -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Rate Limit Information</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>• Login attempts are limited to 5 per minute</p>
                                <p>• Please wait before trying again</p>
                                <p v-if="retryAfter" class="mt-2 font-medium">
                                    Please wait {{ retryAfter }} seconds before trying again.
                                </p>
                                <p v-else-if="canRetry" class="mt-2 font-medium text-green-700">
                                    You can now try again.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div v-if="retryAfter && retryAfter > 0" class="mb-6">
                    <div class="bg-gray-100 rounded-full h-2 mb-2">
                        <div 
                            class="bg-yellow-500 h-2 rounded-full transition-all duration-1000 ease-linear"
                            :style="{ width: countdownPercentage + '%' }"
                        ></div>
                    </div>
                    <p class="text-sm text-gray-600">
                        Retry available in {{ Math.ceil(retryAfter) }} seconds
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button 
                        @click="goBack" 
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                    >
                        Go Back
                    </button>
                    
                    <button 
                        @click="retryLogin" 
                        :disabled="!canRetry"
                        :class="[
                            'w-full px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-200',
                            canRetry 
                                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500' 
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        ]"
                    >
                        {{ canRetry ? 'Try Again' : 'Please Wait...' }}
                    </button>
                </div>

                <!-- Help Text -->
                <p class="text-xs text-gray-500 mt-6">
                    If you continue to experience issues, please contact support.
                </p>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-sm text-gray-500">
                    © {{ currentYear }} {{ appName }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    retryAfter: {
        type: Number,
        default: 0
    },
    appName: {
        type: String,
        default: 'Duaya Task'
    }
})

const retryAfter = ref(props.retryAfter)
const currentYear = new Date().getFullYear()
let countdownInterval = null

const canRetry = computed(() => retryAfter.value <= 0)
const countdownPercentage = computed(() => {
    if (retryAfter.value <= 0) return 100
    return Math.max(0, ((60 - retryAfter.value) / 60) * 100)
})

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back()
    } else {
        router.visit('/')
    }
}

const retryLogin = () => {
    if (!canRetry.value) return
    
    // Determine which login page to redirect to based on current URL
    const currentPath = window.location.pathname
    if (currentPath.includes('/admin/login')) {
        router.visit('/admin/login')
    } else if (currentPath.includes('/employee/login')) {
        router.visit('/employee/login')
    } else {
        router.visit('/login')
    }
}

const startCountdown = () => {
    if (retryAfter.value <= 0) return
    
    countdownInterval = setInterval(() => {
        retryAfter.value -= 0.1
        if (retryAfter.value <= 0) {
            retryAfter.value = 0
            clearInterval(countdownInterval)
        }
    }, 100)
}

onMounted(() => {
    if (retryAfter.value > 0) {
        startCountdown()
    }
})

onUnmounted(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval)
    }
})
</script>


