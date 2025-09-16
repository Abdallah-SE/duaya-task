import { ref, computed } from 'vue'
import apiService from '../services/ApiService'

export function useDashboard() {
    const dashboardData = ref(null)
    const stats = ref(null)
    const activities = ref([])
    const loading = ref(false)
    const error = ref(null)

    const isAdmin = computed(() => apiService.userType === 'admin')
    const isEmployee = computed(() => apiService.userType === 'employee')

    /**
     * Load dashboard data
     */
    const loadDashboard = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await apiService.getDashboard()
            
            if (response.success) {
                dashboardData.value = response.data
                stats.value = response.data.stats
                activities.value = response.data.recentActivities || []
            } else {
                error.value = response.message
            }
        } catch (err) {
            error.value = err.message
        } finally {
            loading.value = false
        }
    }

    /**
     * Load statistics only
     */
    const loadStats = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await apiService.getStats()
            
            if (response.success) {
                stats.value = response.data
            } else {
                error.value = response.message
            }
        } catch (err) {
            error.value = err.message
        } finally {
            loading.value = false
        }
    }

    /**
     * Load activities
     */
    const loadActivities = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await apiService.getActivities()
            
            if (response.success) {
                activities.value = response.data
            } else {
                error.value = response.message
            }
        } catch (err) {
            error.value = err.message
        } finally {
            loading.value = false
        }
    }

    /**
     * Load users (admin only)
     */
    const loadUsers = async () => {
        if (!isAdmin.value) {
            throw new Error('Access denied. Admin privileges required.')
        }

        loading.value = true
        error.value = null

        try {
            const response = await apiService.getUsers()
            
            if (response.success) {
                return response.data
            } else {
                error.value = response.message
                return null
            }
        } catch (err) {
            error.value = err.message
            return null
        } finally {
            loading.value = false
        }
    }

    /**
     * Load employees (admin only)
     */
    const loadEmployees = async () => {
        if (!isAdmin.value) {
            throw new Error('Access denied. Admin privileges required.')
        }

        loading.value = true
        error.value = null

        try {
            const response = await apiService.getEmployees()
            
            if (response.success) {
                return response.data
            } else {
                error.value = response.message
                return null
            }
        } catch (err) {
            error.value = err.message
            return null
        } finally {
            loading.value = false
        }
    }

    /**
     * Load penalties
     */
    const loadPenalties = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await apiService.getPenalties()
            
            if (response.success) {
                return response.data
            } else {
                error.value = response.message
                return null
            }
        } catch (err) {
            error.value = err.message
            return null
        } finally {
            loading.value = false
        }
    }

    /**
     * Load settings
     */
    const loadSettings = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await apiService.getSettings()
            
            if (response.success) {
                return response.data
            } else {
                error.value = response.message
                return null
            }
        } catch (err) {
            error.value = err.message
            return null
        } finally {
            loading.value = false
        }
    }

    /**
     * Update settings
     */
    const updateSettings = async (settings) => {
        loading.value = true
        error.value = null

        try {
            const response = await apiService.updateSettings(settings)
            
            if (response.success) {
                return response.data
            } else {
                error.value = response.message
                return null
            }
        } catch (err) {
            error.value = err.message
            return null
        } finally {
            loading.value = false
        }
    }

    return {
        // State
        dashboardData: computed(() => dashboardData.value),
        stats: computed(() => stats.value),
        activities: computed(() => activities.value),
        loading: computed(() => loading.value),
        error: computed(() => error.value),
        isAdmin,
        isEmployee,

        // Methods
        loadDashboard,
        loadStats,
        loadActivities,
        loadUsers,
        loadEmployees,
        loadPenalties,
        loadSettings,
        updateSettings
    }
}