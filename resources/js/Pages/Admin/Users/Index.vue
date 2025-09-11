<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        User Management
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Header with Add User Button -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Users</h3>
                <p class="text-sm text-gray-600">Manage system users and their permissions</p>
              </div>
              <Button
                @click="openCreateModal"
                :disabled="createLoading || loading"
                variant="primary"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ createLoading ? 'Creating...' : 'Add New User' }}
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


            <!-- Data Table -->
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
      </div>
    </div>

    <!-- Create User Modal -->
    <UserModal
      :show="showCreateModal"
      :is-create="true"
      :loading="createLoading"
      :errors="createErrors"
      :show-role-field="true"
      :available-roles="availableRoles"
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
      :show-role-field="true"
      :available-roles="availableRoles"
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
  users: Object,
  errors: Object,
  currentUser: Object
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

// Available roles based on current user
const availableRoles = computed(() => {
  const roles = [{ value: 'employee', label: 'Employee' }]
  if (props.currentUser?.roles?.[0]?.name === 'admin') {
    roles.unshift({ value: 'admin', label: 'Admin' })
  }
  return roles
})



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
  
  router.post('/admin/users', formData, {
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
  
  router.put(`/admin/users/${selectedUser.value.id}`, formData, {
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
    
    router.delete(`/admin/users/${userId}`, {
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