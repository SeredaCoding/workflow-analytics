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

            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                <div v-if="filteredTopics.length === 0" class="text-center py-12 text-sm text-gray-400">
                    Nenhuma pergunta encontrada.
                </div>
                <div v-for="topic in filteredTopics" :key="topic.id">
                    <button @click="toggleTopic(topic.id)"
                        class="w-full text-left py-2.5 px-3 rounded-lg text-sm font-medium flex items-center justify-between gap-2 transition-colors"
                        :class="openTopics.has(topic.id)
                            ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                        <span>{{ topic.name }} ({{ topic.faqs?.length || 0 }})</span>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': openTopics.has(topic.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div v-if="openTopics.has(topic.id)" class="ml-3 space-y-0.5 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                        <div v-for="faq in topic.faqs" :key="faq.id" class="border-b border-gray-100 dark:border-gray-800 last:border-0">
                            <button @click="toggleFaq(faq.id)"
                                class="w-full text-left py-2 flex items-center justify-between gap-4 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                {{ faq.question }}
                                <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': openFaqs.has(faq.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div v-if="openFaqs.has(faq.id)" class="pb-3 text-sm text-gray-500 dark:text-gray-400 leading-relaxed whitespace-pre-wrap">
                                {{ faq.answer }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close'])

const props = defineProps({
    faqs: { type: Array, default: () => [] },
})

const search = ref('')
const openTopics = ref(new Set())
const openFaqs = ref(new Set())

const groupedByTopic = computed(() => {
    const map = {}
    for (const faq of props.faqs) {
        const tid = faq.faq_topic_id || 0
        if (!map[tid]) {
            map[tid] = {
                id: tid || `none-${Math.random()}`,
                name: faq.topic?.name || 'Sem tópico',
                faqs: [],
            }
        }
        map[tid].faqs.push(faq)
    }
    return Object.values(map).sort((a, b) => a.name.localeCompare(b.name))
})

const filteredTopics = computed(() => {
    const q = search.value.toLowerCase().trim()
    if (!q) return groupedByTopic.value
    return groupedByTopic.value
        .map(t => ({
            ...t,
            faqs: t.faqs.filter(f =>
                f.question.toLowerCase().includes(q) ||
                f.answer.toLowerCase().includes(q)
            ),
        }))
        .filter(t => t.faqs.length > 0)
})

function toggleTopic(id) {
    if (openTopics.value.has(id)) {
        openTopics.value.delete(id)
    } else {
        openTopics.value.add(id)
    }
}

function toggleFaq(id) {
    if (openFaqs.value.has(id)) {
        openFaqs.value.delete(id)
    } else {
        openFaqs.value.add(id)
        axios.post(`/api/faqs/${id}/click`).catch(() => {})
    }
}
</script>
