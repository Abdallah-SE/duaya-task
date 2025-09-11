<template>
  <AppLayout 
    :user="user" 
    :user-settings="userSettings"
    :initial-settings="initialSettings"
    :can-control-idle-monitoring="canControlIdleMonitoring"
    :is-idle-monitoring-enabled="isIdleMonitoringEnabled"
  >
    <!-- Dashboard Header -->
    <DashboardHeader 
      :title="'Employee Management Dashboard'"
      :subtitle="'Comprehensive employee monitoring and management system'"
      :actions="headerActions"
      @action-click="handleHeaderAction"
    />

    <!-- Dashboard Grid Layout -->
    <div class="dashboard-grid">
      <!-- Statistics Cards Row -->
      <div class="dashboard-section">
        <StatisticsCards 
          :stats="dashboardStats"
          :loading="statsLoading"
          @refresh="refreshStats"
        />
      </div>

      <!-- Quick Actions Row -->
      <div class="dashboard-section">
        <QuickActions 
          :actions="quickActions"
          :loading="actionsLoading"
          @action-click="handleQuickAction"
        />
      </div>

      <!-- Main Content Area -->
      <div class="dashboard-main">
        <!-- Left Column - Filters & Controls -->
        <div class="dashboard-sidebar">
          <FilterPanel 
            :filters="activeFilters"
            :available-filters="availableFilters"
            @filter-change="handleFilterChange"
            @clear-filters="clearFilters"
          />
          
          <BulkActions 
            :selected-items="selectedUsers"
            :available-actions="bulkActions"
            @action-click="handleBulkAction"
            @clear-selection="clearSelection"
          />
        </div>

        <!-- Right Column - Data Table -->
        <div class="dashboard-content">
          <DataTable 
            :data="filteredUsers"
            :columns="tableColumns"
            :loading="tableLoading"
            :pagination="pagination"
            :sorting="sorting"
            :selection="selectedUsers"
            @sort="handleSort"
            @page-change="handlePageChange"
            @selection-change="handleSelectionChange"
            @row-action="handleRowAction"
          />
        </div>
      </div>

      <!-- Charts and Analytics Row -->
      <div class="dashboard-section">
        <AnalyticsCharts 
          :data="analyticsData"
          :loading="analyticsLoading"
          :chart-configs="chartConfigs"
          @chart-interaction="handleChartInteraction"
        />
      </div>
    </div>

    <!-- Modals -->
    <UserModal 
      v-if="showUserModal"
      :user="selectedUser"
      :mode="modalMode"
      :loading="modalLoading"
      @save="handleUserSave"
      @close="closeUserModal"
    />

    <ConfirmationModal 
      v-if="showConfirmationModal"
      :title="confirmationTitle"
      :message="confirmationMessage"
      :loading="confirmationLoading"
      @confirm="handleConfirmation"
      @cancel="closeConfirmationModal"
    />

    <!-- Toast Notifications -->
    <ToastContainer 
      :notifications="notifications"
      @remove="removeNotification"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Dashboard Components
import DashboardHeader from '@/Components/Dashboard/DashboardHeader.vue'
import StatisticsCards from '@/Components/Dashboard/StatisticsCards.vue'
import QuickActions from '@/Components/Dashboard/QuickActions.vue'
import FilterPanel from '@/Components/Dashboard/FilterPanel.vue'
import BulkActions from '@/Components/Dashboard/BulkActions.vue'
import DataTable from '@/Components/Dashboard/DataTable.vue'
import AnalyticsCharts from '@/Components/Dashboard/AnalyticsCharts.vue'
import UserModal from '@/Components/Modals/UserModal.vue'
import ConfirmationModal from '@/Components/Modals/ConfirmationModal.vue'
import ToastContainer from '@/Components/Notifications/ToastContainer.vue'

// Composables
import { useDashboard } from '@/Composables/useDashboard'
import { useUserManagement } from '@/Composables/useUserManagement'
import { useNotifications } from '@/Composables/useNotifications'
import { useFilters } from '@/Composables/useFilters'
import { usePagination } from '@/Composables/usePagination'

const props = defineProps({
  user: Object,
  userSettings: Object,
  initialSettings: Object,
  canControlIdleMonitoring: Boolean,
  isIdleMonitoringEnabled: Boolean,
  users: Object,
  stats: Object
})

// Composables
const { 
  dashboardStats, 
  statsLoading, 
  refreshStats,
  analyticsData,
  analyticsLoading 
} = useDashboard()

const {
  users: allUsers,
  tableLoading,
  selectedUsers,
  selectedUser,
  showUserModal,
  modalMode,
  modalLoading,
  handleUserSave,
  handleRowAction,
  clearSelection
} = useUserManagement(props.users)

const { 
  notifications, 
  addNotification, 
  removeNotification 
} = useNotifications()

const {
  activeFilters,
  availableFilters,
  filteredUsers,
  handleFilterChange,
  clearFilters
} = useFilters(allUsers)

const {
  pagination,
  sorting,
  handleSort,
  handlePageChange
} = usePagination(filteredUsers)

// State
const showConfirmationModal = ref(false)
const confirmationTitle = ref('')
const confirmationMessage = ref('')
const confirmationLoading = ref(false)
const actionsLoading = ref(false)

// Header Actions
const headerActions = computed(() => [
  {
    id: 'add-user',
    label: 'Add Employee',
    icon: 'plus',
    variant: 'primary',
    loading: modalLoading.value
  },
  {
    id: 'export-data',
    label: 'Export Data',
    icon: 'download',
    variant: 'secondary'
  },
  {
    id: 'refresh',
    label: 'Refresh',
    icon: 'refresh',
    variant: 'outline',
    loading: statsLoading.value
  }
])

