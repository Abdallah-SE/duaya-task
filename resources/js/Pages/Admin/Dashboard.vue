<template>
    <AppLayout :user="user" :user-settings="userSettings">
        <div class="space-y-6">
            <!-- Header with Task Focus -->
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        User Activity Logs & Inactivity Monitoring
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Track CRUD operations, monitor idle states, manage penalties, and configure inactivity settings
                    </p>
                </div>
                <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                    <button @click="refreshData" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                    <Link :href="'/admin/settings'" 
                          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </Link>
                </div>
            </div>

            <!-- Task-Specific Stats Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Activity Logs -->
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Activity Logs</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.totalActivities }}</dd>
                                    <dd class="text-xs text-gray-400">{{ stats.todayActivities }} today</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CRUD Operations -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">CRUD Operations</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.crudOperations }}</dd>
                                    <dd class="text-xs text-gray-400">Create, Read, Update, Delete</dd>
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
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.totalIdleSessions }}</dd>
                                    <dd class="text-xs text-gray-400">{{ stats.activeIdleSessions }} active</dd>
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
                                    <dd class="text-lg font-medium text-gray-900">{{ stats.totalPenalties }}</dd>
                                    <dd class="text-xs text-gray-400">{{ stats.autoLogoutPenalties }} auto-logouts</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inactivity Monitoring Settings -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Inactivity Monitoring Settings</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Current configuration for idle timeout and penalty system</p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Idle Timeout</div>
                                    <div class="text-2xl font-bold text-indigo-600">
                                        {{ inactivitySettings.idle_timeout }}s
                                    </div>
                                    <div class="text-xs text-gray-500">Alert threshold</div>
                                </div>
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Warning Threshold</div>
                                    <div class="text-2xl font-bold text-yellow-600">
                                        {{ inactivitySettings.warning_threshold }}
                                    </div>
                                    <div class="text-xs text-gray-500">Warnings before logout</div>
                                </div>
                                <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Auto Logout</div>
                                    <div class="text-2xl font-bold" :class="inactivitySettings.auto_logout_enabled ? 'text-green-600' : 'text-red-600'">
                                        {{ inactivitySettings.auto_logout_enabled ? 'ON' : 'OFF' }}
                                    </div>
                                    <div class="text-xs text-gray-500">Automatic logout enabled</div>
                                </div>
                                <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="inactivitySettings.auto_logout_enabled ? 'bg-green-100' : 'bg-red-100'">
                                    <svg class="h-4 w-4" :class="inactivitySettings.auto_logout_enabled ? 'text-green-600' : 'text-red-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Monitoring</div>
                                    <div class="text-2xl font-bold" :class="inactivitySettings.monitoring_enabled ? 'text-green-600' : 'text-red-600'">
                                        {{ inactivitySettings.monitoring_enabled ? 'ON' : 'OFF' }}
                                    </div>
                                    <div class="text-xs text-gray-500">Activity monitoring enabled</div>
                                </div>
                                <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="inactivitySettings.monitoring_enabled ? 'bg-green-100' : 'bg-red-100'">
                                    <svg class="h-4 w-4" :class="inactivitySettings.monitoring_enabled ? 'text-green-600' : 'text-red-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CRUD Operations Breakdown -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">CRUD Operations Breakdown</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Distribution of Create, Read, Update, Delete operations across the system</p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="operation in crudBreakdown" :key="operation.operation_type" class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ operation.operation_type }}</div>
                                    <div class="text-2xl font-bold" :class="getOperationColor(operation.operation_type)">
                                        {{ operation.count }}
                                    </div>
                                </div>
                                <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="getOperationIconClass(operation.operation_type)">
                                    <svg class="h-4 w-4" :class="getOperationIconColor(operation.operation_type)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getOperationIconPath(operation.operation_type)" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activity Logs</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Filtered CRUD operations, login/logout events, and important actions with enhanced tracking</p>
                        </div>
                        <div class="flex space-x-2">
                            <select v-model="activityFilter" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all">All Activities</option>
                                <option value="high">High Priority</option>
                                <option value="medium">Medium Priority</option>
                                <option value="low">Low Priority</option>
                                <option value="crud">CRUD Operations</option>
                                <option value="auth">Authentication</option>
                                <option value="settings">Settings Changes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div v-for="group in filteredActivities" :key="`${group.user?.name}-${group.date}`" class="border border-gray-200 rounded-lg">
                        <!-- Group Header -->
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-600">
                                            {{ group.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ group.user?.name || 'Unknown User' }}
                                            <span v-if="group.user?.roles?.length" class="ml-2">
                                                <span v-for="role in group.user.roles" :key="role" 
                                                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                    {{ role }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <span v-if="group.user?.department">{{ group.user.department }}</span>
                                            <span v-if="group.user?.department && group.user?.job_title"> • </span>
                                            <span v-if="group.user?.job_title">{{ group.user.job_title }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ group.totalCount }} activities</div>
                                    <div class="text-xs text-gray-500">{{ formatDate(group.date) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Group Activities -->
                        <div class="divide-y divide-gray-200">
                            <div v-for="(activity, index) in group.activities.slice(0, 5)" :key="activity.id" 
                                 class="px-4 py-3 hover:bg-gray-50">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-6 w-6 rounded-full flex items-center justify-center" :class="getActivityIconClass(activity.action)">
                                                <svg class="h-3 w-3" :class="getActivityIconColor(activity.action)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIconPath(activity.action)" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center space-x-2">
                                                <div class="text-sm text-gray-900">
                                                    {{ activity.details?.description || formatActivityAction(activity.action) }}
                                                </div>
                                                <div v-if="activity.importance" 
                                                     :class="getImportanceBadgeClass(activity.importance)"
                                                     class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium">
                                                    {{ activity.importance.toUpperCase() }}
                                                </div>
                                            </div>
                                            <div v-if="activity.details?.target" class="text-xs text-gray-500 mt-1">
                                                Target: {{ activity.details.target }}
                                            </div>
                                            <div class="mt-1 flex items-center space-x-3 text-xs text-gray-400">
                                                <span>{{ activity.device }}</span>
                                                <span>{{ activity.browser }}</span>
                                                <span>{{ activity.ip_address }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">
                                            {{ formatTimeAgo(activity.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Show more indicator if there are more than 5 activities -->
                            <div v-if="group.activities.length > 5" class="px-4 py-2 bg-gray-50 text-center">
                                <span class="text-xs text-gray-500">
                                    +{{ group.activities.length - 5 }} more activities
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Penalties -->
            <div v-if="allPenalties.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Penalty System</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Auto-logout penalties applied due to inactivity (3rd idle warning)</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="penalty in allPenalties" :key="penalty.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ penalty.user?.name || 'Unknown User' }}
                                </div>
                                <div class="text-sm text-gray-500">
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

            <!-- Idle Session Statistics -->
            <div v-if="idleSessionStats.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Inactivity Tracking</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Users with most idle sessions - Alert → Warning → Auto Logout</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="stat in idleSessionStats" :key="stat.user_id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ stat.user?.name || 'Unknown User' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Sessions: {{ stat.session_count }} | 
                                    Total Duration: {{ formatDuration(stat.total_duration) }} | 
                                    Avg Duration: {{ formatDuration(stat.avg_duration) }}
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Employee Activity Statistics -->
            <div v-if="employeeActivityStats.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Employee Activity Monitoring</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Top employees by activity count - Track productivity and system usage</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="stat in employeeActivityStats" :key="stat.user?.name" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-600">
                                            {{ stat.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ stat.user?.name || 'Unknown User' }}
                                        <span v-if="stat.user?.employee" class="text-xs text-gray-500">
                                            ({{ stat.user.employee.job_title }})
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Total: {{ stat.activity_count }} activities
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ stat.today_activities }} today
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ stat.week_activities }} this week
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Activity Statistics -->
            <div v-if="activityStats.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Activity Logs Summary</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Most frequently performed CRUD operations and actions</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="stat in activityStats" :key="stat.action" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-3 w-3 rounded-full" :class="getActionColor(stat.action)"></div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ formatActivityAction(stat.action) }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ stat.count }} times
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    stats: Object,
    recentActivities: Array,
    crudBreakdown: Array,
    employeeActivityStats: Array,
    allPenalties: Array,
    idleSessionStats: Array,
    activityStats: Array,
    inactivitySettings: Object,
    greeting: String
})

