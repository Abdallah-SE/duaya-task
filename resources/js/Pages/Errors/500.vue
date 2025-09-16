<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-md w-full mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                    <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Server Error</h1>
                
                <!-- Description -->
                <p class="text-gray-600 mb-6">
                    Something went wrong on our end. We're working to fix this issue.
                </p>

                <!-- Error Info -->
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">What happened?</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>• An unexpected error occurred on our servers</p>
                                <p>• Our team has been notified and is investigating</p>
                                <p>• Please try again in a few minutes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button 
                        @click="refreshPage" 
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                    >
                        Try Again
                    </button>
                    
                    <button 
                        @click="goHome" 
                        class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200"
                    >
                        Go Home
                    </button>
                </div>

                <!-- Help Text -->
                <p class="text-xs text-gray-500 mt-6">
                    If this problem persists, please contact our support team.
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
import { router } from '@inertiajs/vue3'

const props = defineProps({
    appName: {
        type: String,
        default: 'Duaya Task'
    }
})

const currentYear = new Date().getFullYear()

const refreshPage = () => {
    window.location.reload()
}

const goHome = () => {
    router.visit('/')
}
</script>


