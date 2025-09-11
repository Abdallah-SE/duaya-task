import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

export function useUserManagement(initialUsers = { data: [], total: 0 }) {
  // State
  const users = ref(initialUsers.data || [])
  const totalUsers = ref(initialUsers.total || 0)
  const selectedUsers = ref([])
  const selectedUser = ref(null)
  const showUserModal = ref(false)
  const modalMode = ref('create') // 'create', 'edit', 'view'
  const tableLoading = ref(false)
  const modalLoading = ref(false)

  // Computed
  const hasSelection = computed(() => selectedUsers.value.length > 0)
  const selectedCount = computed(() => selectedUsers.value.length)
  const isAllSelected = computed(() => 
    users.value.length > 0 && selectedUsers.value.length === users.value.length
  )

  // Methods
  const handleUserSave = async (userData) => {
    modalLoading.value = true
    try {
      if (modalMode.value === 'create') {
        await createUser(userData)
      } else if (modalMode.value === 'edit') {
        await updateUser(selectedUser.value.id, userData)
      }
      
      // Refresh users list
      await refreshUsers()
      
      return { success: true }
    } catch (error) {
      console.error('Error saving user:', error)
      return { success: false, error: error.message }
    } finally {
      modalLoading.value = false
    }
  }

  const handleRowAction = (action, user) => {
    selectedUser.value = user
    
    switch (action) {
      case 'view':
        modalMode.value = 'view'
        showUserModal.value = true
        break
      case 'edit':
        modalMode.value = 'edit'
        showUserModal.value = true
        break
      case 'delete':
        confirmDeleteUser(user)
        break
      case 'activate':
        toggleUserStatus(user.id, true)
        break
      case 'deactivate':
        toggleUserStatus(user.id, false)
        break
    }
  }

  const createUser = async (userData) => {
    return new Promise((resolve, reject) => {
      router.post('/users', userData, {
        onSuccess: () => resolve(),
        onError: (errors) => reject(new Error(Object.values(errors).join(', ')))
      })
    })
  }

  const updateUser = async (userId, userData) => {
    return new Promise((resolve, reject) => {
      router.put(`/users/${userId}`, userData, {
        onSuccess: () => resolve(),
        onError: (errors) => reject(new Error(Object.values(errors).join(', ')))
      })
    })
  }

  const deleteUser = async (userId) => {
    return new Promise((resolve, reject) => {
      router.delete(`/users/${userId}`, {
        onSuccess: () => resolve(),
        onError: (errors) => reject(new Error(Object.values(errors).join(', ')))
      })
    })
  }

  const toggleUserStatus = async (userId, isActive) => {
    tableLoading.value = true
    try {
      await updateUser(userId, { is_active: isActive })
      await refreshUsers()
    } catch (error) {
      console.error('Error toggling user status:', error)
    } finally {
      tableLoading.value = false
    }
  }

  const refreshUsers = async () => {
    tableLoading.value = true
    try {
      router.reload({ only: ['users'] })
    } catch (error) {
      console.error('Error refreshing users:', error)
    } finally {
      tableLoading.value = false
    }
  }

  const confirmDeleteUser = (user) => {
    if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
      deleteUser(user.id).then(() => {
        refreshUsers()
      }).catch(error => {
        console.error('Error deleting user:', error)
      })
    }
  }

  const selectUser = (user) => {
    if (!selectedUsers.value.find(u => u.id === user.id)) {
      selectedUsers.value.push(user)
    }
  }

  const deselectUser = (user) => {
    selectedUsers.value = selectedUsers.value.filter(u => u.id !== user.id)
  }

  const toggleUserSelection = (user) => {
    if (selectedUsers.value.find(u => u.id === user.id)) {
      deselectUser(user)
    } else {
      selectUser(user)
    }
  }

  const selectAll = () => {
    selectedUsers.value = [...users.value]
  }

  const clearSelection = () => {
    selectedUsers.value = []
  }

  const bulkDelete = async () => {
    if (selectedUsers.value.length === 0) return
    
    try {
      const userIds = selectedUsers.value.map(user => user.id)
      await Promise.all(userIds.map(id => deleteUser(id)))
      await refreshUsers()
      clearSelection()
    } catch (error) {
      console.error('Error bulk deleting users:', error)
    }
  }

  const bulkUpdate = async (updates) => {
    if (selectedUsers.value.length === 0) return
    
    try {
      const userIds = selectedUsers.value.map(user => user.id)
      await Promise.all(userIds.map(id => updateUser(id, updates)))
      await refreshUsers()
      clearSelection()
    } catch (error) {
      console.error('Error bulk updating users:', error)
    }
  }

  // Watch for changes in initial users
  watch(() => initialUsers, (newUsers) => {
    if (newUsers) {
      users.value = newUsers.data || []
      totalUsers.value = newUsers.total || 0
    }
  }, { deep: true })

  return {
    // State
    users,
    totalUsers,
    selectedUsers,
    selectedUser,
    showUserModal,
    modalMode,
    tableLoading,
    modalLoading,
    
    // Computed
    hasSelection,
    selectedCount,
    isAllSelected,
    
    // Methods
    handleUserSave,
    handleRowAction,
    createUser,
    updateUser,
    deleteUser,
    toggleUserStatus,
    refreshUsers,
    selectUser,
    deselectUser,
    toggleUserSelection,
    selectAll,
    clearSelection,
    bulkDelete,
    bulkUpdate
  }
}
