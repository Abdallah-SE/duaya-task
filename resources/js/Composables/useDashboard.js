import { ref, computed, onMounted, onUnmounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

export function useDashboard() {
  // State
  const dashboardStats = ref({
    totalUsers: 0,
    activeUsers: 0,
    idleSessions: 0,
    penalties: 0,
    recentActivities: 0,
    totalActivities: 0
  })
  
  const analyticsData = ref({
    activities: [],
    departments: [],
    roles: [],
    timeSeries: []
  })
  
  const statsLoading = ref(false)
  const analyticsLoading = ref(false)
  
  // Abort controllers for API calls
  const statsAbortController = ref(null)
  const analyticsAbortController = ref(null)
  
  // Component state tracking
  const isMounted = ref(false)

  // Computed
  const statsPercentage = computed(() => ({
    activeUsers: dashboardStats.value.totalUsers > 0 
      ? Math.round((dashboardStats.value.activeUsers / dashboardStats.value.totalUsers) * 100)
      : 0,
    idleSessions: dashboardStats.value.totalUsers > 0
      ? Math.round((dashboardStats.value.idleSessions / dashboardStats.value.totalUsers) * 100)
      : 0
  }))

  // Helper function to cancel all pending requests
  const cancelAllRequests = () => {
    if (statsAbortController.value) {
      statsAbortController.value.abort()
      statsAbortController.value = null
    }
    if (analyticsAbortController.value) {
      analyticsAbortController.value.abort()
      analyticsAbortController.value = null
    }
  }

  // Methods
  const fetchStats = async () => {
    // Only proceed if component is still mounted
    if (!isMounted.value) {
      return
    }

    // Cancel any existing stats request
    if (statsAbortController.value) {
      statsAbortController.value.abort()
    }

    statsLoading.value = true
    statsAbortController.value = new AbortController()

    try {
      const response = await fetch('/api/dashboard/stats', {
        signal: statsAbortController.value.signal
      })
      
      // Check if component is still mounted after fetch
      if (!isMounted.value) {
        return
      }

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      dashboardStats.value = data
    } catch (error) {
      // Don't process errors if component is unmounted or request was aborted
      if (!isMounted.value || error.name === 'AbortError') {
        return
      }
      console.error('Error fetching stats:', error)
    } finally {
      if (isMounted.value) {
        statsLoading.value = false
      }
      statsAbortController.value = null
    }
  }

  const fetchAnalytics = async () => {
    // Only proceed if component is still mounted
    if (!isMounted.value) {
      return
    }

    // Cancel any existing analytics request
    if (analyticsAbortController.value) {
      analyticsAbortController.value.abort()
    }

    analyticsLoading.value = true
    analyticsAbortController.value = new AbortController()

    try {
      const response = await fetch('/api/dashboard/analytics', {
        signal: analyticsAbortController.value.signal
      })
      
      // Check if component is still mounted after fetch
      if (!isMounted.value) {
        return
      }

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      analyticsData.value = data
    } catch (error) {
      // Don't process errors if component is unmounted or request was aborted
      if (!isMounted.value || error.name === 'AbortError') {
        return
      }
      console.error('Error fetching analytics:', error)
    } finally {
      if (isMounted.value) {
        analyticsLoading.value = false
      }
      analyticsAbortController.value = null
    }
  }

  const refreshStats = async () => {
    // Only proceed if component is still mounted
    if (!isMounted.value) {
      return
    }

    await Promise.all([
      fetchStats(),
      fetchAnalytics()
    ])
  }

  const updateStats = (newStats) => {
    if (isMounted.value) {
      dashboardStats.value = { ...dashboardStats.value, ...newStats }
    }
  }

  const resetStats = () => {
    if (isMounted.value) {
      dashboardStats.value = {
        totalUsers: 0,
        activeUsers: 0,
        idleSessions: 0,
        penalties: 0,
        recentActivities: 0,
        totalActivities: 0
      }
    }
  }

  // Cleanup function
  const cleanup = () => {
    cancelAllRequests()
    isMounted.value = false
  }

  // Lifecycle
  onMounted(() => {
    isMounted.value = true
    refreshStats()
  })

  onBeforeUnmount(() => {
    cleanup()
  })

  onUnmounted(() => {
    cleanup()
  })

  return {
    // State
    dashboardStats,
    analyticsData,
    statsLoading,
    analyticsLoading,
    
    // Computed
    statsPercentage,
    
    // Methods
    fetchStats,
    fetchAnalytics,
    refreshStats,
    updateStats,
    resetStats,
    cleanup
  }
}

    }

    analyticsLoading.value = true
    analyticsAbortController.value = new AbortController()

    try {
      const response = await fetch('/api/dashboard/analytics', {
        signal: analyticsAbortController.value.signal
      })
      
      // Check if component is still mounted after fetch
      if (!isMounted.value) {
        return
      }

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      analyticsData.value = data
    } catch (error) {
      // Don't process errors if component is unmounted or request was aborted
      if (!isMounted.value || error.name === 'AbortError') {
        return
      }
      console.error('Error fetching analytics:', error)
    } finally {
      if (isMounted.value) {
        analyticsLoading.value = false
      }
      analyticsAbortController.value = null
    }
  }

  const refreshStats = async () => {
    // Only proceed if component is still mounted
    if (!isMounted.value) {
      return
    }

    await Promise.all([
      fetchStats(),
      fetchAnalytics()
    ])
  }

  const updateStats = (newStats) => {
    if (isMounted.value) {
      dashboardStats.value = { ...dashboardStats.value, ...newStats }
    }
  }

  const resetStats = () => {
    if (isMounted.value) {
      dashboardStats.value = {
        totalUsers: 0,
        activeUsers: 0,
        idleSessions: 0,
        penalties: 0,
        recentActivities: 0,
        totalActivities: 0
      }
    }
  }

  // Cleanup function
  const cleanup = () => {
    cancelAllRequests()
    isMounted.value = false
  }
  // Lifecycle
  onMounted(() => {
    isMounted.value = true
    refreshStats()
  })

  onBeforeUnmount(() => {
    cleanup()
  })

  onUnmounted(() => {
    cleanup()
  })

  return {
    // State
    dashboardStats,
    analyticsData,
    statsLoading,
    analyticsLoading,
    
    // Computed
    statsPercentage,
    
    // Methods
    fetchStats,
    fetchAnalytics,
    refreshStats,
    updateStats,
    resetStats,
    cleanup
  }
}
