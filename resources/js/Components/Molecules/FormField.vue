<template>
  <div class="space-y-1">
    <Input
      :model-value="modelValue"
      :type="type"
      :label="label"
      :placeholder="placeholder"
      :error="error"
      :help="help"
      :disabled="disabled"
      :required="required"
      :icon="icon"
      :size="size"
      @update:model-value="$emit('update:modelValue', $event)"
      @blur="$emit('blur', $event)"
      @focus="$emit('focus', $event)"
    />
  </div>
</template>

<script setup>
import Input from '@/Components/Atoms/Input.vue'

defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  help: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  icon: {
    type: [String, Object],
    default: null
  },
  size: {
    type: String,
    default: 'md'
  }
})

defineEmits(['update:modelValue', 'blur', 'focus'])
</script>
