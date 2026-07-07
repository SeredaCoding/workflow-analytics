<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[8vh] bg-black/20 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 max-h-[84vh] flex flex-col">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Perguntas Frequentes</h3>
                <button @click="$emit('close')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <div class="p-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                <div class="relative">
                    <input v-model="search" type="text" placeholder="Buscar perguntas..."
                        class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pl-9 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-1">
                <div v-if="filteredFaqs.length === 0" class="text-center py-12 text-sm text-gray-400">
                    Nenhuma pergunta encontrada.
                </div>
                <div v-for="faq in filteredFaqs" :key="faq.id" class="border-b border-gray-100 dark:border-gray-800 last:border-0">
                    <button @click="toggle(faq.id)"
                        class="w-full text-left py-3 flex items-center justify-between gap-4 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                        {{ faq.question }}
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': openIds.has(faq.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div v-if="openIds.has(faq.id)" class="pb-3 text-sm text-gray-500 dark:text-gray-400 leading-relaxed whitespace-pre-wrap">
                        {{ faq.answer }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const emit = defineEmits(['close'])

const props = defineProps({
    faqs: { type: Array, default: () => [] },
})

const search = ref('')
const openIds = ref(new Set())

const filteredFaqs = computed(() => {
    const q = search.value.toLowerCase().trim()
    if (!q) return props.faqs
    return props.faqs.filter(f =>
        f.question.toLowerCase().includes(q) ||
        f.answer.toLowerCase().includes(q)
    )
})

function toggle(id) {
    if (openIds.value.has(id)) {
        openIds.value.delete(id)
    } else {
        openIds.value.add(id)
    }
}
</script>
