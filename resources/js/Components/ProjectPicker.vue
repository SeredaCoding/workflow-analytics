<template>
    <div class="relative" ref="container">
        <div v-if="selected && !isOpen"
            class="flex items-center gap-2 px-3 py-1.5 text-sm rounded-lg border transition-colors cursor-pointer"
            :class="'border-gray-900 dark:border-white bg-gray-900 dark:bg-white text-white dark:text-gray-900'"
            @click="isOpen = true">
            <span class="truncate">{{ selected.name }}</span>
            <button type="button" @click.stop="clear" class="shrink-0 hover:opacity-70">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div v-else class="relative">
            <input ref="input" v-model="query" type="text" placeholder="Buscar projeto..."
                @focus="isOpen = true" @input="onInput" @keydown="onKeydown"
                class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pl-9 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
            <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div v-if="isOpen"
            class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-64 overflow-y-auto">
            <button v-for="(proj, index) in filtered" :key="proj.id" type="button"
                @mousedown.prevent="select(proj)"
                class="w-full text-left px-3 py-2 text-sm transition-colors"
                :class="index === highlightedIndex
                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                <span class="inline-block w-2 h-2 rounded-full mr-2 shrink-0" :style="{ backgroundColor: proj.color }" />
                {{ proj.name }}
            </button>
            <div v-if="filtered.length === 0 && !showCreateOption" class="px-3 py-4 text-sm text-gray-400 text-center">
                Nenhum projeto encontrado.
            </div>
            <button v-if="showCreateOption" type="button"
                @mousedown.prevent="create"
                class="w-full text-left px-3 py-2 text-sm border-t border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                + Criar "{{ query }}"
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    projects: { type: Array, required: true },
    modelValue: { type: Number, default: null },
    allowCreate: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'create'])

const container = ref(null)
const input = ref(null)
const query = ref('')
const isOpen = ref(false)
const highlightedIndex = ref(0)

const selected = computed(() => {
    if (!props.modelValue) return null
    return props.projects.find(p => p.id === props.modelValue) || null
})

const filtered = computed(() => {
    const q = query.value.toLowerCase().trim()
    if (!q) return props.projects
    return props.projects.filter(p => p.name.toLowerCase().includes(q))
})

const exactMatch = computed(() => {
    const q = query.value.toLowerCase().trim()
    if (!q) return false
    return props.projects.some(p => p.name.toLowerCase() === q)
})

const showCreateOption = computed(() => {
    return props.allowCreate && query.value.trim() && !exactMatch.value
})

watch(isOpen, (open) => {
    if (open) {
        highlightedIndex.value = 0
        if (selected.value) {
            query.value = ''
        }
        requestAnimationFrame(() => input.value?.focus())
    }
})

watch(filtered, () => {
    highlightedIndex.value = 0
})

function onInput() {
    isOpen.value = true
    highlightedIndex.value = 0
}

function onKeydown(e) {
    if (e.key === 'ArrowDown') {
        e.preventDefault()
        const max = filtered.value.length + (showCreateOption.value ? 1 : 0)
        highlightedIndex.value = (highlightedIndex.value + 1) % max
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        const max = filtered.value.length + (showCreateOption.value ? 1 : 0)
        highlightedIndex.value = (highlightedIndex.value - 1 + max) % max
    } else if (e.key === 'Enter') {
        e.preventDefault()
        if (highlightedIndex.value < filtered.value.length) {
            select(filtered.value[highlightedIndex.value])
        } else if (showCreateOption.value) {
            create()
        }
    } else if (e.key === 'Escape') {
        isOpen.value = false
    }
}

const handleClickOutside = (e) => {
    if (isOpen.value && container.value && !container.value.contains(e.target)) {
        isOpen.value = false
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

function select(proj) {
    emit('update:modelValue', proj.id)
    query.value = proj.name
    isOpen.value = false
}

function clear() {
    emit('update:modelValue', null)
    query.value = ''
    isOpen.value = true
    requestAnimationFrame(() => input.value?.focus())
}

function create() {
    emit('create', query.value.trim())
    isOpen.value = false
}
</script>
