<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h3 class="modal-title">
          {{ mode === 'create' ? 'Add New User' : mode === 'edit' ? 'Edit User' : 'View User' }}
        </h3>
        <button @click="closeModal" class="modal-close">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-body">
        <div class="form-grid">
          <!-- Name -->
          <div class="form-group">
            <label class="form-label">Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              :disabled="mode === 'view'"
              class="form-input"
              :class="{ 'disabled': mode === 'view' }"
            />
            <div v-if="errors.name" class="form-error">{{ errors.name }}</div>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label class="form-label">Email *</label>
            <input
              v-model="form.email"
              type="email"
              required
              :disabled="mode === 'view'"
              class="form-input"
              :class="{ 'disabled': mode === 'view' }"
            />
            <div v-if="errors.email" class="form-error">{{ errors.email }}</div>
          </div>

          <!-- Password -->
          <div v-if="mode !== 'view'" class="form-group">
            <label class="form-label">
              {{ mode === 'create' ? 'Password *' : 'New Password' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="mode === 'create'"
              class="form-input"
            />
            <div v-if="errors.password" class="form-error">{{ errors.password }}</div>
          </div>

          <!-- Role -->
          <div class="form-group">
            <label class="form-label">Role *</label>
            <select
              v-model="form.role"
              required
              :disabled="mode === 'view'"
              class="form-select"
              :class="{ 'disabled': mode === 'view' }"
            >
              <option value="">Select Role</option>
              <option value="employee">Employee</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
            </select>
            <div v-if="errors.role" class="form-error">{{ errors.role }}</div>
          </div>

          <!-- Department (for employees) -->
          <div v-if="form.role === 'employee'" class="form-group">
            <label class="form-label">Department</label>
            <select
              v-model="form.department"
              :disabled="mode === 'view'"
              class="form-select"
              :class="{ 'disabled': mode === 'view' }"
            >
              <option value="">Select Department</option>
              <option value="it">IT</option>
              <option value="hr">Human Resources</option>
              <option value="finance">Finance</option>
              <option value="marketing">Marketing</option>
              <option value="sales">Sales</option>
            </select>
          </div>

          <!-- Job Title (for employees) -->
          <div v-if="form.role === 'employee'" class="form-group">
            <label class="form-label">Job Title</label>
            <input
              v-model="form.job_title"
              type="text"
              :disabled="mode === 'view'"
              class="form-input"
              :class="{ 'disabled': mode === 'view' }"
            />
          </div>

          <!-- Status -->
          <div class="form-group">
            <label class="form-label">Status</label>
            <div class="flex items-center space-x-4">
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="radio"
                  :value="true"
                  :disabled="mode === 'view'"
                  class="form-radio"
                />
                <span class="ml-2 text-sm text-gray-700">Active</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="radio"
                  :value="false"
                  :disabled="mode === 'view'"
                  class="form-radio"
                />
                <span class="ml-2 text-sm text-gray-700">Inactive</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Employee-specific fields -->
        <div v-if="form.role === 'employee'" class="employee-section">
          <h4 class="section-title">Employee Details</h4>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Employee ID</label>
              <input
                v-model="form.employee_id"
                type="text"
                :disabled="mode === 'view'"
                class="form-input"
                :class="{ 'disabled': mode === 'view' }"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                :disabled="mode === 'view'"
                class="form-input"
                :class="{ 'disabled': mode === 'view' }"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Hire Date</label>
              <input
                v-model="form.hire_date"
                type="date"
                :disabled="mode === 'view'"
                class="form-input"
                :class="{ 'disabled': mode === 'view' }"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Salary</label>
              <input
                v-model="form.salary"
                type="number"
                step="0.01"
                :disabled="mode === 'view'"
                class="form-input"
                :class="{ 'disabled': mode === 'view' }"
              />
            </div>
          </div>
        </div>
      </form>

      <div class="modal-footer">
        <button
          type="button"
          @click="closeModal"
          class="btn-secondary"
        >
          {{ mode === 'view' ? 'Close' : 'Cancel' }}
        </button>
        <button
          v-if="mode !== 'view'"
          type="submit"
          @click="handleSubmit"
          :disabled="loading"
          class="btn-primary"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Saving...' : mode === 'create' ? 'Create User' : 'Update User' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create', // 'create', 'edit', 'view'
    validator: (value) => ['create', 'edit', 'view'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'save'])

// Form data
const form = ref({
  name: '',
  email: '',
  password: '',
  role: '',
  department: '',
  job_title: '',
  is_active: true,
  employee_id: '',
  phone: '',
  hire_date: '',
  salary: ''
})

const errors = ref({})

// Computed
const show = computed(() => props.show)

// Methods
const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    password: '',
    role: '',
    department: '',
    job_title: '',
    is_active: true,
    employee_id: '',
    phone: '',
    hire_date: '',
    salary: ''
  }
  errors.value = {}
}

const populateForm = (user) => {
  if (user) {
    form.value = {
      name: user.name || '',
      email: user.email || '',
      password: '',
      role: user.roles?.[0]?.name || '',
      department: user.employee?.department || '',
      job_title: user.employee?.job_title || '',
      is_active: user.is_active !== false,
      employee_id: user.employee?.employee_id || '',
      phone: user.employee?.phone || '',
      hire_date: user.employee?.hire_date || '',
      salary: user.employee?.salary || ''
    }
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!form.value.name.trim()) {
    errors.value.name = 'Name is required'
  }
  
  if (!form.value.email.trim()) {
    errors.value.email = 'Email is required'
  } else if (!/\S+@\S+\.\S+/.test(form.value.email)) {
    errors.value.email = 'Email is invalid'
  }
  
  if (props.mode === 'create' && !form.value.password.trim()) {
    errors.value.password = 'Password is required'
  }
  
  if (!form.value.role) {
    errors.value.role = 'Role is required'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleSubmit = () => {
  if (validateForm()) {
    emit('save', { ...form.value })
  }
}

const closeModal = () => {
  emit('close')
}

// Watchers
watch(() => props.user, (newUser) => {
  if (newUser) {
    populateForm(newUser)
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.show, (newShow) => {
  if (newShow && props.user) {
    populateForm(props.user)
  } else if (!newShow) {
    resetForm()
  }
})
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50;
}

.modal-container {
  @apply relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white;
}

.modal-header {
  @apply flex items-center justify-between pb-4 border-b border-gray-200;
}

.modal-title {
  @apply text-lg font-medium text-gray-900;
}

.modal-close {
  @apply text-gray-400 hover:text-gray-600;
}

.modal-body {
  @apply py-6;
}

.form-grid {
  @apply grid grid-cols-1 md:grid-cols-2 gap-4;
}

.form-group {
  @apply space-y-1;
}

.form-label {
  @apply block text-sm font-medium text-gray-700;
}

.form-input,
.form-select {
  @apply block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm;
}

.form-input.disabled,
.form-select.disabled {
  @apply bg-gray-100 cursor-not-allowed;
}

.form-radio {
  @apply text-indigo-600 focus:ring-indigo-500;
}

.form-error {
  @apply text-sm text-red-600;
}

.employee-section {
  @apply mt-6 pt-6 border-t border-gray-200;
}

.section-title {
  @apply text-md font-medium text-gray-900 mb-4;
}

.modal-footer {
  @apply flex justify-end space-x-3 pt-4 border-t border-gray-200;
}

.btn-primary {
  @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50;
}

.btn-secondary {
  @apply inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500;
}
</style>
