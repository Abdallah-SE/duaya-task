<template>
  <TanStackTable
    :data="roleSettings"
    :columns="columns"
    :show-search="true"
    :show-pagination="true"
    search-placeholder="Search roles..."
    :initial-page-size="10"
    @search="handleSearch"
    @sort="handleSort"
    @page-change="handlePageChange"
    @per-page-change="handlePerPageChange"
  >
    <!-- Custom column templates -->
    <template #cell-role_name="{ row }">
      <div class="flex items-center">
        <div class="flex-shrink-0 h-10 w-10">
          <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
            <span class="text-sm font-medium text-indigo-600">
              {{ row.role_display_name.charAt(0).toUpperCase() }}
            </span>
          </div>
        </div>
        <div class="ml-4">
          <div class="text-sm font-medium text-gray-900">{{ row.role_display_name }}</div>
          <div class="text-sm text-gray-500">{{ row.role_name }}</div>
        </div>
      </div>
    </template>

    <template #cell-guard_name="{ row }">
      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="row.guard_name === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
        {{ row.guard_name }}
      </span>
    </template>

    <template #cell-idle_monitoring_enabled="{ row }">
      <div class="flex items-center">
        <div class="flex items-center">
          <div class="relative">
            <input
              v-if="!row.is_admin"
              type="checkbox"
              :checked="row.idle_monitoring_enabled"
              :disabled="!canControl"
              @change="toggleRole(row)"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              :class="canControl ? 'cursor-pointer' : 'bg-gray-50 cursor-not-allowed'"
            />
            <div
              v-else
              class="h-4 w-4 bg-gray-200 border border-gray-300 rounded flex items-center justify-center"
            >
              <svg class="h-3 w-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-2 flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="row.idle_monitoring_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
              <svg v-if="row.idle_monitoring_enabled" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
              {{ row.idle_monitoring_enabled ? 'Enabled' : 'Disabled' }}
            </span>
            <span v-if="row.is_admin" class="text-gray-500 text-xs">(Fixed)</span>
          </div>
        </div>
      </div>
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
          title="View Role Details"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </button>
        <button
          v-if="!row.is_admin && canControl"
          @click="$emit('toggle', row)"
          class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50 transition-colors duration-150 disabled:opacity-50"
          title="Toggle Monitoring"
          :disabled="toggleLoading"
        >
          <svg v-if="toggleLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
          </svg>
        </button>
        <span v-else-if="row.is_admin" class="text-gray-400 text-xs px-2 py-1 rounded bg-gray-100">
          Fixed
        </span>
        <span v-else-if="!canControl" class="text-gray-400 text-xs px-2 py-1 rounded bg-gray-100">
          Read Only
        </span>
      </div>
    </template>
  </TanStackTable>
</template>

<script setup>
import { computed } from 'vue'
import TanStackTable from './TanStackTable.vue'

const props = defineProps({
  roleSettings: {
    type: Array,
    default: () => []
  },
  toggleLoading: {
    type: Boolean,
    default: false
  },
  canControl: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['view', 'toggle', 'search', 'sort', 'page-change', 'per-page-change'])

// Table configuration
const columns = [
  { key: 'role_name', label: 'Role', sortable: true, searchable: true },
  { key: 'guard_name', label: 'Guard', sortable: true, searchable: false },
  { key: 'idle_monitoring_enabled', label: 'Idle Monitoring', sortable: true, searchable: false },
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

const toggleRole = (role) => {
  if (props.canControl) {
    emit('toggle', role)
  }
}

// Utility functions
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString()
}
</script>
