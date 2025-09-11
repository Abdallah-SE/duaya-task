<template>
  <div class="statistics-cards">
    <div class="cards-grid">
      <div
        v-for="(stat, index) in statsList"
        :key="stat.key"
        class="stat-card"
        :class="getCardClasses(stat)"
      >
        <div class="card-content">
          <div class="card-icon">
            <component :is="getIconComponent(stat.icon)" class="w-8 h-8" />
          </div>
          
          <div class="card-text">
            <p class="card-label">{{ stat.label }}</p>
            <div class="card-value-container">
              <p class="card-value">
                <span v-if="loading" class="animate-pulse bg-gray-300 rounded h-8 w-16"></span>
                <span v-else>{{ formatValue(stat.value) }}</span>
              </p>
              <div v-if="stat.percentage !== undefined && !loading" class="card-percentage">
                <span :class="getPercentageClasses(stat.percentage)">
                  {{ stat.percentage > 0 ? '+' : '' }}{{ stat.percentage }}%
                </span>
                <span class="text-xs text-gray-500 ml-1">vs last period</span>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="stat.trend && !loading" class="card-trend">
          <component 
            :is="getTrendIcon(stat.trend.direction)" 
            :class="getTrendClasses(stat.trend.direction)"
            class="w-4 h-4"
          />
          <span :class="getTrendClasses(stat.trend.direction)" class="text-xs">
            {{ stat.trend.value }}
          </span>
        </div>
        
        <button
          v-if="stat.clickable"
          @click="$emit('card-click', stat.key)"
          class="card-action"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
    
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['refresh', 'card-click'])

const statsList = computed(() => [
  {
    key: 'totalUsers',
    label: 'Total Users',
    value: props.stats.totalUsers || 0,
    icon: 'users',
    color: 'blue',
    clickable: true,
    trend: {
      direction: 'up',
      value: '+12%'
    }
  },
  {
    key: 'activeUsers',
    label: 'Active Users',
    value: props.stats.activeUsers || 0,
    icon: 'user-check',
    color: 'green',
    percentage: 8.2,
    clickable: true,
    trend: {
      direction: 'up',
      value: '+5%'
    }
  },
  {
    key: 'idleSessions',
    label: 'Idle Sessions',
    value: props.stats.idleSessions || 0,
    icon: 'clock',
    color: 'yellow',
    percentage: -2.1,
    clickable: true,
    trend: {
      direction: 'down',
      value: '-3%'
    }
  },
  {
    key: 'penalties',
    label: 'Penalties',
    value: props.stats.penalties || 0,
    icon: 'exclamation-triangle',
    color: 'red',
    percentage: -15.3,
    clickable: true,
    trend: {
      direction: 'down',
      value: '-8%'
    }
  },
  {
    key: 'recentActivities',
    label: 'Recent Activities',
    value: props.stats.recentActivities || 0,
    icon: 'activity',
    color: 'purple',
    percentage: 12.5,
    clickable: true,
    trend: {
      direction: 'up',
      value: '+15%'
    }
  },
  {
    key: 'totalActivities',
    label: 'Total Activities',
    value: props.stats.totalActivities || 0,
    icon: 'chart-bar',
    color: 'indigo',
    percentage: 6.8,
    clickable: true,
    trend: {
      direction: 'up',
      value: '+7%'
    }
  }
])

const getCardClasses = (stat) => {
  const colorClasses = {
    blue: 'bg-blue-50 border-blue-200',
    green: 'bg-green-50 border-green-200',
    yellow: 'bg-yellow-50 border-yellow-200',
    red: 'bg-red-50 border-red-200',
    purple: 'bg-purple-50 border-purple-200',
    indigo: 'bg-indigo-50 border-indigo-200'
  }
  
  return `stat-card-${stat.color} ${colorClasses[stat.color] || colorClasses.blue}`
}

const getIconComponent = (iconName) => {
  const icons = {
    users: 'UsersIcon',
    'user-check': 'UserCheckIcon',
    clock: 'ClockIcon',
    'exclamation-triangle': 'ExclamationTriangleIcon',
    activity: 'ActivityIcon',
    'chart-bar': 'ChartBarIcon'
  }
  
  return icons[iconName] || 'div'
}

const getPercentageClasses = (percentage) => {
  if (percentage > 0) return 'text-green-600'
  if (percentage < 0) return 'text-red-600'
  return 'text-gray-600'
}

const getTrendClasses = (direction) => {
  return direction === 'up' ? 'text-green-600' : 'text-red-600'
}

const getTrendIcon = (direction) => {
  return direction === 'up' ? 'ArrowUpIcon' : 'ArrowDownIcon'
}

const formatValue = (value) => {
  if (typeof value === 'number') {
    if (value >= 1000000) {
      return (value / 1000000).toFixed(1) + 'M'
    } else if (value >= 1000) {
      return (value / 1000).toFixed(1) + 'K'
    }
    return value.toLocaleString()
  }
  return value
}
</script>

<style scoped>
.statistics-cards {
  @apply relative;
}

.cards-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4;
}

.stat-card {
  @apply relative bg-white overflow-hidden shadow-sm border rounded-lg p-6 hover:shadow-md transition-shadow duration-200;
}

.stat-card-blue {
  @apply bg-blue-50 border-blue-200;
}

.stat-card-green {
  @apply bg-green-50 border-green-200;
}

.stat-card-yellow {
  @apply bg-yellow-50 border-yellow-200;
}

.stat-card-red {
  @apply bg-red-50 border-red-200;
}

.stat-card-purple {
  @apply bg-purple-50 border-purple-200;
}

.stat-card-indigo {
  @apply bg-indigo-50 border-indigo-200;
}

.card-content {
  @apply flex items-center;
}

.card-icon {
  @apply flex-shrink-0;
}

.card-icon svg {
  @apply text-gray-600;
}

.stat-card-blue .card-icon svg {
  @apply text-blue-600;
}

.stat-card-green .card-icon svg {
  @apply text-green-600;
}

.stat-card-yellow .card-icon svg {
  @apply text-yellow-600;
}

.stat-card-red .card-icon svg {
  @apply text-red-600;
}

.stat-card-purple .card-icon svg {
  @apply text-purple-600;
}

.stat-card-indigo .card-icon svg {
  @apply text-indigo-600;
}

.card-text {
  @apply ml-4 flex-1 min-w-0;
}

.card-label {
  @apply text-sm font-medium text-gray-600 truncate;
}

.card-value-container {
  @apply flex items-baseline space-x-2;
}

.card-value {
  @apply text-2xl font-bold text-gray-900;
}

.card-percentage {
  @apply flex items-center text-sm;
}

.card-trend {
  @apply absolute top-4 right-4 flex items-center space-x-1;
}

.card-action {
  @apply absolute bottom-4 right-4 p-1 text-gray-400 hover:text-gray-600 transition-colors duration-200;
}

.loading-overlay {
  @apply absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10;
}

.loading-spinner {
  @apply flex items-center justify-center;
}
</style>
