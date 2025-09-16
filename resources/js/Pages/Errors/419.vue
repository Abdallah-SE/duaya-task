<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-md w-full mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 mb-6">
                    <svg class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>

                <!-- Title -->
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Session Expired</h1>
                
                <!-- Description -->
                <p class="text-gray-600 mb-6">
                    Your session has expired for security reasons. Please refresh the page and try again.
                </p>

                <!-- Session Info -->
                <div class="bg-orange-50 border border-orange-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800">Why did this happen?</h3>
                            <div class="mt-2 text-sm text-orange-700">
                                <p>• Your session token has expired</p>
                                <p>• This is a security measure to protect your account</p>
                                <p>• Simply refresh the page to continue</p>
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
                        Refresh Page
                    </button>
                    
                    <button 
                        @click="goToLogin" 
                        class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200"
                    >
                        Go to Login
                    </button>
                </div>

                <!-- Help Text -->
                <p class="text-xs text-gray-500 mt-6">
                    This usually happens when you've been inactive for a while or your browser cleared cookies.
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

const goToLogin = () => {
    // Determine which login page to redirect to based on current URL
    const currentPath = window.location.pathname
    if (currentPath.includes('/admin')) {
        router.visit('/admin/login')
    } else if (currentPath.includes('/employee')) {
        router.visit('/employee/login')
    } else {
        router.visit('/login')
    }
}
</script>


