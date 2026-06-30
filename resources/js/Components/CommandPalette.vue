<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[20vh] bg-black/50" @click.self="$emit('close')">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input ref="searchInput" v-model="query" placeholder="O que deseja fazer?"
                    class="w-full pl-10 pr-4 py-3.5 bg-transparent border-b border-gray-200 dark:border-gray-800 text-sm outline-none placeholder-gray-400"
                    @keydown.enter="execute(selectedIndex)"
                    @keydown.escape="$emit('close')"
                    @keydown.down.prevent="selectedIndex = Math.min(selectedIndex + 1, filteredCommands.length - 1)"
                    @keydown.up.prevent="selectedIndex = Math.max(selectedIndex - 1, 0)" />
            </div>
            <div class="max-h-72 overflow-y-auto py-1">
                <button v-for="(cmd, i) in filteredCommands" :key="cmd.id"
                    @click="execute(i)" @mouseenter="selectedIndex = i"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-left transition-colors"
                    :class="i === selectedIndex
                        ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                        : 'text-gray-600 dark:text-gray-400'">
                    <span v-html="cmd.icon" class="w-4 h-4 shrink-0" />
                    <span>{{ cmd.label }}</span>
                    <span v-if="cmd.shortcut" class="ml-auto text-xs text-gray-400 font-mono">{{ cmd.shortcut }}</span>
                </button>
                <div v-if="filteredCommands.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                    Nenhum resultado para "{{ query }}"
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const emit = defineEmits(['close', 'quick-start', 'manual-entry', 'interrupt'])

const page = usePage()

const query = ref('')
const selectedIndex = ref(0)
const searchInput = ref(null)

const commands = [
    { id: 'dashboard', label: 'Dashboard', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>', action: () => router.visit('/') },
    { id: 'activities', label: 'Atividades', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>', action: () => router.visit('/activities') },
    { id: 'stats', label: 'Estatísticas', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>', action: () => router.visit('/stats') },
    { id: 'quick-start', label: 'Nova Atividade', shortcut: 'Q', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>', action: () => emit('quick-start') },
    { id: 'manual-entry', label: 'Registrar Atividade', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>', action: () => emit('manual-entry') },
    { id: 'interrupt', label: 'Interromper', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>', action: () => emit('interrupt') },
]

const filteredCommands = computed(() => {
    const q = query.value.toLowerCase().trim()
    if (!q) return commands
    return commands.filter(c => c.label.toLowerCase().includes(q))
})

function execute(index) {
    const cmd = filteredCommands.value[index]
    if (!cmd) return
    emit('close')
    cmd.action()
}

onMounted(() => {
    nextTick(() => searchInput.value?.focus())
})
</script>
