<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[10vh] bg-black/20 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="w-full max-w-4xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 max-h-[80vh] flex flex-col">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <h3 class="text-lg font-semibold truncate">{{ activity.title }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full shrink-0"
                        :class="statusClass(activity.status)">
                        {{ statusLabel(activity.status) }}
                    </span>
                </div>
                <button @click="$emit('close')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors shrink-0">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <div class="flex border-b border-gray-100 dark:border-gray-800 px-4 shrink-0">
                <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px"
                    :class="activeTab === tab.key
                        ? 'border-gray-900 dark:border-white text-gray-900 dark:text-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                    {{ tab.label }}
                </button>
            </div>

            <div class="p-4 overflow-y-auto flex-1">
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="w-6 h-6 border-2 border-gray-300 dark:border-gray-600 border-t-gray-900 dark:border-t-white rounded-full animate-spin" />
                </div>

                <div v-if="!loading && error" class="text-center py-12 text-red-500 text-sm">
                    {{ error }}
                </div>

                <div v-if="!loading && !error">
                    <div v-if="activeTab === 'details'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Categoria</div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: detail.activity?.category?.color }" />
                                    {{ detail.activity?.category?.name || '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Projeto</div>
                                <div>{{ detail.activity?.project?.name || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Módulo <span class="text-gray-400">(camada do sistema)</span></div>
                                <div>{{ detail.activity?.context?.name || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Descrição</div>
                                <div class="text-gray-700 dark:text-gray-300">{{ detail.activity?.description || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Prioridade</div>
                                <div>
                                    <span v-if="detail.activity?.priority" class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                                        :class="priorityClass(detail.activity.priority)">
                                        {{ priorityLabel(detail.activity.priority) }}
                                    </span>
                                    <span v-else class="text-gray-400">—</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Dificuldade</div>
                                <div>{{ difficultyLabel(detail.activity?.energy_level) || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Tipo</div>
                                <div>{{ detail.activity?.type === 'interruption' ? 'Interrupção' : 'Atividade' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Início</div>
                                <div class="font-mono">{{ formatDate(detail.activity?.started_at) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Fim</div>
                                <div class="font-mono">{{ formatDate(detail.activity?.ended_at) || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Duração</div>
                                <div class="font-mono">{{ formatDuration(detail.activity?.duration_minutes) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-0.5">Sessões</div>
                                <div>{{ detail.stats?.session_count || 1 }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'stats'" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                <div class="text-xs text-gray-500 mb-1">Tempo Total</div>
                                <div class="text-2xl font-bold">{{ formatDuration(detail.stats?.total_duration) }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                <div class="text-xs text-gray-500 mb-1">Sessões</div>
                                <div class="text-2xl font-bold">{{ detail.stats?.session_count }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                <div class="text-xs text-gray-500 mb-1">Média por Sessão</div>
                                <div class="text-2xl font-bold">{{ formatDuration(detail.stats?.average_session) }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                <div class="text-xs text-gray-500 mb-1">Tempo em Pausa</div>
                                <div class="text-2xl font-bold">{{ formatDuration(detail.stats?.total_pause_duration) }}</div>
                            </div>
                        </div>

                        <div v-if="detail.sessions?.length > 1">
                            <h4 class="text-sm font-medium text-gray-500 mb-3">Histórico de Sessões</h4>
                            <div class="space-y-1">
                                <div v-for="s in detail.sessions" :key="s.id"
                                    class="flex items-center justify-between py-2 px-3 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                            :class="s.status === 'in_progress' ? 'bg-green-500' : s.status === 'paused' ? 'bg-yellow-500' : 'bg-gray-400'" />
                                        <span class="text-gray-700 dark:text-gray-300 truncate">{{ s.title }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs text-gray-500 font-mono">
                                            {{ formatDate(s.started_at) }} → {{ formatDate(s.ended_at) || 'agora' }}
                                        </span>
                                        <span class="font-mono text-gray-700 dark:text-gray-300 w-16 text-right">{{ formatDuration(s.duration_minutes) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'project'">
                        <div v-if="detail.activity?.project" class="space-y-4 text-sm">
                            <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                                <div>
                                    <div class="text-xs text-gray-500 mb-0.5">Nome</div>
                                    <div class="font-medium">{{ detail.activity.project.name }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 mb-0.5">Sessões neste projeto</div>
                                    <div>{{ detail.sessions?.length || 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 mb-0.5">Tempo total no projeto</div>
                                    <div class="font-mono">{{ formatDuration(detail.stats?.total_duration) }}</div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-10 h-10 mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2Z"/></svg>
                            <p class="text-sm">Nenhum projeto associado a esta atividade.</p>
                        </div>
                    </div>

                    <div v-if="activeTab === 'timeline'" class="space-y-0 relative pl-8">
                        <div v-if="detail.events?.length" class="absolute left-3.5 top-2 bottom-2 w-px bg-gray-200 dark:bg-gray-700" />
                        <div v-for="(ev, i) in detail.events" :key="i"
                            class="relative pb-5 last:pb-0">
                            <div class="absolute -left-[18px] top-1 w-3 h-3 rounded-full border-2"
                                :class="timelineDotClass(ev.type)" />
                            <div class="flex items-baseline justify-between">
                                <span class="text-sm" :class="timelineLabelClass(ev.type)">
                                    {{ ev.description }}
                                </span>
                                <span class="text-xs text-gray-400 font-mono shrink-0 ml-4">
                                    {{ formatDate(ev.at) }}
                                </span>
                            </div>
                        </div>
                        <div v-if="!detail.events?.length" class="text-center py-8 text-gray-400 text-sm">
                            Nenhum evento registrado.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    activity: Object,
})

const emit = defineEmits(['close'])

const tabs = [
    { key: 'details', label: 'Detalhes' },
    { key: 'stats', label: 'Estatísticas' },
    { key: 'project', label: 'Projeto' },
    { key: 'timeline', label: 'Linha do Tempo' },
]

const activeTab = ref('details')
const loading = ref(true)
const error = ref(null)
const detail = reactive({
    activity: null,
    sessions: [],
    events: [],
    stats: null,
})

onMounted(async () => {
    try {
        const res = await axios.get(`/api/activities/${props.activity.id}/detail`)
        Object.assign(detail, res.data)
    } catch (e) {
        error.value = 'Erro ao carregar detalhes da atividade.'
        console.error(e)
    } finally {
        loading.value = false
    }
})

function formatDate(date) {
    if (!date) return null
    return new Date(date).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

function formatDuration(minutes) {
    if (!minutes && minutes !== 0) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}

function statusClass(status) {
    const map = {
        in_progress: 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400',
        paused: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400',
        completed: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
    }
    return map[status] || ''
}

function statusLabel(status) {
    const map = {
        in_progress: 'Em andamento',
        paused: 'Pausado',
        completed: 'Concluído',
    }
    return map[status] || status
}

function priorityClass(priority) {
    const map = {
        low: 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400',
        normal: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        medium: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400',
        high: 'bg-orange-100 dark:bg-orange-950/30 text-orange-700 dark:text-orange-400',
        critical: 'bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400',
    }
    return map[priority] || ''
}

function priorityLabel(priority) {
    const map = {
        low: 'Baixa',
        normal: 'Normal',
        medium: 'Média',
        high: 'Alta',
        critical: 'Crítica',
    }
    return map[priority] || priority
}

function difficultyLabel(level) {
    if (!level) return null
    const map = {
        1: '1 — Muito fácil',
        2: '2 — Fácil',
        3: '3 — Normal',
        4: '4 — Difícil',
        5: '5 — Muito difícil',
    }
    return map[level] || level
}

function timelineDotClass(type) {
    const map = {
        started:  'bg-green-500 border-green-500',
        resumed:  'bg-green-500 border-green-500',
        paused:   'bg-yellow-400 border-yellow-400',
        completed:'bg-gray-400 border-gray-400',
        reopened: 'bg-blue-500 border-blue-500',
    }
    return map[type] || 'bg-gray-400 border-gray-400'
}

function timelineLabelClass(type) {
    const map = {
        started:  'text-green-700 dark:text-green-400 font-medium',
        resumed:  'text-green-700 dark:text-green-400',
        paused:   'text-yellow-700 dark:text-yellow-400',
        completed:'text-gray-500',
        reopened: 'text-blue-700 dark:text-blue-400 font-medium',
    }
    return map[type] || ''
}
</script>