const activityFilter = ref('all')

const refreshData = () => {
    router.reload()
}

// Filter activities based on selected filter
const filteredActivities = computed(() => {
    let activities = props.recentActivities
    
    // Apply filter
    if (activityFilter.value !== 'all') {
        activities = activities.filter(activity => {
            switch (activityFilter.value) {
                case 'high':
                    return activity.importance === 'high'
                case 'medium':
                    return activity.importance === 'medium'
                case 'low':
                    return activity.importance === 'low'
                case 'crud':
                    return ['create', 'read', 'update', 'delete'].some(op => 
                        activity.action.toLowerCase().includes(op)
                    )
                case 'auth':
                    return ['login', 'logout', 'admin_login', 'employee_login'].some(auth => 
                        activity.action.toLowerCase().includes(auth)
                    )
                case 'settings':
                    return ['settings', 'idle', 'timeout', 'role'].some(setting => 
                        activity.action.toLowerCase().includes(setting)
                    )
                default:
                    return true
            }
        })
    }
    
    // Group similar activities to reduce clutter
    return groupSimilarActivities(activities)
})

// Group similar activities by user and time period
const groupSimilarActivities = (activities) => {
    const grouped = []
    const groups = new Map()
    
    activities.forEach(activity => {
        const key = `${activity.user?.name || 'Unknown'}-${activity.created_at.split('T')[0]}`
        
        if (!groups.has(key)) {
            groups.set(key, {
                user: activity.user,
                date: activity.created_at.split('T')[0],
                activities: [],
                totalCount: 0
            })
        }
        
        const group = groups.get(key)
        group.activities.push(activity)
        group.totalCount++
    })
    
    // Convert groups to array and sort by most recent activity
    groups.forEach(group => {
        group.activities.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        grouped.push(group)
    })
    
    return grouped.sort((a, b) => new Date(b.activities[0].created_at) - new Date(a.activities[0].created_at))
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}

