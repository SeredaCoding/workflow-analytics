<script setup>
const props = defineProps({
    checked: { type: [Boolean, Array], default: false },
    value: { default: null },
    modelValue: { default: undefined },
})

const emit = defineEmits(['update:modelValue', 'update:checked'])

function isChecked() {
    if (props.modelValue !== undefined) return props.modelValue
    if (Array.isArray(props.checked)) return props.checked.includes(props.value)
    return props.checked
}

function toggle() {
    if (props.modelValue !== undefined) {
        emit('update:modelValue', !props.modelValue)
    } else if (!Array.isArray(props.checked)) {
        emit('update:checked', !props.checked)
    }
}
</script>

<template>
    <div @click="toggle"
        class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition-colors cursor-pointer"
        :class="isChecked()
            ? 'bg-gray-900 dark:bg-white border-gray-900 dark:border-white'
            : 'border-gray-300 dark:border-gray-600'">
        <svg v-if="isChecked()" class="w-3 h-3 text-white dark:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
</template>
