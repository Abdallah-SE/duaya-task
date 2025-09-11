<template>
  <div class="bg-white shadow overflow-hidden sm:rounded-md">
    <!-- Search and Filters -->
    <div v-if="showSearch || showFilters" class="px-4 py-3 border-b border-gray-200">
      <div class="flex flex-col sm:flex-row gap-4">
        <div v-if="showSearch" class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            @input="handleSearch"
          />
        </div>
        <div v-if="showFilters" class="flex gap-2">
          <button
            @click="resetFilters"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="handleSort(column)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <span v-if="sortColumn === column.key">
                  <span v-if="sortDirection === 'asc'" class="text-indigo-600">↑</span>
                  <span v-else class="text-indigo-600">↓</span>
                </span>
                <span v-else class="text-gray-400">↕</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(row, index) in paginatedData" :key="row.id || index" class="hover:bg-gray-50">
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="getNestedValue(row, column.key)"
                :column="column"
              >
                {{ getNestedValue(row, column.key) }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="filteredData.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No data found</h3>
      <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
    </div>

    <!-- Pagination -->
    <div v-if="showPagination && totalPages > 1" class="px-4 py-3 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-700">Show</span>
          <select
            v-model="perPage"
            @change="handlePerPageChange"
            class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          >
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
          </select>
          <span class="text-sm text-gray-700">entries</span>
        </div>
        
        <div class="flex items-center space-x-2">
          <button
            @click="goToPage(1)"
            :disabled="currentPage === 1"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            First
          </button>
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <span class="text-sm text-gray-700">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
          <button
            @click="goToPage(totalPages)"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Last
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  showSearch: {
    type: Boolean,
    default: true
  },
  showFilters: {
    type: Boolean,
    default: false
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  initialPageSize: {
    type: Number,
    default: 10
  }
})

const emit = defineEmits(['search', 'sort', 'page-change', 'per-page-change'])

// Reactive state
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = ref(props.initialPageSize)
const sortColumn = ref('')
const sortDirection = ref('asc')

// Computed properties
const filteredData = computed(() => {
  let filtered = [...props.data]
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(row => {
      return props.columns.some(column => {
        if (column.searchable === false) return false
        const value = getNestedValue(row, column.key)
        return String(value).toLowerCase().includes(query)
      })
    })
  }
  
  // Apply sorting
  if (sortColumn.value) {
    filtered.sort((a, b) => {
      const aValue = getNestedValue(a, sortColumn.value)
      const bValue = getNestedValue(b, sortColumn.value)
      
      if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1
      if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })
  }
  
  return filtered
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage.value))

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredData.value.slice(start, end)
})

// Methods
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj) ?? ''
}

const handleSearch = () => {
  currentPage.value = 1
  emit('search', searchQuery.value)
}

const handleSort = (column) => {
  if (column.sortable === false) return
  
  if (sortColumn.value === column.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column.key
    sortDirection.value = 'asc'
  }
  emit('sort', { column: column.key, direction: sortDirection.value })
}

const handlePerPageChange = () => {
  currentPage.value = 1
  emit('per-page-change', perPage.value)
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    emit('page-change', page)
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  sortColumn.value = ''
  sortDirection.value = 'asc'
  currentPage.value = 1
}

// Watch for data changes
watch(() => props.data, () => {
  currentPage.value = 1
})
</script>