<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Duaya Task - Activity Monitor
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Sign in to your account to start monitoring
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            v-model="form.email"
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Email address"
                        />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            v-model="form.password"
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Password"
                        />
                    </div>
                </div>

                <div v-if="errors.email" class="text-red-600 text-sm">
                    {{ errors.email }}
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        <span v-if="processing">Signing in...</span>
                        <span v-else>Sign in</span>
                    </button>
                </div>

                <!-- Test Users Info -->
                <div class="mt-6 p-4 bg-blue-50 rounded-md">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">Test Users:</h3>
                    <div class="text-xs text-blue-700 space-y-1">
                        <div><strong>Admin:</strong> admin@duaya.com / password</div>
                        <div><strong>Manager:</strong> manager@duaya.com / password</div>
                        <div><strong>Employee 1:</strong> employee1@duaya.com / password</div>
                        <div><strong>Employee 2:</strong> employee2@duaya.com / password</div>
                        <div><strong>Employee 3:</strong> employee3@duaya.com / password</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
    email: '',
    password: '',
})

const processing = ref(false)
const errors = ref({})

const submit = () => {
    processing.value = true
    errors.value = {}
    
    router.post('/login', form.value, {
        onFinish: () => {
            processing.value = false
        },
        onError: (error) => {
            errors.value = error
        }
    })
}
</script>

