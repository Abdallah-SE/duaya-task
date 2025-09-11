<template>
  <div class="data-table-container">
    <!-- Table Header -->
    <div class="table-header">
      <div class="table-title">
        <h3 class="text-lg font-medium text-gray-900">Users</h3>
        <p class="text-sm text-gray-500">Manage and monitor user accounts</p>
      </div>
      
      <div class="table-actions">
        <div class="search-container">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search users..."
            class="search-input"
            @input="handleSearch"
          />
          <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        
        <button
          @click="toggleColumnVisibility"
          class="action-button"
          title="Column Settings"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <table class="data-table">
        <thead class="table-header-row">
          <tr>
            <!-- Select All Checkbox -->
            <th class="table-header-cell select-cell">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="checkbox-input"
              />
            </th>
            
            <!-- Dynamic Columns -->
            <th
              v-for="column in visibleColumns"
              :key="column.key"
              class="table-header-cell"
              :class="{ 'sortable': column.sortable }"
              @click="column.sortable ? handleSort(column.key) : null"
            >
              <div class="header-cell-content">
                <span class="header-text">{{ column.label }}</span>
                <div v-if="column.sortable" class="sort-indicators">
                  <svg
                    v-if="sorting.field === column.key && sorting.direction === 'asc'"
                    class="sort-icon active"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg
                    v-else-if="sorting.field === column.key && sorting.direction === 'desc'"
                    class="sort-icon active"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                  <svg
                    v-else
                    class="sort-icon inactive"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v11a1 1 0 102 0V5a1 1 0 100-2H3zm11.293 9.293a1 1 0 001.414 1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </th>
          </tr>
        </thead>
        
        <tbody class="table-body">
          <!-- Loading State -->
          <tr v-if="loading" class="loading-row">
            <td :colspan="visibleColumns.length + 1" class="loading-cell">
              <div class="loading-content">
                <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="loading-text">Loading users...</span>
              </div>
            </td>
          </tr>
          
          <!-- Empty State -->
          <tr v-else-if="filteredData.length === 0" class="empty-row">
            <td :colspan="visibleColumns.length + 1" class="empty-cell">
              <div class="empty-content">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <h3 class="empty-title">No users found</h3>
                <p class="empty-description">Try adjusting your search or filter criteria</p>
              </div>
            </td>
          </tr>
          
          <!-- Data Rows -->
          <tr
            v-else
            v-for="(row, index) in paginatedData"
            :key="row.id || index"
            class="table-row"
            :class="{ 'selected': isRowSelected(row) }"
          >
            <!-- Select Checkbox -->
            <td class="table-cell select-cell">
              <input
                type="checkbox"
                :checked="isRowSelected(row)"
                @change="toggleRowSelection(row)"
                class="checkbox-input"
              />
            </td>
            
            <!-- Dynamic Cells -->
            <td
              v-for="column in visibleColumns"
              :key="column.key"
              class="table-cell"
              :class="getCellClasses(column)"
            >
              <div v-html="renderCell(row, column)"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.total > pagination.perPage" class="pagination-container">
      <div class="pagination-info">
        <span class="pagination-text">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </span>
      </div>
      
      <div class="pagination-controls">
        <button
          @click="goToPage(pagination.currentPage - 1)"
          :disabled="pagination.currentPage <= 1"
          class="pagination-button"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          Previous
        </button>
        
        <div class="pagination-pages">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="getPageButtonClasses(page)"
            class="pagination-page-button"
          >
            {{ page }}
          </button>
        </div>
        
        <button
          @click="goToPage(pagination.currentPage + 1)"
          :disabled="pagination.currentPage >= pagination.lastPage"
          class="pagination-button"
        >
          Next
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Object,
    default: null
  },
  sorting: {
    type: Object,
    default: () => ({ field: 'created_at', direction: 'desc' })
  },
  selection: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits([
  'sort',
  'page-change',
  'selection-change',
  'row-action',
  'search'
])

// State
const searchQuery = ref('')
const visibleColumns = ref([...props.columns])
const showColumnSettings = ref(false)

// Computed
const filteredData = computed(() => {
  if (!searchQuery.value) return props.data
  
  const query = searchQuery.value.toLowerCase()
  return props.data.filter(row => {
    return props.columns.some(column => {
      if (!column.searchable) return false
      const value = getNestedValue(row, column.key)
      return String(value).toLowerCase().includes(query)
    })
  })
})

const paginatedData = computed(() => {
  if (!props.pagination) return filteredData.value
  
  const start = (props.pagination.currentPage - 1) * props.pagination.perPage
  const end = start + props.pagination.perPage
  return filteredData.value.slice(start, end)
})

const isAllSelected = computed(() => {
  return props.data.length > 0 && props.selection.length === props.data.length
})

const visiblePages = computed(() => {
  if (!props.pagination) return []
  
  const current = props.pagination.currentPage
  const last = props.pagination.lastPage
  const pages = []
  
  // Always show first page
  if (current > 3) {
    pages.push(1)
    if (current > 4) pages.push('...')
  }
  
  // Show pages around current page
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }
  
  // Always show last page
  if (current < last - 2) {
    if (current < last - 3) pages.push('...')
    pages.push(last)
  }
  
  return pages
})

// Methods
const handleSort = (field) => {
  const direction = props.sorting.field === field && props.sorting.direction === 'asc' ? 'desc' : 'asc'
  emit('sort', { field, direction })
}

const handleSearch = () => {
  emit('search', searchQuery.value)
}

const goToPage = (page) => {
  if (page >= 1 && page <= props.pagination.lastPage) {
    emit('page-change', page)
  }
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    emit('selection-change', [])
  } else {
    emit('selection-change', [...props.data])
  }
}

