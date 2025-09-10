<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4 sm:px-6 lg:px-8">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, rgba(156, 146, 172, 0.1) 1px, transparent 0); background-size: 20px 20px;"></div>
        
        <div class="relative max-w-4xl w-full space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-4xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
                    Activity Monitor
                </h2>
                <p class="mt-2 text-center text-sm text-gray-300">
                    Choose your access portal
                </p>
            </div>
            
            <!-- Portal Selection Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Admin Portal Card -->
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 p-8 hover:bg-white/15 transition-all duration-300 group cursor-pointer" @click="goToAdminLogin">
                    <div class="text-center">
                        <div class="mx-auto h-20 w-20 bg-gradient-to-r from-red-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                        <h3 class="mt-6 text-2xl font-bold text-white">Admin Portal</h3>
                        <p class="mt-2 text-sm text-gray-300">
                            Full system access and management
                        </p>
                        <div class="mt-4 space-y-2 text-xs text-gray-400">
                            <p>• Monitor all user activities</p>
                            <p>• Manage system settings</p>
                            <p>• View comprehensive reports</p>
                            <p>• Administrative privileges</p>
                            </div>
                        <div class="mt-6">
                            <button class="w-full bg-gradient-to-r from-red-600 to-purple-600 hover:from-red-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                                Access Admin Portal
                            </button>
                        </div>
                            </div>
                        </div>
                        
                <!-- Employee Portal Card -->
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 p-8 hover:bg-white/15 transition-all duration-300 group cursor-pointer" @click="goToEmployeeLogin">
                    <div class="text-center">
                        <div class="mx-auto h-20 w-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                        <h3 class="mt-6 text-2xl font-bold text-white">Employee Portal</h3>
                        <p class="mt-2 text-sm text-gray-300">
                            Personal activity dashboard
                        </p>
                        <div class="mt-4 space-y-2 text-xs text-gray-400">
                            <p>• View your activity history</p>
                            <p>• Track personal performance</p>
                            <p>• Monitor idle time</p>
                            <p>• Employee self-service</p>
                        </div>
                        <div class="mt-6">
                            <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                                Access Employee Portal
                                </button>
                            </div>
                            </div>
                        </div>
                    </div>

            <!-- Test Credentials -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                <h3 class="text-sm font-semibold text-gray-200 mb-3 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Test Accounts
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-300">
                    <div class="bg-white/5 rounded-lg p-3 border border-white/10">
                        <p class="font-semibold text-red-300 mb-2">Admin Account</p>
                        <div class="space-y-1">
                            <p><span class="text-gray-400">Email:</span> admin@duaya.com</p>
                            <p><span class="text-gray-400">Password:</span> password</p>
                            <p class="text-xs text-gray-500 mt-2">Click "Admin Portal" above to login</p>
                        </div>
                    </div>
                    <div class="bg-white/5 rounded-lg p-3 border border-white/10">
                        <p class="font-semibold text-blue-300 mb-2">Employee Account</p>
                        <div class="space-y-1">
                            <p><span class="text-gray-400">Email:</span> employee1@duaya.com</p>
                            <p><span class="text-gray-400">Password:</span> password</p>
                            <p class="text-xs text-gray-500 mt-2">Click "Employee Portal" above to login</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                <h3 class="text-sm font-semibold text-gray-200 mb-3 flex items-center">
                    <svg class="h-4 w-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Secure Access
                </h3>
                <div class="text-xs text-gray-400 space-y-2">
                    <p>• All login attempts are monitored and logged</p>
                    <p>• Session activity is tracked for security</p>
                    <p>• Role-based access control enforced</p>
                    <p>• Contact your administrator for access credentials</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const goToAdminLogin = () => {
    router.visit('/admin/login')
}

const goToEmployeeLogin = () => {
    router.visit('/employee/login')
}
</script>