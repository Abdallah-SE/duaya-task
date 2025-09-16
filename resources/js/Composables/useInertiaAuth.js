import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

export function useInertiaAuth() {
    const page = usePage()
    
    // Get user from Inertia's shared data
    const user = computed(() => page.props.auth?.user || null)
    const loading = computed(() => page.props.processing || false)
    const errors = computed(() => page.props.errors || {})
    
    const isAuthenticated = computed(() => !!user.value)
    const isAdmin = computed(() => user.value?.role_names?.includes('admin'))
    const isEmployee = computed(() => user.value?.role_names?.includes('employee'))

    /**
     * Login as admin using Inertia
     */
    const loginAdmin = (credentials, options = {}) => {
        return router.post('/admin/login', credentials, {
            onError: (errors) => {
                console.error('Admin login error:', errors)
            },
            ...options
        })
    }

    /**
     * Login as employee using Inertia
     */
    const loginEmployee = (credentials, options = {}) => {
        return router.post('/employee/login', credentials, {
            onError: (errors) => {
                console.error('Employee login error:', errors)
            },
            ...options
        })
    }

    /**
     * Login with role selection using Inertia
     */
    const login = (credentials, options = {}) => {
        return router.post('/login', credentials, {
            onError: (errors) => {
                console.error('Login error:', errors)
            },
            ...options
        })
    }


    /**
     * Logout admin user using Inertia
     */
    const logoutAdmin = (options = {}) => {
        return router.post('/admin/logout', {}, {
            onSuccess: () => {
                // Admin will be redirected to admin login
            },
            onError: (errors) => {
                console.error('Admin logout error:', errors)
            },
            ...options
        })
    }

    /**
     * Logout employee user using Inertia
     */
    const logoutEmployee = (options = {}) => {
        return router.post('/employee/logout', {}, {
            onSuccess: () => {
                // Employee will be redirected to employee login
            },
            onError: (errors) => {
                console.error('Employee logout error:', errors)
            },
            ...options
        })
    }

    /**
     * Smart logout - automatically determines which logout method to use
     */
    const smartLogout = (options = {}) => {
        if (isAdmin.value) {
            return logoutAdmin(options)
        } else if (isEmployee.value) {
            return logoutEmployee(options)
        } else {
            // Fallback to admin logout if no specific role is detected
            return logoutAdmin(options)
        }
    }

    /**
     * Check if user has specific role
     */
    const hasRole = (role) => {
        return user.value?.role_names?.includes(role)
    }

    /**
     * Check if user has specific permission
     */
    const hasPermission = (permission) => {
        return user.value?.permissions?.includes(permission)
    }

    /**
     * Get user's roles
     */
    const getUserRoles = () => {
        return user.value?.roles || []
    }

    /**
     * Get user's permissions
     */
    const getUserPermissions = () => {
        return user.value?.permissions || []
    }

    return {
        // State
        user,
        loading,
        errors,
        isAuthenticated,
        isAdmin,
        isEmployee,

        // Methods
        login,
        loginAdmin,
        loginEmployee,
        logoutAdmin,
        logoutEmployee,
        smartLogout,
        hasRole,
        hasPermission,
        getUserRoles,
        getUserPermissions
    }
}
