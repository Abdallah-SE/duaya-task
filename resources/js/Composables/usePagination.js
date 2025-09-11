import { ref, computed } from 'vue'

export function usePagination(data, options = {}) {
  const currentPage = ref(options.currentPage || 1)
  const perPage = ref(options.perPage || 10)
  const totalItems = ref(options.totalItems || 0)

  const totalPages = computed(() => {
    return Math.ceil(totalItems.value / perPage.value)
  })

  const paginatedData = computed(() => {
    if (!data.value) return []
    
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return data.value.slice(start, end)
  })

  const paginationInfo = computed(() => {
    const start = (currentPage.value - 1) * perPage.value + 1
    const end = Math.min(currentPage.value * perPage.value, totalItems.value)
    
    return {
      from: totalItems.value > 0 ? start : 0,
      to: end,
      total: totalItems.value,
      currentPage: currentPage.value,
      lastPage: totalPages.value,
      perPage: perPage.value
    }
  })

  const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  const nextPage = () => {
    if (currentPage.value < totalPages.value) {
      currentPage.value++
    }
  }

  const previousPage = () => {
    if (currentPage.value > 1) {
      currentPage.value--
    }
  }

  const setPerPage = (newPerPage) => {
    perPage.value = newPerPage
    currentPage.value = 1 // Reset to first page
  }

  const updateTotalItems = (total) => {
    totalItems.value = total
  }

  return {
    // State
    currentPage,
    perPage,
    totalItems,
    
    // Computed
    totalPages,
    paginatedData,
    paginationInfo,
    
    // Methods
    goToPage,
    nextPage,
    previousPage,
    setPerPage,
    updateTotalItems
  }
}
