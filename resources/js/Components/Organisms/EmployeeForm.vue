<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <SelectField
        v-if="isCreate"
        v-model="form.user_id"
        label="Select User"
        placeholder="Choose a user"
        :error="errors.user_id"
        :required="isCreate"
        :options="userOptions"
      />
      
      <FormField
        v-model="form.job_title"
        label="Job Title"
        placeholder="Enter job title"
        :error="errors.job_title"
        :required="true"
      />
    </div>
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <FormField
        v-model="form.department"
        label="Department"
        placeholder="Enter department"
        :error="errors.department"
        :required="true"
      />
      
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
  },
  users: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['submit', 'cancel'])

// Form data
const form = ref({
  user_id: '',
  job_title: '',
  department: '',
  hire_date: ''
})

// Helper function to format date for HTML date input (YYYY-MM-DD)
const formatDateForInput = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toISOString().split('T')[0]
}

// User options for select field
const userOptions = computed(() => {
  return props.users.map(user => ({
    value: user.id,
    label: `${user.name} (${user.email})`
  }))
})

// Watch for initial data changes
watch(() => props.initialData, (newData) => {
  if (newData) {
    form.value = {
      user_id: newData?.user_id || '',
      job_title: newData?.job_title || '',
      department: newData?.department || '',
      hire_date: newData?.hire_date ? formatDateForInput(newData.hire_date) : ''
    }
  }
}, { immediate: true, deep: true })

// Methods
const handleSubmit = () => {
  const formData = { ...form.value }
  
  // Remove user_id for updates since we don't want to change the user
  if (!props.isCreate) {
    delete formData.user_id
  }
  
  emit('submit', formData)
}
</script>
