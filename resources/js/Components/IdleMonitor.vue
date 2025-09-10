<template>
    <div>
        <!-- Idle Alert Modal -->
        <div v-if="showAlert" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Inactivity Alert
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        You have been inactive for {{ timeout }} seconds. 
                                        <span v-if="warningCount > 0">
                                            Warning {{ warningCount }} of {{ maxWarnings }}.
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="resetIdleTimer" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            I'm Active
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auto Logout Modal -->
        <div v-if="showLogout" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Auto Logout
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        You have been inactive for too long and will be automatically logged out.
                                        A penalty has been applied to your account.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="forceLogout" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Logout Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    timeout: {
        type: Number,
        default: 5
    },
    enabled: {
        type: Boolean,
        default: true
    },
    maxWarnings: {
        type: Number,
        default: 3
    }
})

const emit = defineEmits(['penalty-applied'])

const showAlert = ref(false)
const showLogout = ref(false)
const warningCount = ref(0)
const idleTimer = ref(null)
const warningTimer = ref(null)

let lastActivity = Date.now()

const resetIdleTimer = () => {
    lastActivity = Date.now()
    showAlert.value = false
    warningCount.value = 0
    
    if (warningTimer.value) {
        clearTimeout(warningTimer.value)
        warningTimer.value = null
    }
    
    startIdleTimer()
}

const startIdleTimer = () => {
    if (!props.enabled) return
    
    if (idleTimer.value) {
        clearTimeout(idleTimer.value)
    }
    
    idleTimer.value = setTimeout(() => {
        if (warningCount.value < props.maxWarnings) {
            showAlert.value = true
            warningCount.value++
            
            // Auto-hide alert after 10 seconds if no interaction
            warningTimer.value = setTimeout(() => {
                if (showAlert.value) {
                    showAlert.value = false
                    startIdleTimer() // Start next warning cycle
                }
            }, 10000)
        } else {
            // Max warnings reached, show logout
            showLogout.value = true
            applyPenalty()
        }
    }, props.timeout * 1000)
}

const applyPenalty = async () => {
    try {
        // Apply penalty via API
        const response = await fetch('/activities/penalty', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: window.Laravel.user.id,
                reason: 'Inactivity timeout exceeded',
                count: warningCount.value,
                metadata: {
                    timeout: props.timeout,
                    max_warnings: props.maxWarnings,
                    timestamp: new Date().toISOString()
                }
            })
        })
        
        if (response.ok) {
            // Emit penalty event to parent
            emit('penalty-applied', {
                reason: 'Inactivity timeout exceeded',
                count: warningCount.value,
                timestamp: new Date().toISOString()
            })
        }
    } catch (error) {
        console.error('Failed to apply penalty:', error)
    }
}

const forceLogout = () => {
    router.post('/logout', {}, {
        onSuccess: () => {
            showLogout.value = false
        }
    })
}

const handleActivity = () => {
    lastActivity = Date.now()
    if (showAlert.value) {
        resetIdleTimer()
    } else {
        startIdleTimer()
    }
}

onMounted(() => {
    if (props.enabled) {
        // Add event listeners for user activity
        document.addEventListener('mousemove', handleActivity)
        document.addEventListener('keydown', handleActivity)
        document.addEventListener('click', handleActivity)
        document.addEventListener('scroll', handleActivity)
        
        startIdleTimer()
    }
})

onUnmounted(() => {
    if (idleTimer.value) {
        clearTimeout(idleTimer.value)
    }
    if (warningTimer.value) {
        clearTimeout(warningTimer.value)
    }
    
    document.removeEventListener('mousemove', handleActivity)
    document.removeEventListener('keydown', handleActivity)
    document.removeEventListener('click', handleActivity)
    document.removeEventListener('scroll', handleActivity)
})
</script>

