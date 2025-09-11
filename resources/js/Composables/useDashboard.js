import { ref, computed, onMounted } from 'vue'
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

  // Computed
  const statsPercentage = computed(() => ({
    activeUsers: dashboardStats.value.totalUsers > 0 
      ? Math.round((dashboardStats.value.activeUsers / dashboardStats.value.totalUsers) * 100)
      : 0,
    idleSessions: dashboardStats.value.totalUsers > 0
      ? Math.round((dashboardStats.value.idleSessions / dashboardStats.value.totalUsers) * 100)
      : 0
  }))

  // Methods
  const fetchStats = async () => {
    statsLoading.value = true
    try {
      const response = await fetch('/api/dashboard/stats')
      const data = await response.json()
      dashboardStats.value = data
    } catch (error) {
      console.error('Error fetching stats:', error)
    } finally {
      statsLoading.value = false
    }
  }

  const fetchAnalytics = async () => {
    analyticsLoading.value = true
    try {
      const response = await fetch('/api/dashboard/analytics')
      const data = await response.json()
      analyticsData.value = data
    } catch (error) {
      console.error('Error fetching analytics:', error)
    } finally {
      analyticsLoading.value = false
    }
  }

  const refreshStats = async () => {
    await Promise.all([
      fetchStats(),
      fetchAnalytics()
    ])
  }

  const updateStats = (newStats) => {
    dashboardStats.value = { ...dashboardStats.value, ...newStats }
  }

  const resetStats = () => {
    dashboardStats.value = {
      totalUsers: 0,
      activeUsers: 0,
      idleSessions: 0,
      penalties: 0,
      recentActivities: 0,
      totalActivities: 0
    }
  }

  // Lifecycle
  onMounted(() => {
    refreshStats()
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
    resetStats
  }
}
