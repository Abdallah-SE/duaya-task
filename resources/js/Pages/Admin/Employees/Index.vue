<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Employee Management
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Header with Add Employee Button -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Employees</h3>
                <p class="text-sm text-gray-600">Manage employee accounts and their information</p>
              </div>
              <Button
                @click="openCreateModal"
                :disabled="createLoading || loading"
                variant="primary"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ createLoading ? 'Creating...' : 'Add New Employee' }}
              </Button>
            </div>

            <!-- Global Error Alert -->
            <div v-if="globalError" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
              <span class="block sm:inline">{{ globalError }}</span>
              <button @click="globalError = ''" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close error message">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                  <title>Close</title>
                  <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
              </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Employees</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.totalEmployees }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Active Employees</p>
                    <p class="text-2xl font-bold text-green-900">{{ stats.activeEmployees }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-600">Total Activities</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ stats.totalActivities }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-red-50 p-4 rounded-lg">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-red-600">Total Penalties</p>
                    <p class="text-2xl font-bold text-red-900">{{ stats.totalPenalties }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Data Table -->
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
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EmployeesTable from '@/Components/Molecules/EmployeesTable.vue'
import EmployeeModal from '@/Components/Organisms/EmployeeModal.vue'
import UserViewModal from '@/Components/Organisms/UserViewModal.vue'
import Button from '@/Components/Atoms/Button.vue'

const props = defineProps({
  employees: Object,
  currentUser: Object,
  stats: Object
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
  
  router.post('/admin/employees', formData, {
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
  
  router.put(`/admin/employees/${selectedEmployee.value.id}`, formData, {
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
    
    router.delete(`/admin/employees/${employeeId}`, {
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
