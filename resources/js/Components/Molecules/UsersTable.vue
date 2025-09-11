<template>
  <TanStackTable
    :data="users"
    :columns="columns"
    :show-search="true"
    :show-pagination="true"
    search-placeholder="Search users..."
    :initial-page-size="10"
    @search="handleSearch"
    @sort="handleSort"
    @page-change="handlePageChange"
    @per-page-change="handlePerPageChange"
  >
    <!-- Custom column templates -->
    <template #cell-name="{ row }">
      <div class="flex items-center">
        <div class="flex-shrink-0 h-10 w-10">
          <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
            <span class="text-sm font-medium text-gray-700">{{ row.name.charAt(0).toUpperCase() }}</span>
          </div>
        </div>
        <div class="ml-4">
          <div class="text-sm font-medium text-gray-900">{{ row.name }}</div>
          <div class="text-sm text-gray-500">ID: {{ row.id }}</div>
        </div>
      </div>
    </template>

    <template #cell-email="{ row }">
      <div class="text-sm text-gray-900">{{ row.email }}</div>
    </template>

    <template #cell-created_at="{ row }">
      <div class="text-sm text-gray-900">{{ formatDate(row.created_at) }}</div>
    </template>

    <template #cell-updated_at="{ row }">
      <div class="text-sm text-gray-900">{{ formatDate(row.updated_at) }}</div>
    </template>

    <template #cell-actions="{ row }">
      <div class="flex space-x-2">
        <button
          @click="$emit('view', row)"
          class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50 transition-colors duration-150"
          title="View User"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </button>
        <button
          @click="$emit('edit', row)"
          class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50 transition-colors duration-150"
          title="Edit User"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
        </button>
        <button
          @click="$emit('delete', row.id)"
          class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-150 disabled:opacity-50"
          title="Delete User"
          :disabled="deleteLoading"
        >
          <svg v-if="deleteLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
        </button>
      </div>
    </template>
  </TanStackTable>
</template>

<script setup>
import { computed } from 'vue'
import TanStackTable from './TanStackTable.vue'

const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  deleteLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['view', 'edit', 'delete', 'search', 'sort', 'page-change', 'per-page-change'])

// Table configuration
const columns = [
  { key: 'name', label: 'User', sortable: true, searchable: true },
  { key: 'email', label: 'Email', sortable: true, searchable: true },
  { key: 'created_at', label: 'Created At', sortable: true, searchable: false },
  { key: 'updated_at', label: 'Updated At', sortable: true, searchable: false },
  { key: 'actions', label: 'Actions', sortable: false, searchable: false }
]

// Event handlers
const handleSearch = (query) => {
  emit('search', query)
}

const handleSort = ({ column, direction }) => {
  emit('sort', { column, direction })
}

const handlePageChange = (page) => {
  emit('page-change', page)
}

const handlePerPageChange = (perPage) => {
  emit('per-page-change', perPage)
}

// Utility functions
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString()
}
</script>
