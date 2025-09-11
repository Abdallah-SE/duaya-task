import { ref, computed, watch } from 'vue'

export function useFilters(users) {
  // State
  const activeFilters = ref({})
  const searchQuery = ref('')
  const sortField = ref('created_at')
  const sortDirection = ref('desc')

  // Available filter options
  const availableFilters = ref([
    {
      key: 'status',
      label: 'Status',
      type: 'select',
      options: [
        { value: 'active', label: 'Active' },
        { value: 'inactive', label: 'Inactive' },
        { value: 'all', label: 'All' }
      ]
    },
    {
      key: 'role',
      label: 'Role',
      type: 'multiselect',
      options: [
        { value: 'employee', label: 'Employee' },
        { value: 'admin', label: 'Admin' },
        { value: 'manager', label: 'Manager' }
      ]
    },
    {
      key: 'department',
      label: 'Department',
      type: 'select',
      options: [
        { value: 'it', label: 'IT' },
        { value: 'hr', label: 'Human Resources' },
        { value: 'finance', label: 'Finance' },
        { value: 'marketing', label: 'Marketing' }
      ]
    },
    {
      key: 'activity_level',
      label: 'Activity Level',
      type: 'select',
      options: [
        { value: 'high', label: 'High (>50 activities)' },
        { value: 'medium', label: 'Medium (10-50 activities)' },
        { value: 'low', label: 'Low (<10 activities)' }
      ]
    },
    {
      key: 'idle_sessions',
      label: 'Idle Sessions',
      type: 'select',
      options: [
        { value: 'none', label: 'No Idle Sessions' },
        { value: 'some', label: 'Has Idle Sessions' },
        { value: 'many', label: 'Many Idle Sessions (>5)' }
      ]
    },
    {
      key: 'created_date',
      label: 'Created Date',
      type: 'daterange',
      options: []
    },
    {
      key: 'last_activity',
      label: 'Last Activity',
      type: 'daterange',
      options: []
    }
  ])

  // Computed
  const filteredUsers = computed(() => {
    let filtered = [...users.value]

    // Apply search query
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(user => 
        user.name?.toLowerCase().includes(query) ||
        user.email?.toLowerCase().includes(query) ||
        user.roles?.some(role => role.name.toLowerCase().includes(query))
      )
    }

    // Apply active filters
    Object.entries(activeFilters.value).forEach(([key, value]) => {
      if (!value || value === 'all') return

      switch (key) {
        case 'status':
          filtered = filtered.filter(user => {
            if (value === 'active') return user.is_active !== false
            if (value === 'inactive') return user.is_active === false
            return true
          })
          break

        case 'role':
          if (Array.isArray(value) && value.length > 0) {
            filtered = filtered.filter(user => 
              user.roles?.some(role => value.includes(role.name))
            )
          }
          break

        case 'department':
          filtered = filtered.filter(user => 
            user.employee?.department === value
          )
          break

        case 'activity_level':
          filtered = filtered.filter(user => {
            const activityCount = user.activity_logs_count || 0
            switch (value) {
              case 'high': return activityCount > 50
              case 'medium': return activityCount >= 10 && activityCount <= 50
              case 'low': return activityCount < 10
              default: return true
            }
          })
          break

        case 'idle_sessions':
          filtered = filtered.filter(user => {
            const idleCount = user.idle_sessions_count || 0
            switch (value) {
              case 'none': return idleCount === 0
              case 'some': return idleCount > 0
              case 'many': return idleCount > 5
              default: return true
            }
          })
          break

        case 'created_date':
          if (value && value.start && value.end) {
            const startDate = new Date(value.start)
            const endDate = new Date(value.end)
            filtered = filtered.filter(user => {
              const createdDate = new Date(user.created_at)
              return createdDate >= startDate && createdDate <= endDate
            })
          }
          break

        case 'last_activity':
          if (value && value.start && value.end) {
            const startDate = new Date(value.start)
            const endDate = new Date(value.end)
            filtered = filtered.filter(user => {
              const lastActivity = new Date(user.last_activity_at || user.created_at)
              return lastActivity >= startDate && lastActivity <= endDate
            })
          }
          break
      }
    })

    // Apply sorting
    filtered.sort((a, b) => {
      let aValue = a[sortField.value]
      let bValue = b[sortField.value]

      // Handle nested properties
      if (sortField.value === 'roles') {
        aValue = a.roles?.[0]?.name || ''
        bValue = b.roles?.[0]?.name || ''
      }

      // Handle dates
      if (sortField.value.includes('_at') || sortField.value === 'created_at') {
        aValue = new Date(aValue || 0)
        bValue = new Date(bValue || 0)
      }

      // Compare values
      if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1
      if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })

    return filtered
  })

  const activeFilterCount = computed(() => {
    return Object.values(activeFilters.value).filter(value => 
      value && value !== 'all' && (!Array.isArray(value) || value.length > 0)
    ).length
  })

  const hasActiveFilters = computed(() => activeFilterCount.value > 0)

  // Methods
  const handleFilterChange = (filter) => {
    if (filter.value === null || filter.value === undefined || filter.value === '') {
      delete activeFilters.value[filter.key]
    } else {
      activeFilters.value[filter.key] = filter.value
    }
  }

  const clearFilters = () => {
    activeFilters.value = {}
    searchQuery.value = ''
    sortField.value = 'created_at'
    sortDirection.value = 'desc'
  }

  const clearFilter = (key) => {
    delete activeFilters.value[key]
  }

  const setSearchQuery = (query) => {
    searchQuery.value = query
  }

  const setSorting = (field, direction) => {
    sortField.value = field
    sortDirection.value = direction
  }

  const getFilterValue = (key) => {
    return activeFilters.value[key]
  }

  const hasFilter = (key) => {
    return key in activeFilters.value && activeFilters.value[key] !== null
  }

  // Watch for changes in users to reset filters if needed
  watch(() => users.value, () => {
    // Optionally reset filters when users change
    // clearFilters()
  })

  return {
    // State
    activeFilters,
    searchQuery,
    sortField,
    sortDirection,
    availableFilters,
    
    // Computed
    filteredUsers,
    activeFilterCount,
    hasActiveFilters,
    
    // Methods
    handleFilterChange,
    clearFilters,
    clearFilter,
    setSearchQuery,
    setSorting,
    getFilterValue,
    hasFilter
  }
}
