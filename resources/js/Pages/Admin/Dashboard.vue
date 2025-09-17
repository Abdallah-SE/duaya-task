<template>
    <AppLayout :user="user" :user-settings="userSettings" :is-idle-monitoring-enabled="isIdleMonitoringEnabled">
        <div class="space-y-6">
            <!-- Task Header -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Duaya Task</h1>
                        <h2 class="text-xl font-semibold text-indigo-600 mt-1">Activity Monitor</h2>
                        <p class="text-sm text-gray-500 mt-2">
                        Track CRUD operations, monitor idle states, manage penalties, and configure inactivity settings
                    </p>
                </div>
                    <div class="flex space-x-3">
                    <button @click="refreshData" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                    <Link :href="'/admin/settings'" 
                              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </Link>
                    </div>
                </div>
            </div>

            <!-- Core Task Statistics -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Activity Logs -->
                <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Activity Logs</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ stats.totalActivities }}</dd>
                                    <dd class="text-sm text-blue-600 font-medium">{{ stats.todayActivities }} today</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CRUD Operations -->
                <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-green-500">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">CRUD Operations</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ stats.crudOperations }}</dd>
                                    <dd class="text-sm text-green-600 font-medium">Create, Read, Update, Delete</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Idle Sessions -->
                <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-yellow-500">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Idle Sessions</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ stats.totalIdleSessions }}</dd>
                                    <dd class="text-sm text-yellow-600 font-medium">{{ stats.activeIdleSessions }} active</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penalties -->
                <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-red-500">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 19.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Penalties</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ stats.totalPenalties }}</dd>
                                    <dd class="text-sm text-red-600 font-medium">{{ stats.autoLogoutPenalties }} auto-logouts</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inactivity Monitoring Settings -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Inactivity Monitoring Settings</h3>
                    <p class="text-sm text-gray-500">Current configuration for idle timeout and penalty system</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600 mb-2">
                                        {{ inactivitySettings.idle_timeout }}s
                            </div>
                            <div class="text-sm font-medium text-gray-900">Idle Timeout</div>
                            <div class="text-xs text-gray-500">Alert threshold</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-600 mb-2">
                                        {{ inactivitySettings.warning_threshold }}
                            </div>
                            <div class="text-sm font-medium text-gray-900">Warning Threshold</div>
                            <div class="text-xs text-gray-500">Warnings before logout</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold mb-2" :class="inactivitySettings.auto_logout_enabled ? 'text-green-600' : 'text-red-600'">
                                        {{ inactivitySettings.auto_logout_enabled ? 'ON' : 'OFF' }}
                            </div>
                            <div class="text-sm font-medium text-gray-900">Auto Logout</div>
                            <div class="text-xs text-gray-500">Automatic logout enabled</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold mb-2" :class="inactivitySettings.monitoring_enabled ? 'text-green-600' : 'text-red-600'">
                                        {{ inactivitySettings.monitoring_enabled ? 'ON' : 'OFF' }}
                            </div>
                            <div class="text-sm font-medium text-gray-900">Monitoring</div>
                            <div class="text-xs text-gray-500">Activity monitoring enabled</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Summary -->
            <div v-if="recentActivities.pagination" class="bg-gradient-to-r from-indigo-500 to-purple-600 shadow rounded-lg text-white">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Activity Logs Summary</h3>
                            <p class="text-indigo-100 text-sm">Complete activity tracking overview</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold">{{ recentActivities.pagination.total }}</div>
                            <div class="text-indigo-200 text-sm">Total Records</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-semibold text-gray-900">Activity Logs</h3>
                                <div v-if="recentActivities.pagination" class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800">
                                        {{ recentActivities.pagination.total }} Total Records
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        ({{ recentActivities.pagination.from }}-{{ recentActivities.pagination.to }} shown)
                                    </span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                Track all CRUD operations and important actions
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select v-model="perPage" @change="loadActivities" 
                                    class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="5">5 per page</option>
                                <option value="10">10 per page</option>
                                <option value="20">20 per page</option>
                                <option value="50">50 per page</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Search Only -->
                    <div class="mt-4">
                        <div class="relative max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg v-if="!loading" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <svg v-else class="animate-spin h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <input v-model="searchQuery" 
                                   @input="debouncedSearch"
                                   type="text" 
                                   placeholder="Search activities..."
                                   :disabled="loading"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Enhanced Loading State -->
                    <div v-if="loading" class="py-12">
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <!-- Spinner -->
                            <div class="relative">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-200"></div>
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-600 border-t-transparent absolute top-0 left-0"></div>
                            </div>
                            
                            <!-- Loading Text -->
                            <div class="text-center">
                                <p class="text-lg font-medium text-gray-900">Loading Activities</p>
                                <p class="text-sm text-gray-500">Please wait while we fetch the data...</p>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-64 bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activities List -->
                    <div v-else class="transition-all duration-300 ease-in-out">
                        <!-- Results Count -->
                        <div v-if="recentActivities.pagination" class="mb-4 bg-gray-50 px-4 py-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="text-sm text-gray-600">
                                        <span v-if="searchQuery">
                                            <span class="font-semibold text-indigo-600">{{ recentActivities.pagination.total }}</span> result(s) found for "<span class="font-medium">{{ searchQuery }}</span>"
                                        </span>
                                        <span v-else>
                                            Showing <span class="font-semibold text-gray-900">{{ recentActivities.pagination.from }}-{{ recentActivities.pagination.to }}</span> of <span class="font-semibold text-indigo-600">{{ recentActivities.pagination.total }}</span> total activities
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Page {{ recentActivities.pagination.current_page }} of {{ recentActivities.pagination.last_page }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-for="activity in recentActivities.data" :key="activity.id" 
                                 class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="h-2 w-2 rounded-full" :class="getActivityDotColor(activity.action)"></div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ activity.details?.description || formatActivityAction(activity.action) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ activity.user?.name || 'Unknown User' }} • {{ activity.device }} • {{ activity.ip_address }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ formatTimeAgo(activity.created_at) }}
                                </div>
                            </div>
                            
                            <!-- Empty State -->
                            <div v-if="recentActivities.data.length === 0" class="text-center py-8">
                                <div class="text-gray-500 text-sm">
                                    <span v-if="searchQuery">No activities found matching "{{ searchQuery }}"</span>
                                    <span v-else>No activities found</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enhanced Pagination -->
                    <div v-if="recentActivities.pagination && recentActivities.pagination.last_page > 1" 
                         class="mt-6 bg-gray-50 px-4 py-3 rounded-lg">
                        <!-- Mobile Pagination -->
                        <div class="flex items-center justify-between sm:hidden">
                            <div class="flex items-center space-x-2">
                                <button @click="goToPage(1)" 
                                        :disabled="recentActivities.pagination.current_page <= 1 || loading"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <span v-if="loading && currentPage === 1" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span v-else>First</span>
                                </button>
                                <button @click="goToPage(recentActivities.pagination.current_page - 1)" 
                                        :disabled="recentActivities.pagination.current_page <= 1 || loading"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <span v-if="loading && currentPage === recentActivities.pagination.current_page - 1" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span v-else>Previous</span>
                                </button>
                            </div>
                            
                            <div class="flex items-center space-x-1">
                                <span class="text-sm text-gray-700">
                                    <span v-if="loading" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading...
                                    </span>
                                    <span v-else>Page {{ recentActivities.pagination.current_page }} of {{ recentActivities.pagination.last_page }}</span>
                                </span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button @click="goToPage(recentActivities.pagination.current_page + 1)" 
                                        :disabled="recentActivities.pagination.current_page >= recentActivities.pagination.last_page || loading"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <span v-if="loading && currentPage === recentActivities.pagination.current_page + 1" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span v-else>Next</span>
                                </button>
                                <button @click="goToPage(recentActivities.pagination.last_page)" 
                                        :disabled="recentActivities.pagination.current_page >= recentActivities.pagination.last_page || loading"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <span v-if="loading && currentPage === recentActivities.pagination.last_page" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span v-else>Last</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Desktop Pagination -->
                        <div class="hidden sm:flex sm:items-center sm:justify-between">
                            <div class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ recentActivities.pagination.from }}</span>
                                to
                                <span class="font-medium">{{ recentActivities.pagination.to }}</span>
                                of
                                <span class="font-medium">{{ recentActivities.pagination.total }}</span>
                                results
                            </div>
                            
                            <nav class="flex items-center space-x-1" aria-label="Pagination">
                                <!-- Previous Page -->
                                <button @click="goToPage(recentActivities.pagination.current_page - 1)" 
                                        :disabled="recentActivities.pagination.current_page <= 1 || loading"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <span v-if="loading && currentPage === recentActivities.pagination.current_page - 1" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading...
                                    </span>
                                    <span v-else>Previous</span>
                                </button>
                                
                                <!-- Page Numbers -->
                                <template v-for="page in getVisiblePages()" :key="page">
                                    <button v-if="page !== '...'" 
                                            @click="goToPage(page)"
                                            :disabled="loading"
                                            :class="[
                                                'px-4 py-2 text-sm font-medium border transition-all duration-200',
                                                page === recentActivities.pagination.current_page
                                                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed'
                                            ]">
                                        <span v-if="loading && currentPage === page" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                        <span v-else>{{ page }}</span>
                                    </button>
                                    <span v-else class="px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300">
                                        ...
                                    </span>
                                </template>
                                
                                <!-- Next Page -->
                                <button @click="goToPage(recentActivities.pagination.current_page + 1)" 
                                        :disabled="recentActivities.pagination.current_page >= recentActivities.pagination.last_page || loading"
                                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <span v-if="loading && currentPage === recentActivities.pagination.current_page + 1" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading...
                                    </span>
                                    <span v-else>Next</span>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Penalty System -->
            <div v-if="allPenalties.length > 0" class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Penalty System</h3>
                    <p class="text-sm text-gray-500">Auto-logout penalties applied due to inactivity (3rd idle warning)</p>
                </div>
                <div class="divide-y divide-gray-200">
                    <div v-for="penalty in allPenalties.slice(0, 5)" :key="penalty.id" class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ penalty.user?.name || 'Unknown User' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ penalty.reason }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-red-600">Count: {{ penalty.count }}</div>
                                <div class="text-xs text-gray-500">{{ formatDate(penalty.date) }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-if="allPenalties.length > 5" class="p-4 text-center">
                        <span class="text-xs text-gray-500">
                            +{{ allPenalties.length - 5 }} more penalties
                        </span>
            </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    isIdleMonitoringEnabled: Boolean,
    stats: Object,
    recentActivities: Object, // Changed from Array to Object to support pagination
    allPenalties: Array,
    inactivitySettings: Object
})

