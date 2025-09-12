<template>
    <div class="flex items-center mb-4">
        <IdleWarningIcon :level="warningLevel" />
        <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">
                {{ title }}
            </h3>
        </div>
    </div>
</template>

<script setup>
import IdleWarningIcon from '@/Components/Atoms/IdleWarningIcon.vue'

defineProps({
    warningLevel: {
        type: Number,
        required: true,
        validator: (value) => [1, 2, 3].includes(value)
    },
    title: {
        type: String,
        required: true
    }
})
</script>
