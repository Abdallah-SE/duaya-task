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
    
    <div v-if="isCreate" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <FormField
        v-model="form.password"
        type="password"
        label="Password"
        placeholder="Enter password"
        :error="errors.password"
        :required="true"
        help="Minimum 8 characters"
      />
      
      <FormField
        v-model="form.password_confirmation"
        type="password"
        label="Confirm Password"
        placeholder="Confirm password"
        :error="errors.password_confirmation"
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
import { ref, watch } from 'vue'
import FormField from '@/Components/Molecules/FormField.vue'
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
  password_confirmation: ''
})

// Watch for initial data changes
watch(() => props.initialData, (newData) => {
  if (newData) {
    form.value = {
      name: newData?.name || '',
      email: newData?.email || '',
      password: props.isCreate ? '' : undefined,
      password_confirmation: props.isCreate ? '' : undefined
    }
  }
}, { immediate: true, deep: true })

// Methods
const handleSubmit = () => {
  const formData = { ...form.value }
  
  // Remove password fields for updates (not needed for user updates)
  if (!props.isCreate) {
    delete formData.password
    delete formData.password_confirmation
  }
  
  emit('submit', formData)
}
</script>