const formatTimeAgo = (dateString) => {
    const now = new Date()
    const date = new Date(dateString)
    const diffInSeconds = Math.floor((now - date) / 1000)
    
    if (diffInSeconds < 60) {
        return 'Just now'
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60)
        return `${minutes}m ago`
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600)
        return `${hours}h ago`
    } else {
        const days = Math.floor(diffInSeconds / 86400)
        return `${days}d ago`
    }
}

const formatDuration = (duration) => {
    if (!duration) return 'N/A'
    const hours = Math.floor(duration / 3600)
    const minutes = Math.floor((duration % 3600) / 60)
    const seconds = Math.floor(duration % 60)
    return `${hours}h ${minutes}m ${seconds}s`
}

// Get importance badge styling
const getImportanceBadgeClass = (importance) => {
    const classes = {
        'high': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-gray-100 text-gray-800'
    }
    return classes[importance] || 'bg-gray-100 text-gray-800'
}

// Helper functions for CRUD operations display
const getOperationColor = (operation) => {
    const colors = {
        'Create': 'text-green-600',
        'Read': 'text-blue-600',
        'Update': 'text-yellow-600',
        'Delete': 'text-red-600',
        'Other': 'text-gray-600',
    }
    return colors[operation] || 'text-gray-600'
}

const getOperationIconClass = (operation) => {
    const classes = {
        'Create': 'bg-green-100',
        'Read': 'bg-blue-100',
        'Update': 'bg-yellow-100',
        'Delete': 'bg-red-100',
        'Other': 'bg-gray-100',
    }
    return classes[operation] || 'bg-gray-100'
}

const getOperationIconColor = (operation) => {
    const colors = {
        'Create': 'text-green-600',
        'Read': 'text-blue-600',
        'Update': 'text-yellow-600',
        'Delete': 'text-red-600',
        'Other': 'text-gray-600',
    }
    return colors[operation] || 'text-gray-600'
}

const getOperationIconPath = (operation) => {
    const paths = {
        'Create': 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        'Read': 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        'Update': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'Delete': 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'Other': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    }
    return paths[operation] || 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
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
