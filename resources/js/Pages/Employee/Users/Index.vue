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
            User Management
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Manage and monitor user accounts and activities
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
            {{ createLoading ? 'Creating...' : 'Add User' }}
          </Button>
        </div>
      </div>


      <!-- Data Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Users</h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">Advanced table with sorting, filtering, and pagination</p>
        </div>
        
        <UsersTable
          :users="users.data || []"
          :delete-loading="deleteLoading"
          @view="openViewModal"
          @edit="openEditModal"
          @delete="deleteUser"
          @search="handleSearch"
          @sort="handleSort"
          @page-change="handlePageChange"
          @per-page-change="handlePerPageChange"
        />
      </div>
    </div>

    <!-- Create User Modal -->
    <UserModal
      :show="showCreateModal"
      :is-create="true"
      :loading="createLoading"
      :errors="createErrors"
      :show-role-field="false"
      :available-roles="[{ value: 'employee', label: 'Employee' }]"
      @submit="createUser"
      @close="closeCreateModal"
    />

    <!-- Edit User Modal -->
    <UserModal
      :show="showEditModal"
      :user-data="selectedUser"
      :is-create="false"
      :loading="editLoading"
      :errors="editErrors"
      :show-role-field="false"
      :available-roles="[{ value: 'employee', label: 'Employee' }]"
      @submit="updateUser"
      @close="closeEditModal"
    />

    <!-- View User Modal -->
    <UserViewModal
      :show="showViewModal"
      :user-data="selectedUser"
      @close="closeViewModal"
      @edit="openEditModal(selectedUser)"
    />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :show="showConfirmationModal"
      :title="confirmationConfig.title"
      :message="confirmationConfig.message"
      :confirm-text="confirmationConfig.confirmText"
      :cancel-text="confirmationConfig.cancelText"
      :loading="confirmationConfig.loading"
      @confirm="handleConfirmationConfirm"
      @cancel="handleConfirmationCancel"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import UsersTable from '@/Components/Molecules/UsersTable.vue'
import UserModal from '@/Components/Organisms/UserModal.vue'
import UserViewModal from '@/Components/Organisms/UserViewModal.vue'
import Button from '@/Components/Atoms/Button.vue'
import { useToastNotifications } from '@/Composables/useToast.js'
import ConfirmationModal from '@/Components/Atoms/ConfirmationModal.vue'

const props = defineProps({
  user: Object,
  userSettings: Object,
  initialSettings: Object,
  canControlIdleMonitoring: Boolean,
  isIdleMonitoringEnabled: Boolean,
  users: Object,
  stats: Object
})

// Toast notifications
const { 
  showSuccess, 
  showError, 
  showDeleteConfirmation,
  showConfirmationModal,
  confirmationConfig,
  handleConfirmationConfirm,
  handleConfirmationCancel
} = useToastNotifications()

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const selectedUser = ref(null)

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

const openEditModal = (user) => {
  selectedUser.value = user
  editErrors.value = {}
  globalError.value = ''
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editErrors.value = {}
  globalError.value = ''
}

const openViewModal = (user) => {
  selectedUser.value = user
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedUser.value = null
}

// Form submissions
const createUser = (formData) => {
  createLoading.value = true
  globalError.value = ''
  createErrors.value = {}
  
  router.post('/employee/users', formData, {
    onSuccess: () => {
      closeCreateModal()
      globalError.value = ''
      showSuccess('User created successfully!')
    },
    onError: (errors) => {
      createErrors.value = errors
      globalError.value = 'Failed to create user. Please check the form for errors.'
      showError('Failed to create user. Please check the form for errors.')
    },
    onFinish: () => {
      createLoading.value = false
    }
  })
}

const updateUser = (formData) => {
  editLoading.value = true
  globalError.value = ''
  editErrors.value = {}
  
  router.put(`/employee/users/${selectedUser.value.id}`, formData, {
    onSuccess: () => {
      closeEditModal()
      globalError.value = ''
      showSuccess('User updated successfully!')
    },
    onError: (errors) => {
      editErrors.value = errors
      globalError.value = 'Failed to update user. Please check the form for errors.'
      showError('Failed to update user. Please check the form for errors.')
    },
    onFinish: () => {
      editLoading.value = false
    }
  })
}

const deleteUser = (userId) => {
  const user = props.users.data.find(u => u.id === userId)
  const userName = user ? user.name : 'this user'
  
  showDeleteConfirmation(userName, () => {
    deleteLoading.value = true
    globalError.value = ''
    
    router.delete(`/employee/users/${userId}`, {
      onSuccess: () => {
        globalError.value = ''
        showSuccess('User deleted successfully!')
      },
      onError: (errors) => {
        globalError.value = 'Failed to delete user. Please try again.'
        showError('Failed to delete user. Please try again.')
      },
      onFinish: () => {
        deleteLoading.value = false
      }
    })
  })
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