<template>
    <AppLayout :user="$page.props.auth.user" :user-settings="$page.props.auth.userSettings">
        <div class="space-y-6">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Activity Logs
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Track all user activities in the system
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button @click="refreshData" 
                            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Activities List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">All Activities</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Complete log of all user activities</p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="activity in activities.data" :key="activity.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-600">
                                            {{ activity.user ? activity.user.name.charAt(0).toUpperCase() : '?' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ activity.user ? activity.user.name : 'System' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ activity.description }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <span v-if="activity.event" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                            {{ activity.event }}
                                        </span>
                                        <span v-if="activity.subject_type" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            {{ activity.subject_type }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ formatDate(activity.created_at) }}
                            </div>
                        </div>
                    </li>
                </ul>
                
                <!-- Pagination -->
                <div v-if="activities.links" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link v-if="activities.prev_page_url" :href="activities.prev_page_url" 
                              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </Link>
                        <Link v-if="activities.next_page_url" :href="activities.next_page_url" 
                              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ activities.from }}</span>
                                to
                                <span class="font-medium">{{ activities.to }}</span>
                                of
                                <span class="font-medium">{{ activities.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <Link v-for="link in activities.links" :key="link.label" :href="link.url" 
                                      v-html="link.label"
                                      :class="[
                                          'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                          link.url ? 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' : 'bg-gray-100 border-gray-300 text-gray-300 cursor-not-allowed',
                                          link.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : ''
                                      ]">
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    activities: Object
})

const refreshData = () => {
    router.reload()
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}
</script>
