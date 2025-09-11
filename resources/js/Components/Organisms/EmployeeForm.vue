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
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <FormField
        v-model="form.job_title"
        label="Job Title"
        placeholder="Enter job title"
        :error="errors.job_title"
        :required="true"
      />
      
      <FormField
        v-model="form.department"
        label="Department"
        placeholder="Enter department"
        :error="errors.department"
        :required="true"
      />
    </div>
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <FormField
        v-model="form.hire_date"
        type="date"
        label="Hire Date"
        :error="errors.hire_date"
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
        {{ isCreate ? 'Create Employee' : 'Update Employee' }}
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
  }
})

const emit = defineEmits(['submit', 'cancel'])

// Form data
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  job_title: '',
  department: '',
  hire_date: ''
})

// Watch for initial data changes
watch(() => props.initialData, (newData) => {
  if (newData) {
    form.value = {
      name: newData?.name || '',
      email: newData?.email || '',
      password: '',
      password_confirmation: '',
      job_title: newData?.job_title || '',
      department: newData?.department || '',
      hire_date: newData?.hire_date || ''
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
