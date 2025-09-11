<template>
  <Modal
    :show="show"
    :title="modalTitle"
    size="lg"
    @close="$emit('close')"
  >
    <UserForm
      :initial-data="userData"
      :errors="errors"
      :loading="loading"
      :is-create="isCreate"
      :show-role-field="showRoleField"
      :available-roles="availableRoles"
      @submit="handleSubmit"
      @cancel="$emit('close')"
    />
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import Modal from '@/Components/Atoms/Modal.vue'
import UserForm from '@/Components/Organisms/UserForm.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  userData: {
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
  },
  showRoleField: {
    type: Boolean,
    default: true
  },
  availableRoles: {
    type: Array,
    default: () => [
      { value: 'employee', label: 'Employee' },
      { value: 'admin', label: 'Admin' }
    ]
  }
})

const emit = defineEmits(['close', 'submit'])

const modalTitle = computed(() => {
  return props.isCreate ? 'Create New User' : `Edit User: ${props.userData?.name || ''}`
})

const handleSubmit = (formData) => {
  emit('submit', formData)
}
</script>
