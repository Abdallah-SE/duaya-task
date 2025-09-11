<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <FormField
        v-model="form.name"
        label="Full Name"
        placeholder="Enter full name"
        :error="errors.name"
        :required="true"
      />
      
      <FormField
        v-model="form.email"
        type="email"
        label="Email Address"
        placeholder="Enter email address"
        :error="errors.email"
        :required="true"
      />
    </div>
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <FormField
        v-model="form.password"
        type="password"
        label="Password"
        placeholder="Enter password"
        :error="errors.password"
        :required="isCreate"
        :help="isCreate ? 'Minimum 8 characters' : 'Leave blank to keep current password'"
      />
      
      <FormField
        v-model="form.password_confirmation"
        type="password"
        label="Confirm Password"
        placeholder="Confirm password"
        :error="errors.password_confirmation"
        :required="isCreate"
      />
    </div>
    
    <div v-if="showRoleField" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <SelectField
        v-model="form.role"
        label="Role"
        placeholder="Select a role"
        :options="roleOptions"
        :error="errors.role"
        :required="true"
      />
    </div>
    
    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
      <Button
        type="button"
        variant="secondary"
        @click="$emit('cancel')"
        :disabled="loading"
      >
        Cancel
      </Button>
      
      <Button
        type="submit"
        variant="primary"
        :loading="loading"
        :disabled="loading"
      >
        {{ isCreate ? 'Create User' : 'Update User' }}
      </Button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import FormField from '@/Components/Molecules/FormField.vue'
import SelectField from '@/Components/Molecules/SelectField.vue'
import Button from '@/Components/Atoms/Button.vue'

const props = defineProps({
  initialData: {
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

const emit = defineEmits(['submit', 'cancel'])

// Form data
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: ''
})

// Computed properties
const roleOptions = computed(() => props.availableRoles)

// Watch for initial data changes
watch(() => props.initialData, (newData) => {
  if (newData) {
    form.value = {
      name: newData?.name || '',
      email: newData?.email || '',
      password: '',
      password_confirmation: '',
      role: newData?.roles?.[0]?.name || ''
    }
  }
}, { immediate: true, deep: true })

// Methods
const handleSubmit = () => {
  const formData = { ...form.value }
  
  // Remove empty password fields for updates
  if (!props.isCreate) {
    if (!formData.password) {
      delete formData.password
      delete formData.password_confirmation
    }
  }
  
  emit('submit', formData)
}
</script>
