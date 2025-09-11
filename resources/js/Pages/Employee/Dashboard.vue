<template>
    <AppLayout 
        :user="user" 
        :user-settings="userSettings"
        :initial-settings="initialSettings"
        :can-control-idle-monitoring="canControlIdleMonitoring"
        :is-idle-monitoring-enabled="isIdleMonitoringEnabled"
    >
        <div class="space-y-6">
            <!-- Header with Greeting -->
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        {{ greeting }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Welcome to your personal dashboard - Track your activities and performance
                    </p>
                    <div v-if="user.employee" class="mt-2 text-sm text-gray-600">
                        <span class="font-medium">{{ user.employee.job_title }}</span> • 
                        <span>{{ user.employee.department }}</span>
                    </div>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <Link href="/employee/users" 
                          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        Manage Employees
                    </Link>
                    <button @click="refreshData" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Employee Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">My Activities</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.myActivities }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">My Penalties</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.myPenalties }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Idle Sessions</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.myIdleSessions }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Recent Activities -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">My Recent Activities</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Your recent system activities</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="activity in myActivities" :key="activity.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ activity.action }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ activity.device }} • {{ activity.browser }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ formatDate(activity.created_at) }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- My Penalties -->
            <div v-if="myPenalties.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">My Penalties</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Penalties applied to your account</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="penalty in myPenalties" :key="penalty.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ penalty.reason }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Count: {{ penalty.count }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ formatDate(penalty.date) }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- My Idle Sessions -->
            <div v-if="myIdleSessions.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">My Idle Sessions</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Your recent idle time sessions</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="session in myIdleSessions" :key="session.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    Session {{ session.id }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Duration: {{ formatDuration(session.duration) }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ formatDate(session.created_at) }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    initialSettings: Object,
    canControlIdleMonitoring: Boolean,
    isIdleMonitoringEnabled: Boolean,
    stats: Object,
    myActivities: Array,
    myPenalties: Array,
    myIdleSessions: Array,
    greeting: String
})

const refreshData = () => {
    router.reload()
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}

const formatDuration = (duration) => {
    if (!duration) return 'N/A'
    const hours = Math.floor(duration / 3600)
    const minutes = Math.floor((duration % 3600) / 60)
    const seconds = duration % 60
    return `${hours}h ${minutes}m ${seconds}s`
}
</script>
