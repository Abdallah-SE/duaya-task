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
                <div class="mt-4 flex md:mt-0 md:ml-4">
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
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Activities -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Activities</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.myActivities }}</dd>
                                    <dd class="text-xs text-gray-500">All time</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Activities -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Today's Activities</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.todayActivities }}</dd>
                                    <dd class="text-xs text-gray-500">Active today</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penalties -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Penalties</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.myPenalties }}</dd>
                                    <dd class="text-xs text-gray-500">Total penalties</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Idle Sessions -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Idle Sessions</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.myIdleSessions }}</dd>
                                    <dd class="text-xs text-gray-500">Total sessions</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Performance Metrics -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Weekly Activities -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">This Week</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.thisWeekActivities }}</dd>
                                    <dd class="text-xs text-gray-500">Activities</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Activities -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.thisMonthActivities }}</dd>
                                    <dd class="text-xs text-gray-500">Activities</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Idle Time -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Idle Time</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ formatDuration(stats.totalIdleTime) }}</dd>
                                    <dd class="text-xs text-gray-500">All time</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Breakdown Chart -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Activity Breakdown</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Your activities categorized by action type</p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="space-y-3">
                        <div v-for="breakdown in activityBreakdown" :key="breakdown.action" class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-3 w-3 rounded-full" :class="getActionColor(breakdown.action)"></div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 capitalize">
                                        {{ breakdown.action.replace(/_/g, ' ') }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ breakdown.count }}</div>
                                <div class="ml-2 text-xs text-gray-500">times</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Recent Activities -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">My Recent Activities</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Your recent system activities and CRUD operations</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="activity in myActivities" :key="activity.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="getActivityIconClass(activity.action)">
                                        <svg class="h-4 w-4" :class="getActivityIconColor(activity.action)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIconPath(activity.action)" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ formatActivityAction(activity.action) }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ activity.subject_type ? formatSubjectType(activity.subject_type) : 'System Action' }}
                                        <span v-if="activity.subject_id"> • ID: {{ activity.subject_id }}</span>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ activity.device }} • {{ activity.browser }} • {{ activity.ip_address }}
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
    activityBreakdown: Array,
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

// Helper functions for activity display
const getActionColor = (action) => {
    const colors = {
        'create': 'bg-green-400',
        'read': 'bg-blue-400',
        'update': 'bg-yellow-400',
        'delete': 'bg-red-400',
        'login': 'bg-indigo-400',
        'logout': 'bg-purple-400',
        'view_admin_settings': 'bg-gray-400',
        'update_global_idle_settings': 'bg-orange-400',
        'toggle_role_monitoring': 'bg-pink-400',
    }
    return colors[action] || 'bg-gray-400'
}

const getActivityIconClass = (action) => {
    const classes = {
        'create': 'bg-green-100',
        'read': 'bg-blue-100',
        'update': 'bg-yellow-100',
        'delete': 'bg-red-100',
        'login': 'bg-indigo-100',
        'logout': 'bg-purple-100',
        'view_admin_settings': 'bg-gray-100',
        'update_global_idle_settings': 'bg-orange-100',
        'toggle_role_monitoring': 'bg-pink-100',
    }
    return classes[action] || 'bg-gray-100'
}

const getActivityIconColor = (action) => {
    const colors = {
        'create': 'text-green-600',
        'read': 'text-blue-600',
        'update': 'text-yellow-600',
        'delete': 'text-red-600',
        'login': 'text-indigo-600',
        'logout': 'text-purple-600',
        'view_admin_settings': 'text-gray-600',
        'update_global_idle_settings': 'text-orange-600',
        'toggle_role_monitoring': 'text-pink-600',
    }
    return colors[action] || 'text-gray-600'
}

const getActivityIconPath = (action) => {
    const paths = {
        'create': 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        'read': 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        'update': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'delete': 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'login': 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
        'logout': 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
        'view_admin_settings': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'update_global_idle_settings': 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4',
        'toggle_role_monitoring': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    }
    return paths[action] || 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
}

const formatActivityAction = (action) => {
    const actionMap = {
        'create': 'Created',
        'read': 'Viewed',
        'update': 'Updated',
        'delete': 'Deleted',
        'login': 'Logged In',
        'logout': 'Logged Out',
        'view_admin_settings': 'Viewed Admin Settings',
        'update_global_idle_settings': 'Updated Global Idle Settings',
        'update_idle_timeout': 'Updated Idle Timeout',
        'update_role_idle_settings': 'Updated Role Idle Settings',
        'toggle_role_monitoring': 'Toggled Role Monitoring',
        'reset_idle_settings_to_defaults': 'Reset Settings to Defaults',
    }
    return actionMap[action] || action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatSubjectType = (subjectType) => {
    if (!subjectType) return 'System'
    return subjectType.split('\\').pop().replace(/([A-Z])/g, ' $1').trim()
}
</script>
