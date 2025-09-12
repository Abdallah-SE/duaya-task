<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <!-- Warning Header -->
            <IdleWarningHeader 
                :warning-level="warningLevel" 
                :title="title" 
            />

            <!-- Warning Message -->
            <div class="mb-4">
                <p class="text-sm text-gray-600">{{ message }}</p>
            </div>

            <!-- Progress Bar for Multiple Warnings -->
            <div v-if="warningLevel > 1" class="mb-4">
                <IdleProgressBar 
                    :current="warningLevel"
                    :total="maxWarnings"
                    label="Warning Progress"
                    :variant="warningLevel === 2 ? 'warning' : 'danger'"
                />
            </div>

            <!-- Countdown Timer -->
            <div class="mb-6">
                <IdleCountdown 
                    :countdown="countdown"
                    :max-countdown="10"
                    :message="countdownMessage"
                />
            </div>

            <!-- Action Buttons -->
            <IdleWarningActions 
                :warning-level="warningLevel"
                @acknowledge="$emit('acknowledge')"
                @force-logout="$emit('force-logout')"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import IdleWarningHeader from '@/Components/Molecules/IdleWarningHeader.vue'
import IdleProgressBar from '@/Components/Atoms/IdleProgressBar.vue'
import IdleCountdown from '@/Components/Atoms/IdleCountdown.vue'
import IdleWarningActions from '@/Components/Molecules/IdleWarningActions.vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    warningLevel: {
        type: Number,
        required: true,
        validator: (value) => [1, 2, 3].includes(value)
    },
    title: {
        type: String,
        required: true
    },
    message: {
        type: String,
        required: true
    },
    maxWarnings: {
        type: Number,
        default: 3
    },
    countdown: {
        type: Number,
        default: 10
    }
})

const countdownMessage = computed(() => {
    return 'This warning will automatically proceed in {countdown} seconds...'
})

defineEmits(['acknowledge', 'force-logout'])
</script>
