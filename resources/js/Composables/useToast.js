import { useToast } from 'vue-toastification'
import { ref } from 'vue'

export function useToastNotifications() {
  const toast = useToast()

  const showSuccess = (message) => {
    toast.success(message, {
      position: 'top-right',
      timeout: 4000,
    })
  }

  const showError = (message) => {
    toast.error(message, {
      position: 'top-right',
      timeout: 6000,
    })
  }

  const showWarning = (message) => {
    toast.warning(message, {
      position: 'top-right',
      timeout: 5000,
    })
  }

  const showInfo = (message) => {
    toast.info(message, {
      position: 'top-right',
      timeout: 4000,
    })
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
    handleConfirmationCancel
  }
}