const toggleRowSelection = (row) => {
  const isSelected = props.selection.some(item => item.id === row.id)
  if (isSelected) {
    emit('selection-change', props.selection.filter(item => item.id !== row.id))
  } else {
    emit('selection-change', [...props.selection, row])
  }
}

const isRowSelected = (row) => {
  return props.selection.some(item => item.id === row.id)
}

const renderCell = (row, column) => {
  const value = getNestedValue(row, column.key)
  
  if (column.key === 'name') {
    return `
      <div class="flex items-center">
        <div class="flex-shrink-0 h-10 w-10">
          <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>
        <div class="ml-4">
          <div class="text-sm font-medium text-gray-900">${value || 'N/A'}</div>
        </div>
      </div>
    `
  }
  
  if (column.key === 'roles') {
    const roles = value || []
    const roleBadges = roles.map(role => 
      `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-1 ${getRoleBadgeClass(role.name)}">${role.name}</span>`
    ).join('')
    return `<div>${roleBadges}</div>`
  }
  
  if (column.key === 'actions') {
    return `
      <div class="flex space-x-2">
        <button onclick="handleRowAction('view', ${JSON.stringify(row).replace(/"/g, '&quot;')})" class="text-indigo-600 hover:text-indigo-900 text-sm">View</button>
        <button onclick="handleRowAction('edit', ${JSON.stringify(row).replace(/"/g, '&quot;')})" class="text-green-600 hover:text-green-900 text-sm">Edit</button>
        <button onclick="handleRowAction('delete', ${JSON.stringify(row).replace(/"/g, '&quot;')})" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
      </div>
    `
  }
  
  if (column.key === 'created_at') {
    return `<div class="text-sm text-gray-500">${formatDate(value)}</div>`
  }
  
  return `<div class="text-sm text-gray-900">${value || 'N/A'}</div>`
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj)
}

const getRoleBadgeClass = (roleName) => {
  const classes = {
    'employee': 'bg-green-100 text-green-800',
    'admin': 'bg-purple-100 text-purple-800'
  }
  return classes[roleName] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

const getCellClasses = (column) => {
  const classes = []
  if (column.width) classes.push(`w-${column.width}`)
  return classes.join(' ')
}

const getPageButtonClasses = (page) => {
  const isCurrent = page === props.pagination.currentPage
  return isCurrent 
    ? 'bg-indigo-600 text-white' 
    : 'bg-white text-gray-700 hover:bg-gray-50'
}

const toggleColumnVisibility = () => {
  showColumnSettings.value = !showColumnSettings.value
}

// Make functions globally available for inline event handlers
window.handleRowAction = (action, row) => {
  emit('row-action', action, row)
}
</script>

<style scoped>
.data-table-container {
  @apply bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden;
}

.table-header {
  @apply px-6 py-4 border-b border-gray-200 flex items-center justify-between;
}

.table-title h3 {
  @apply text-lg font-medium text-gray-900;
}

.table-title p {
  @apply text-sm text-gray-500;
}

.table-actions {
  @apply flex items-center space-x-4;
}

.search-container {
  @apply relative;
}

.search-input {
  @apply pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500;
}

.search-icon {
  @apply absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400;
}

.action-button {
  @apply p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200;
}

.table-wrapper {
  @apply overflow-x-auto;
}

.data-table {
  @apply min-w-full divide-y divide-gray-200;
}

.table-header-row {
  @apply bg-gray-50;
}

.table-header-cell {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.table-header-cell.sortable {
  @apply cursor-pointer hover:bg-gray-100;
}

.header-cell-content {
  @apply flex items-center justify-between;
}

.sort-indicators {
  @apply ml-2;
}

.sort-icon {
  @apply w-4 h-4;
}

.sort-icon.active {
  @apply text-indigo-600;
}

.sort-icon.inactive {
  @apply text-gray-400;
}

.select-cell {
  @apply w-12;
}

.table-body {
  @apply bg-white divide-y divide-gray-200;
}

.table-row {
  @apply hover:bg-gray-50 transition-colors duration-150;
}

.table-row.selected {
  @apply bg-indigo-50;
}

.table-cell {
  @apply px-6 py-4 whitespace-nowrap text-sm;
}

.checkbox-input {
  @apply rounded border-gray-300 text-indigo-600 focus:ring-indigo-500;
}

.loading-row,
.empty-row {
  @apply bg-white;
}

.loading-cell,
.empty-cell {
  @apply px-6 py-12 text-center;
}

.loading-content {
  @apply flex flex-col items-center space-y-2;
}

.loading-text {
  @apply text-sm text-gray-500;
}

.empty-content {
  @apply flex flex-col items-center space-y-2;
}

.empty-icon {
  @apply w-12 h-12 text-gray-400;
}

.empty-title {
  @apply text-lg font-medium text-gray-900;
}

.empty-description {
  @apply text-sm text-gray-500;
}

.pagination-container {
  @apply px-6 py-4 border-t border-gray-200 flex items-center justify-between;
}

.pagination-text {
  @apply text-sm text-gray-700;
}

.pagination-controls {
  @apply flex items-center space-x-2;
}

.pagination-button {
  @apply relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed;
}

.pagination-pages {
  @apply flex space-x-1;
}

.pagination-page-button {
  @apply relative inline-flex items-center px-3 py-2 border text-sm font-medium;
}

.pagination-page-button:not(.bg-indigo-600) {
  @apply border-gray-300 bg-white text-gray-700 hover:bg-gray-50;
}
</style>
