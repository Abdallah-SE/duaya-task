<template>
    <div class="text-center">
        <p class="text-sm text-gray-500 mb-2">
            {{ message }}
        </p>
        <div class="w-full bg-gray-200 rounded-full h-1">
            <div 
                class="h-1 rounded-full bg-blue-500 transition-all duration-1000"
                :style="{ width: `${countdownPercentage}%` }"
            ></div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    countdown: {
        type: Number,
        required: true
    },
    maxCountdown: {
        type: Number,
        default: 10
    },
    message: {
        type: String,
        default: 'This warning will automatically proceed in {countdown} seconds...'
    }
})

const countdownPercentage = computed(() => {
    return (props.countdown / props.maxCountdown) * 100
})

const displayMessage = computed(() => {
    return props.message.replace('{countdown}', props.countdown)
})
</script>