// Quick Actions
const quickActions = computed(() => [
  {
    id: 'view-active',
    label: 'View Active Users',
    icon: 'users',
    count: dashboardStats.value.activeUsers,
    variant: 'success'
  },
  {
    id: 'view-idle',
    label: 'Idle Sessions',
    icon: 'clock',
    count: dashboardStats.value.idleSessions,
    variant: 'warning'
  },
  {
    id: 'view-penalties',
    label: 'Penalties',
    icon: 'exclamation',
    count: dashboardStats.value.penalties,
    variant: 'danger'
  },
  {
    id: 'view-activities',
    label: 'Recent Activities',
    icon: 'activity',
    count: dashboardStats.value.recentActivities,
    variant: 'info'
  }
])

// Bulk Actions
const bulkActions = computed(() => [
  {
    id: 'bulk-export',
    label: 'Export Selected',
    icon: 'download',
    variant: 'secondary'
  },
  {
    id: 'bulk-deactivate',
    label: 'Deactivate',
    icon: 'user-minus',
    variant: 'warning'
  },
  {
    id: 'bulk-delete',
    label: 'Delete',
    icon: 'trash',
    variant: 'danger'
  }
])

// Table Columns Configuration
const tableColumns = computed(() => [
  {
    key: 'name',
    label: 'Name',
    sortable: true,
    searchable: true,
    width: '200px'
  },
  {
    key: 'email',
    label: 'Email',
    sortable: true,
    searchable: true,
    width: '250px'
  },
  {
    key: 'roles',
    label: 'Role',
    sortable: false,
    searchable: false,
    width: '150px'
  },
  {
    key: 'activity_logs_count',
    label: 'Activities',
    sortable: true,
    searchable: false,
    width: '100px'
  },
  {
    key: 'idle_sessions_count',
    label: 'Idle Sessions',
    sortable: true,
    searchable: false,
    width: '120px'
  },
  {
    key: 'created_at',
    label: 'Created',
    sortable: true,
    searchable: false,
    width: '120px'
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
    searchable: false,
    width: '150px'
  }
])

// Chart Configurations
const chartConfigs = computed(() => [
  {
    type: 'line',
    title: 'User Activity Over Time',
    dataKey: 'activities',
    color: '#3B82F6'
  },
  {
    type: 'bar',
    title: 'Users by Department',
    dataKey: 'departments',
    color: '#10B981'
  },
  {
    type: 'pie',
    title: 'Role Distribution',
    dataKey: 'roles',
    colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444']
  }
])

// Event Handlers
const handleHeaderAction = async (actionId) => {
  switch (actionId) {
    case 'add-user':
      selectedUser.value = null
      modalMode.value = 'create'
      showUserModal.value = true
      break
    case 'export-data':
      await exportData()
      break
    case 'refresh':
      await refreshStats()
      break
  }
}

const handleQuickAction = async (actionId) => {
  switch (actionId) {
    case 'view-active':
      handleFilterChange({ key: 'status', value: 'active' })
      break
    case 'view-idle':
      handleFilterChange({ key: 'idle_sessions', value: '>0' })
      break
    case 'view-penalties':
      handleFilterChange({ key: 'penalties', value: '>0' })
      break
    case 'view-activities':
      handleFilterChange({ key: 'recent_activity', value: 'today' })
      break
  }
}

const handleBulkAction = async (actionId) => {
  if (selectedUsers.value.length === 0) {
    addNotification({
      type: 'warning',
      message: 'Please select users first'
    })
    return
  }

  switch (actionId) {
    case 'bulk-export':
      await exportSelectedUsers()
      break
    case 'bulk-deactivate':
      showConfirmationModal.value = true
      confirmationTitle.value = 'Deactivate Users'
      confirmationMessage.value = `Are you sure you want to deactivate ${selectedUsers.value.length} users?`
      break
    case 'bulk-delete':
      showConfirmationModal.value = true
      confirmationTitle.value = 'Delete Users'
      confirmationMessage.value = `Are you sure you want to delete ${selectedUsers.value.length} users? This action cannot be undone.`
      break
  }
}

const handleConfirmation = async () => {
  confirmationLoading.value = true
  
  try {
    // Handle confirmation logic here
    addNotification({
      type: 'success',
      message: 'Action completed successfully'
    })
  } catch (error) {
    addNotification({
      type: 'error',
      message: 'An error occurred while processing the request'
    })
  } finally {
    confirmationLoading.value = false
    closeConfirmationModal()
  }
}

const handleChartInteraction = (chartData) => {
  // Handle chart interactions (e.g., filter data based on chart selection)
  console.log('Chart interaction:', chartData)
}

const closeUserModal = () => {
  showUserModal.value = false
  selectedUser.value = null
}

const closeConfirmationModal = () => {
  showConfirmationModal.value = false
  confirmationTitle.value = ''
  confirmationMessage.value = ''
}

const exportData = async () => {
  // Export functionality
  addNotification({
    type: 'info',
    message: 'Export started...'
  })
}

const exportSelectedUsers = async () => {
  // Export selected users functionality
  addNotification({
    type: 'info',
    message: `Exporting ${selectedUsers.value.length} users...`
  })
}

// Lifecycle
onMounted(() => {
  refreshStats()
})
</script>

<style scoped>
.dashboard-grid {
  @apply space-y-6;
}

.dashboard-section {
  @apply w-full;
}

.dashboard-main {
  @apply grid grid-cols-1 lg:grid-cols-4 gap-6;
}

.dashboard-sidebar {
  @apply lg:col-span-1 space-y-6;
}

.dashboard-content {
  @apply lg:col-span-3;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .dashboard-main {
    @apply grid-cols-1;
  }
  
  .dashboard-sidebar {
    @apply order-2;
  }
  
  .dashboard-content {
    @apply order-1;
  }
}
</style>
