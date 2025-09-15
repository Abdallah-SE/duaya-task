import { useToast } from 'vue-toastification'
import { ref, onMounted, onBeforeUnmount, onUnmounted } from 'vue'

export function useToastNotifications() {
  const toast = useToast()
  
  // Component state tracking
  const isMounted = ref(false)
  
  // Track active toasts for cleanup
  const activeToasts = ref([])

  const showSuccess = (message) => {
    if (!isMounted.value) return
    
    const toastId = toast.success(message, {
      position: 'top-right',
      timeout: 4000,
    })
    activeToasts.value.push(toastId)
  }

  const showError = (message) => {
    if (!isMounted.value) return
    
    const toastId = toast.error(message, {
      position: 'top-right',
      timeout: 6000,
    })
    activeToasts.value.push(toastId)
  }

  const showWarning = (message) => {
    if (!isMounted.value) return
    
    const toastId = toast.warning(message, {
      position: 'top-right',
      timeout: 5000,
    })
    activeToasts.value.push(toastId)
  }

  const showInfo = (message) => {
    if (!isMounted.value) return
    
    const toastId = toast.info(message, {
      position: 'top-right',
      timeout: 4000,
    })
    activeToasts.value.push(toastId)
  }

  // Confirmation modal state
  const showConfirmationModal = ref(false)
  const confirmationConfig = ref({
    title: 'Confirm Action',
    message: '',
    confirmText: 'Yes, Continue',
    cancelText: 'Cancel',
    onConfirm: null,
    onCancel: null,
    loading: false
  })

  const showConfirmation = (message, onConfirm, onCancel = null, title = 'Confirm Action') => {
    confirmationConfig.value = {
      title,
      message,
      confirmText: 'Yes, Continue',
      cancelText: 'Cancel',
      onConfirm,
      onCancel,
      loading: false
    }
    showConfirmationModal.value = true
  }

  const showDeleteConfirmation = (itemName, onConfirm, onCancel = null) => {
    const message = `Are you sure you want to delete ${itemName}? This action cannot be undone.`
    confirmationConfig.value = {
      title: 'Delete Confirmation',
      message,
      confirmText: 'Yes, Delete',
      cancelText: 'Cancel',
      onConfirm,
      onCancel,
      loading: false
    }
    showConfirmationModal.value = true
  }

  const handleConfirmationConfirm = () => {
    if (confirmationConfig.value.onConfirm) {
      confirmationConfig.value.onConfirm()
    }
    showConfirmationModal.value = false
  }

  const handleConfirmationCancel = () => {
    if (confirmationConfig.value.onCancel) {
      confirmationConfig.value.onCancel()
    }
    showConfirmationModal.value = false
  }

  // Cleanup function
  const cleanup = () => {
    // Clear all active toasts
    activeToasts.value.forEach(toastId => {
      toast.dismiss(toastId)
    })
    activeToasts.value = []
    
    // Reset confirmation modal
    showConfirmationModal.value = false
    confirmationConfig.value = {
      title: 'Confirm Action',
      message: '',
      confirmText: 'Yes, Continue',
      cancelText: 'Cancel',
      onConfirm: null,
      onCancel: null,
      loading: false
    }
    
    isMounted.value = false
  }

  // Lifecycle
  onMounted(() => {
    isMounted.value = true
  })

  onBeforeUnmount(() => {
    cleanup()
  })

  onUnmounted(() => {
    cleanup()
  })

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirmation,
    showDeleteConfirmation,
    // Confirmation modal state
    showConfirmationModal,
    confirmationConfig,
    handleConfirmationConfirm,
    handleConfirmationCancel,
    cleanup
  }
}

    showConfirmationModal.value = false
  }

  // Cleanup function
  const cleanup = () => {
    // Clear all active toasts
    activeToasts.value.forEach(toastId => {
      toast.dismiss(toastId)
    })
    activeToasts.value = []
    
    // Reset confirmation modal
    showConfirmationModal.value = false
    confirmationConfig.value = {
      title: 'Confirm Action',
      message: '',
      confirmText: 'Yes, Continue',
      cancelText: 'Cancel',
      onConfirm: null,
      onCancel: null,
      loading: false
    }
    
    isMounted.value = false
  }
  // Lifecycle
  onMounted(() => {
    isMounted.value = true
  })

  onBeforeUnmount(() => {
    cleanup()
  })

  onUnmounted(() => {
    cleanup()
  })

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showConfirmation,
    showDeleteConfirmation,
    // Confirmation modal state
    showConfirmationModal,
    confirmationConfig,
    handleConfirmationConfirm,
    handleConfirmationCancel,
    cleanup
  }
}
