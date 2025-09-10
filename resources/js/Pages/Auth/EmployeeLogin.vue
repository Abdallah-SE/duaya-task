<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 via-indigo-900 to-blue-900 py-12 px-4 sm:px-6 lg:px-8">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, rgba(156, 146, 172, 0.1) 1px, transparent 0); background-size: 20px 20px;"></div>
        
        <div class="relative max-w-md w-full space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-4xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                    Employee Portal
                </h2>
                <p class="mt-2 text-center text-sm text-gray-300">
                    Access your personal activity dashboard
                </p>
            </div>
            
            <!-- Login Form Card -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 p-8">
                <form class="space-y-6" @submit.prevent="submit">
                    <div class="space-y-5">
                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-200 mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    v-model="form.email"
                                    :class="[
                                        'block w-full pl-10 pr-3 py-3 border rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white/10 backdrop-blur-sm text-white placeholder-gray-300',
                                        errors.email ? 'border-red-400 ring-red-400' : 'border-gray-600'
                                    ]"
                                    placeholder="Enter your employee email"
                                />
                            </div>
                            <div v-if="errors.email" class="mt-2 text-sm text-red-300 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ errors.email }}
                            </div>
                        </div>
                        
                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-200 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    required
                                    v-model="form.password"
                                    :class="[
                                        'block w-full pl-10 pr-12 py-3 border rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white/10 backdrop-blur-sm text-white placeholder-gray-300',
                                        errors.password ? 'border-red-400 ring-red-400' : 'border-gray-600'
                                    ]"
                                    placeholder="Enter your password"
                                />
                                <button
                                    type="button"
                                    @click="togglePasswordVisibility"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg v-if="showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                    </svg>
                                    <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div v-if="errors.password" class="mt-2 text-sm text-red-300 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ errors.password }}
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
                        >
                            <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="processing">Authenticating...</span>
                            <span v-else class="flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Employee Sign In
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Employee Test Credentials -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                <h3 class="text-sm font-semibold text-gray-200 mb-3 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Employee Test Account
                </h3>
                <div class="text-xs text-gray-300 space-y-3">
                    <div class="bg-white/5 rounded-lg p-3 border border-white/10">
                        <p class="font-semibold text-blue-300 mb-2">Employee Credentials</p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p><span class="text-gray-400">Email:</span> employee1@duaya.com</p>
                                <button 
                                    @click="copyToClipboard('employee1@duaya.com', 'employee-email')"
                                    :class="[
                                        'px-3 py-1 rounded text-xs font-medium transition-all duration-200',
                                        copiedStates['employee-email'] 
                                            ? 'bg-green-600 text-white' 
                                            : 'text-blue-400 hover:text-blue-300 hover:bg-blue-500/20'
                                    ]"
                                    :title="copiedStates['employee-email'] ? 'Copied!' : 'Copy email'"
                                >
                                    <span v-if="copiedStates['employee-email']" class="flex items-center space-x-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>{{ copiedStates['employee-email'] }}</span>
                                    </span>
                                    <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <p><span class="text-gray-400">Password:</span> password</p>
                                <button 
                                    @click="copyToClipboard('password', 'employee-password')"
                                    :class="[
                                        'px-3 py-1 rounded text-xs font-medium transition-all duration-200',
                                        copiedStates['employee-password'] 
                                            ? 'bg-green-600 text-white' 
                                            : 'text-blue-400 hover:text-blue-300 hover:bg-blue-500/20'
                                    ]"
                                    :title="copiedStates['employee-password'] ? 'Copied!' : 'Copy password'"
                                >
                                    <span v-if="copiedStates['employee-password']" class="flex items-center space-x-1">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>{{ copiedStates['employee-password'] }}</span>
                                    </span>
                                    <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                <h3 class="text-sm font-semibold text-gray-200 mb-3 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Employee Access
                </h3>
                <div class="text-xs text-gray-400 space-y-2">
                    <p>• Employee credentials required</p>
                    <p>• Your activities are monitored for productivity</p>
                    <p>• Contact HR for account assistance</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="text-center">
                <p class="text-sm text-gray-400">
                    Admin? 
                    <a href="/admin/login" class="text-red-400 hover:text-red-300 font-medium">Admin Login</a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
    email: '',
    password: '',
})

const processing = ref(false)
const errors = ref({})
const showPassword = ref(false)
const copiedStates = ref({})

const submit = () => {
    processing.value = true
    errors.value = {}
    
    router.post('/employee/login', form.value, {
        onFinish: () => {
            processing.value = false
        },
        onError: (error) => {
            errors.value = error
        }
    })
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value
}

const copyToClipboard = async (text, buttonId) => {
    try {
        await navigator.clipboard.writeText(text)
        showCopySuccess(buttonId)
        console.log('Copied to clipboard:', text)
    } catch (err) {
        console.error('Failed to copy: ', err)
        // Fallback for older browsers
        const textArea = document.createElement('textarea')
        textArea.value = text
        document.body.appendChild(textArea)
        textArea.focus()
        textArea.select()
        try {
            document.execCommand('copy')
            showCopySuccess(buttonId)
            console.log('Copied to clipboard (fallback):', text)
        } catch (fallbackErr) {
            console.error('Fallback copy failed: ', fallbackErr)
            showCopySuccess(buttonId, true)
        }
        document.body.removeChild(textArea)
    }
}

const showCopySuccess = (buttonId, isError = false) => {
    copiedStates.value[buttonId] = isError ? 'Failed' : 'Copied!'
    setTimeout(() => {
        copiedStates.value[buttonId] = false
    }, 2000)
}

onMounted(() => {
    // Focus on email input when component mounts
    const emailInput = document.getElementById('email')
    if (emailInput) {
        emailInput.focus()
    }
})
</script>
