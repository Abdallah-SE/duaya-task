import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

export function useAuth() {
    // Get user from Inertia's shared data
    const user = computed(() => window.$page?.props?.auth?.user || null)
    const loading = ref(false)
    const error = ref(null)

    const isAuthenticated = computed(() => !!user.value)
    const isAdmin = computed(() => user.value?.roles?.includes('admin'))
    const isEmployee = computed(() => user.value?.roles?.includes('employee'))

    /**
     * Login as admin using Inertia
     */
    const loginAdmin = (credentials) => {
        loading.value = true
        error.value = null

        router.post('/admin/login', credentials, {
            onFinish: () => {
                loading.value = false
            },
            onError: (errors) => {
                error.value = errors.email || 'Login failed'
            }
        })
    }

    /**
     * Login as employee using Inertia
     */
    const loginEmployee = (credentials) => {
        loading.value = true
        error.value = null

        router.post('/employee/login', credentials, {
            onFinish: () => {
                loading.value = false
            },
            onError: (errors) => {
                error.value = errors.email || 'Login failed'
            }
        })
    }

    /**
     * Login with role selection using Inertia
     */
    const login = (credentials) => {
        loading.value = true
        error.value = null

        router.post('/login', credentials, {
            onFinish: () => {
                loading.value = false
            },
            onError: (errors) => {
                error.value = errors.email || 'Login failed'
            }
        })
    }

    /**
     * Logout user using Inertia
     */
    const logout = () => {
        loading.value = true

        router.post('/logout', {}, {
            onFinish: () => {
                loading.value = false
            }
        })
    }

    /**
     * Check if user has specific role
     */
    const hasRole = (role) => {
        return user.value?.roles?.includes(role)
    }

    /**
     * Check if user has specific permission
     */
    const hasPermission = (permission) => {
        return user.value?.permissions?.includes(permission)
    }

    return {
        // State
        user,
        loading: computed(() => loading.value),
        error: computed(() => error.value),
        isAuthenticated,
        isAdmin,
        isEmployee,

        // Methods
        login,
        loginAdmin,
        loginEmployee,
        logout,
        hasRole,
        hasPermission
    }
}
