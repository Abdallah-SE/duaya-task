<template>
  <AppLayout 
    :user="user" 
    :user-settings="userSettings"
    :initial-settings="initialSettings"
    :can-control-idle-monitoring="canControlIdleMonitoring"
    :is-idle-monitoring-enabled="isIdleMonitoringEnabled"
  >
    <div class="space-y-6">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Employee Management
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Manage and monitor employee accounts and activities
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
          <Button
            @click="openCreateModal"
            :disabled="createLoading || loading"
            variant="success"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ createLoading ? 'Creating...' : 'Add Employee' }}
          </Button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Employees</h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">Advanced table with sorting, filtering, and pagination</p>
        </div>
        
        <EmployeesTable
          :employees="employees.data || []"
          :delete-loading="deleteLoading"
          @view="openViewModal"
          @edit="openEditModal"
          @delete="deleteEmployee"
          @search="handleSearch"
          @sort="handleSort"
          @page-change="handlePageChange"
          @per-page-change="handlePerPageChange"
        />
      </div>
    </div>

    <!-- Create Employee Modal -->
    <EmployeeModal
      :show="showCreateModal"
      :is-create="true"
      :loading="createLoading"
      :errors="createErrors"
      @submit="createEmployee"
      @close="closeCreateModal"
    />

    <!-- Edit Employee Modal -->
    <EmployeeModal
      :show="showEditModal"
      :employee-data="selectedEmployee"
      :is-create="false"
      :loading="editLoading"
      :errors="editErrors"
      @submit="updateEmployee"
      @close="closeEditModal"
    />

    <!-- View Employee Modal -->
    <UserViewModal
      :show="showViewModal"
      :user-data="selectedEmployee"
      @close="closeViewModal"
      @edit="openEditModal(selectedEmployee)"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EmployeesTable from '@/Components/Molecules/EmployeesTable.vue'
import EmployeeModal from '@/Components/Organisms/EmployeeModal.vue'
import UserViewModal from '@/Components/Organisms/UserViewModal.vue'
import Button from '@/Components/Atoms/Button.vue'

const props = defineProps({
  user: Object,
  userSettings: Object,
  initialSettings: Object,
  canControlIdleMonitoring: Boolean,
  isIdleMonitoringEnabled: Boolean,
  employees: Object
})

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const selectedEmployee = ref(null)

// Loading states
const loading = ref(false)
const createLoading = ref(false)
const editLoading = ref(false)
const deleteLoading = ref(false)

// Error handling
const globalError = ref('')
const createErrors = ref({})
const editErrors = ref({})

// Modal functions
const openCreateModal = () => {
  showCreateModal.value = true
  createErrors.value = {}
  globalError.value = ''
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createErrors.value = {}
  globalError.value = ''
}

const openEditModal = (employee) => {
  selectedEmployee.value = employee
  editErrors.value = {}
  globalError.value = ''
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editErrors.value = {}
  globalError.value = ''
}

const openViewModal = (employee) => {
  selectedEmployee.value = employee
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedEmployee.value = null
}

// Form submissions
const createEmployee = (formData) => {
  createLoading.value = true
  globalError.value = ''
  createErrors.value = {}
  
  router.post('/employee/employees', formData, {
    onSuccess: () => {
      closeCreateModal()
      globalError.value = ''
    },
    onError: (errors) => {
      createErrors.value = errors
      globalError.value = 'Failed to create employee. Please check the form for errors.'
    },
    onFinish: () => {
      createLoading.value = false
    }
  })
}

const updateEmployee = (formData) => {
  editLoading.value = true
  globalError.value = ''
  editErrors.value = {}
  
  router.put(`/employee/employees/${selectedEmployee.value.id}`, formData, {
    onSuccess: () => {
      closeEditModal()
      globalError.value = ''
    },
    onError: (errors) => {
      editErrors.value = errors
      globalError.value = 'Failed to update employee. Please check the form for errors.'
    },
    onFinish: () => {
      editLoading.value = false
    }
  })
}

const deleteEmployee = (employeeId) => {
  if (confirm('Are you sure you want to delete this employee?')) {
    deleteLoading.value = true
    globalError.value = ''
    
    router.delete(`/employee/employees/${employeeId}`, {
      onSuccess: () => {
        globalError.value = ''
      },
      onError: (errors) => {
        globalError.value = 'Failed to delete employee. Please try again.'
      },
      onFinish: () => {
        deleteLoading.value = false
      }
    })
  }
}

// Table event handlers
const handleSearch = (query) => {
  console.log('Search:', query)
}

const handleSort = ({ column, direction }) => {
  console.log('Sort:', column, direction)
}

const handlePageChange = (page) => {
  console.log('Page change:', page)
}

const handlePerPageChange = (perPage) => {
  console.log('Per page change:', perPage)
}

</script>
