<template>
  <div class="filter-panel">
    <div class="filter-header">
      <h3 class="filter-title">Filters</h3>
      <button 
        v-if="hasActiveFilters" 
        @click="$emit('clear-filters')"
        class="clear-filters-btn"
      >
        Clear All
      </button>
    </div>
    
    <div class="filter-content">
      <div
        v-for="filter in availableFilters"
        :key="filter.key"
        class="filter-group"
      >
        <label class="filter-label">{{ filter.label }}</label>
        
        <!-- Select Filter -->
        <select
          v-if="filter.type === 'select'"
          :value="getFilterValue(filter.key)"
          @change="handleFilterChange({ key: filter.key, value: $event.target.value })"
          class="filter-select"
        >
          <option value="">All {{ filter.label }}</option>
          <option
            v-for="option in filter.options"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
        
        <!-- Multi-select Filter -->
        <div v-else-if="filter.type === 'multiselect'" class="multiselect-container">
          <div
            v-for="option in filter.options"
            :key="option.value"
            class="multiselect-option"
          >
            <input
              :id="`${filter.key}-${option.value}`"
              type="checkbox"
              :checked="isOptionSelected(filter.key, option.value)"
              @change="toggleMultiSelect(filter.key, option.value)"
              class="multiselect-checkbox"
            />
            <label :for="`${filter.key}-${option.value}`" class="multiselect-label">
              {{ option.label }}
            </label>
          </div>
        </div>
        
        <!-- Date Range Filter -->
        <div v-else-if="filter.type === 'daterange'" class="date-range-container">
          <input
            type="date"
            :value="getDateValue(filter.key, 'start')"
            @change="handleDateChange(filter.key, 'start', $event.target.value)"
            class="date-input"
            placeholder="Start date"
          />
          <span class="date-separator">to</span>
          <input
            type="date"
            :value="getDateValue(filter.key, 'end')"
            @change="handleDateChange(filter.key, 'end', $event.target.value)"
            class="date-input"
            placeholder="End date"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  },
  availableFilters: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['filter-change', 'clear-filters'])

const hasActiveFilters = computed(() => {
  return Object.values(props.filters).some(value => 
    value && value !== '' && (!Array.isArray(value) || value.length > 0)
  )
})

const getFilterValue = (key) => {
  return props.filters[key] || ''
}

const isOptionSelected = (filterKey, optionValue) => {
  const selectedValues = props.filters[filterKey] || []
  return Array.isArray(selectedValues) && selectedValues.includes(optionValue)
}

const handleFilterChange = (filter) => {
  emit('filter-change', filter)
}

const toggleMultiSelect = (filterKey, optionValue) => {
  const currentValues = props.filters[filterKey] || []
  let newValues
  
  if (currentValues.includes(optionValue)) {
    newValues = currentValues.filter(v => v !== optionValue)
  } else {
    newValues = [...currentValues, optionValue]
  }
  
  emit('filter-change', { key: filterKey, value: newValues })
}

const handleDateChange = (filterKey, type, value) => {
  const currentRange = props.filters[filterKey] || {}
  const newRange = { ...currentRange, [type]: value }
  
  emit('filter-change', { key: filterKey, value: newRange })
}

const getDateValue = (filterKey, type) => {
  const range = props.filters[filterKey] || {}
  return range[type] || ''
}
</script>

<style scoped>
.filter-panel {
  @apply bg-white border border-gray-200 rounded-lg p-4;
}

.filter-header {
  @apply flex items-center justify-between mb-4;
}

.filter-title {
  @apply text-lg font-medium text-gray-900;
}

.clear-filters-btn {
  @apply text-sm text-indigo-600 hover:text-indigo-800;
}

.filter-content {
  @apply space-y-4;
}

.filter-group {
  @apply space-y-2;
}

.filter-label {
  @apply block text-sm font-medium text-gray-700;
}

.filter-select {
  @apply block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm;
}

.multiselect-container {
  @apply space-y-2;
}

.multiselect-option {
  @apply flex items-center space-x-2;
}

.multiselect-checkbox {
  @apply rounded border-gray-300 text-indigo-600 focus:ring-indigo-500;
}

.multiselect-label {
  @apply text-sm text-gray-700 cursor-pointer;
}

.date-range-container {
  @apply flex items-center space-x-2;
}

.date-input {
  @apply flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm;
}

.date-separator {
  @apply text-sm text-gray-500;
}
</style>
