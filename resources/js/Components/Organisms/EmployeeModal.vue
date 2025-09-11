<template>
  <Modal
    :show="show"
    :title="modalTitle"
    size="lg"
    @close="$emit('close')"
  >
    <EmployeeForm
      :initial-data="employeeData"
      :errors="errors"
      :loading="loading"
      :is-create="isCreate"
      @submit="handleSubmit"
      @cancel="$emit('close')"
    />
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import Modal from '@/Components/Atoms/Modal.vue'
import EmployeeForm from '@/Components/Organisms/EmployeeForm.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  employeeData: {
    type: Object,
    default: () => ({})
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  },
  isCreate: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'submit'])

const modalTitle = computed(() => {
  return props.isCreate ? 'Create New Employee' : `Edit Employee: ${props.employeeData?.name || ''}`
})

const handleSubmit = (formData) => {
  emit('submit', formData)
}
</script>