// Reactive data for pagination and search
const loading = ref(false)
const perPage = ref(10)
const currentPage = ref(1)
const searchQuery = ref('')
const searchTimeout = ref(null)

// Initialize pagination data
onMounted(() => {
    if (props.recentActivities && props.recentActivities.pagination) {
        currentPage.value = props.recentActivities.pagination.current_page
        perPage.value = props.recentActivities.pagination.per_page
    }
})

const refreshData = () => {
    router.reload()
}

const loadActivities = () => {
    loading.value = true
    const params = {
        page: currentPage.value,
        per_page: perPage.value
    }
    
    // Add search if it has value
    if (searchQuery.value) params.search = searchQuery.value
    
    router.get('/admin/dashboard', params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false
        }
    })
}

const goToPage = (page) => {
    if (page < 1 || page > props.recentActivities.pagination.last_page) return
    
    currentPage.value = page
    loadActivities()
}

const debouncedSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
    }
    
    searchTimeout.value = setTimeout(() => {
        currentPage.value = 1 // Reset to first page when searching
        loadActivities()
    }, 500) // 500ms delay
}

const getVisiblePages = () => {
    const current = props.recentActivities.pagination.current_page
    const last = props.recentActivities.pagination.last_page
    const pages = []
    
    if (last <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= last; i++) {
            pages.push(i)
        }
    } else {
        // Show first page
        pages.push(1)
        
        if (current > 4) {
            pages.push('...')
        }
        
        // Show pages around current page
        const start = Math.max(2, current - 1)
        const end = Math.min(last - 1, current + 1)
        
        for (let i = start; i <= end; i++) {
            if (i !== 1 && i !== last) {
                pages.push(i)
            }
        }
        
        if (current < last - 3) {
            pages.push('...')
        }
        
        // Show last page
        if (last > 1) {
            pages.push(last)
        }
    }
    
    return pages
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

// Helper functions for activity display
const getActivityDotColor = (action) => {
    const colors = {
        'create': 'bg-green-500',
        'read': 'bg-blue-500',
        'update': 'bg-yellow-500',
        'delete': 'bg-red-500',
        'login': 'bg-indigo-500',
        'logout': 'bg-purple-500',
        'view_admin_settings': 'bg-gray-500',
        'update_global_idle_settings': 'bg-orange-500',
        'toggle_role_monitoring': 'bg-pink-500',
    }
    return colors[action] || 'bg-gray-500'
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
</script>
