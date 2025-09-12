<template>
    <div class="w-full">
        <div class="flex justify-between text-sm text-gray-600 mb-1">
            <span>{{ label }}</span>
            <span>{{ current }}/{{ total }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
                class="h-2 rounded-full transition-all duration-300"
                :class="progressBarClass"
                :style="{ width: `${progressPercentage}%` }"
            ></div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    current: {
        type: Number,
        required: true
    },
    total: {
        type: Number,
        required: true
    },
    label: {
        type: String,
        default: 'Progress'
    },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'warning', 'danger'].includes(value)
    }
})

const progressPercentage = computed(() => {
    return Math.min((props.current / props.total) * 100, 100)
})

const progressBarClass = computed(() => {
    switch (props.variant) {
        case 'warning':
            return 'bg-orange-500'
        case 'danger':
            return 'bg-red-500'
        default:
            return 'bg-blue-500'
    }
})
</script>
