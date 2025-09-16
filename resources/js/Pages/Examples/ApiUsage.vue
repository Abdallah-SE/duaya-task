<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">API Usage Examples</h1>
    
    <!-- Authentication Example -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-4">Authentication</h2>
      <div class="space-y-4">
        <button 
          @click="loginAdmin" 
          :disabled="loading"
          class="bg-blue-500 text-white px-4 py-2 rounded disabled:opacity-50"
        >
          Login as Admin
        </button>
        <button 
          @click="loginEmployee" 
          :disabled="loading"
          class="bg-green-500 text-white px-4 py-2 rounded disabled:opacity-50"
        >
          Login as Employee
        </button>
        <button 
          @click="logout" 
          :disabled="loading"
          class="bg-red-500 text-white px-4 py-2 rounded disabled:opacity-50"
        >
          Logout
        </button>
      </div>
    </div>

    <!-- Dashboard Data Example -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-4">Dashboard Data</h2>
      <button 
        @click="loadDashboard" 
        :disabled="loading"
        class="bg-purple-500 text-white px-4 py-2 rounded disabled:opacity-50"
      >
        Load Dashboard
      </button>
      
      <div v-if="dashboardData" class="mt-4 p-4 bg-gray-100 rounded">
        <h3 class="font-semibold">Dashboard Data:</h3>
        <pre>{{ JSON.stringify(dashboardData, null, 2) }}</pre>
      </div>
    </div>

    <!-- Stats Example -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold mb-4">Statistics</h2>
      <button 
        @click="loadStats" 
        :disabled="loading"
        class="bg-indigo-500 text-white px-4 py-2 rounded disabled:opacity-50"
      >
        Load Stats
      </button>
      
      <div v-if="stats" class="mt-4 p-4 bg-gray-100 rounded">
        <h3 class="font-semibold">Stats:</h3>
        <pre>{{ JSON.stringify(stats, null, 2) }}</pre>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useInertiaAuth } from '@/Composables/useInertiaAuth'
import { useDashboard } from '@/Composables/useDashboard'

const { loginAdmin: authLoginAdmin, loginEmployee: authLoginEmployee, logout: authLogout, loading: authLoading, errors: authErrors } = useInertiaAuth()
const { loadDashboard: loadDashboardData, loadStats: loadStatsData, dashboardData, stats, loading: dashboardLoading, error: dashboardError } = useDashboard()

const loading = ref(false)
const error = ref(null)

// Authentication methods
const loginAdmin = async () => {
  loading.value = true
  error.value = null
  
  try {
    await authLoginAdmin({
      email: 'admin@example.com',
      password: 'password'
    })
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const loginEmployee = async () => {
  loading.value = true
  error.value = null
  
  try {
    await authLoginEmployee({
      email: 'employee@example.com',
      password: 'password'
    })
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const logout = async () => {
  loading.value = true
  error.value = null
  
  try {
    await authLogout()
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// Dashboard methods
const loadDashboard = async () => {
  loading.value = true
  error.value = null
  
  try {
    await loadDashboardData()
    dashboardData.value = dashboardData.value
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  loading.value = true
  error.value = null
  
  try {
    await loadStatsData()
    stats.value = stats.value
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>
