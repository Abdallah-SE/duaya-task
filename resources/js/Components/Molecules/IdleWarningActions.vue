<template>
    <div class="flex justify-end space-x-3">
        <button
            v-if="warningLevel < 3"
            @click="$emit('acknowledge')"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            I'm Still Here
        </button>
        <button
            v-if="warningLevel >= 3"
            @click="$emit('force-logout')"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
            Logout Now
        </button>
    </div>
</template>

<script setup>
defineProps({
    warningLevel: {
        type: Number,
        required: true,
        validator: (value) => [1, 2, 3].includes(value)
    }
})

defineEmits(['acknowledge', 'force-logout'])
</script>
