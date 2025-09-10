<template>
    <AppLayout :user="user" :user-settings="userSettings">
        <div class="max-w-4xl mx-auto">
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Settings
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Configure your inactivity monitoring preferences
                    </p>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Inactivity Monitoring Settings
                    </h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Idle Timeout -->
                        <div>
                            <label for="idle_timeout" class="block text-sm font-medium text-gray-700">
                                Idle Timeout (seconds)
                            </label>
                            <div class="mt-1">
                                <select
                                    id="idle_timeout"
                                    v-model="form.idle_timeout"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                >
                                    <option value="5">5 seconds</option>
                                    <option value="10">10 seconds</option>
                                    <option value="30">30 seconds</option>
                                    <option value="60">1 minute</option>
                                    <option value="120">2 minutes</option>
                                    <option value="300">5 minutes</option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                How long to wait before showing the first inactivity alert
                            </p>
                        </div>

                        <!-- Max Warnings -->
                        <div>
                            <label for="max_idle_warnings" class="block text-sm font-medium text-gray-700">
                                Maximum Warnings
                            </label>
                            <div class="mt-1">
                                <select
                                    id="max_idle_warnings"
                                    v-model="form.max_idle_warnings"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                >
                                    <option value="1">1 warning</option>
                                    <option value="2">2 warnings</option>
                                    <option value="3">3 warnings</option>
                                    <option value="5">5 warnings</option>
                                    <option value="10">10 warnings</option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Number of warnings before automatic logout
                            </p>
                        </div>

                        <!-- Enable/Disable Monitoring -->
                        <div class="flex items-center">
                            <input
                                id="idle_monitoring_enabled"
                                v-model="form.idle_monitoring_enabled"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <label for="idle_monitoring_enabled" class="ml-2 block text-sm text-gray-900">
                                Enable inactivity monitoring
                            </label>
                        </div>
                        <p class="text-sm text-gray-500">
                            When disabled, you won't receive inactivity alerts or penalties
                        </p>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                <span v-if="processing">Saving...</span>
                                <span v-else>Save Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Penalties -->
            <div v-if="userPenalties.length > 0" class="mt-8 bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Your Penalties
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="penalty in userPenalties"
                            :key="penalty.id"
                            class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-md"
                        >
                            <div>
                                <p class="text-sm font-medium text-red-800">{{ penalty.reason }}</p>
                                <p class="text-sm text-red-600">Count: {{ penalty.count }}</p>
                            </div>
                            <div class="text-sm text-red-600">
                                {{ formatDate(penalty.penalty_date) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    userSettings: Object,
    userPenalties: Array,
})

const form = ref({
    idle_timeout: props.userSettings.idle_timeout,
    max_idle_warnings: props.userSettings.max_idle_warnings,
    idle_monitoring_enabled: props.userSettings.idle_monitoring_enabled,
})

const processing = ref(false)

const submit = () => {
    processing.value = true
    
    router.put('/settings', form.value, {
        onFinish: () => {
            processing.value = false
        }
    })
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}
</script>

