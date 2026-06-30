<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ label }}</div>
        <div class="flex items-baseline gap-1">
            <span class="text-3xl font-bold" :class="colorClass">{{ value }}</span>
            <span v-if="unit" class="text-lg text-gray-400">{{ unit }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: String,
    value: [String, Number],
    unit: String,
    color: String,
})

const colorClass = computed(() => {
    const map = {
        blue: 'text-blue-600 dark:text-blue-400',
        green: 'text-green-600 dark:text-green-400',
        red: 'text-red-600 dark:text-red-400',
        yellow: 'text-yellow-600 dark:text-yellow-400',
    }
    return map[props.color] || 'text-gray-900 dark:text-white'
})
</script>
