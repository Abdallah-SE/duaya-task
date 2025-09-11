<template>
  <Modal
    :show="show"
    :title="`User Details: ${userData?.name || ''}`"
    size="2xl"
    @close="$emit('close')"
  >
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- User Information -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-md font-medium text-gray-900 mb-3">User Information</h4>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3">
          <div>
            <dt class="text-sm font-medium text-gray-500">Name</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ userData?.name || 'N/A' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Email</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ userData?.email || 'N/A' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Role</dt>
            <dd class="mt-1">
              <Badge
                :variant="getRoleBadgeVariant(userData?.roles?.[0]?.name)"
                size="sm"
              >
                {{ userData?.roles?.[0]?.name || 'No Role' }}
              </Badge>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Created At</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(userData?.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Activity Count</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ userData?.activity_logs_count || 0 }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Idle Sessions</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ userData?.idle_sessions_count || 0 }}</dd>
          </div>
        </dl>
      </div>

      <!-- Recent Activity -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-md font-medium text-gray-900 mb-3">Recent Activity</h4>
        <div v-if="userData?.activity_logs && userData.activity_logs.length > 0" class="space-y-3 max-h-64 overflow-y-auto">
          <div
            v-for="activity in userData.activity_logs"
            :key="activity.id"
            class="border-l-4 border-blue-400 pl-3 py-2 bg-white rounded"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ activity.action }}</p>
                <p class="text-sm text-gray-500">
                  {{ activity.subject_type ? activity.subject_type.split('\\').pop() : 'System' }}
                  <span v-if="activity.subject_id">#{{ activity.subject_id }}</span>
                </p>
              </div>
              <span class="text-xs text-gray-400">{{ formatDate(activity.created_at) }}</span>
            </div>
            <div class="mt-1 text-xs text-gray-500">
              <span v-if="activity.device">{{ activity.device }}</span>
              <span v-if="activity.browser"> • {{ activity.browser }}</span>
              <span v-if="activity.ip_address"> • {{ activity.ip_address }}</span>
            </div>
          </div>
        </div>
        <div v-else class="text-sm text-gray-500">
          No recent activity found.
        </div>
      </div>

      <!-- Penalties -->
      <div v-if="userData?.penalties && userData.penalties.length > 0" class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-md font-medium text-gray-900 mb-3">Penalties</h4>
        <div class="space-y-3 max-h-32 overflow-y-auto">
          <div
            v-for="penalty in userData.penalties"
            :key="penalty.id"
            class="border-l-4 border-red-400 pl-3 py-2 bg-white rounded"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ penalty.reason }}</p>
                <p class="text-sm text-gray-500">Count: {{ penalty.count }}</p>
              </div>
              <span class="text-xs text-gray-400">{{ formatDate(penalty.date) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Idle Sessions -->
      <div v-if="userData?.idle_sessions && userData.idle_sessions.length > 0" class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-md font-medium text-gray-900 mb-3">Recent Idle Sessions</h4>
        <div class="space-y-3 max-h-32 overflow-y-auto">
          <div
            v-for="session in userData.idle_sessions"
            :key="session.id"
            class="border-l-4 border-yellow-400 pl-3 py-2 bg-white rounded"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="text-sm font-medium text-gray-900">
                  Duration: {{ formatDuration(session.duration) }}
                </p>
                <p class="text-sm text-gray-500">
                  Warning Count: {{ session.warning_count }}
                </p>
              </div>
              <span class="text-xs text-gray-400">{{ formatDate(session.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <Button
        variant="secondary"
        @click="$emit('close')"
      >
        Close
      </Button>
      <Button
        variant="primary"
        @click="$emit('edit')"
      >
        Edit User
      </Button>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import Modal from '@/Components/Atoms/Modal.vue'
import Badge from '@/Components/Atoms/Badge.vue'
import Button from '@/Components/Atoms/Button.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  userData: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'edit'])

const getRoleBadgeVariant = (role) => {
  switch (role) {
    case 'admin':
      return 'danger'
    case 'employee':
      return 'success'
    default:
      return 'default'
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString()
}

const formatDuration = (seconds) => {
  if (!seconds) return '0s'
  if (seconds < 60) {
    return `${seconds}s`
  } else if (seconds < 3600) {
    return `${Math.floor(seconds / 60)}m ${seconds % 60}s`
  } else {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    return `${hours}h ${minutes}m`
  }
}
</script>
