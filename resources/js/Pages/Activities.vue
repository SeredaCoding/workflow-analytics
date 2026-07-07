<template>
    <AppLayout :in-progress="inProgress">
        <div class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold">Atividades</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Todas as atividades registradas</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800 text-left text-sm text-gray-500">
                                <th class="px-4 py-3 font-medium whitespace-nowrap">Início</th>
                                <th class="px-4 py-3 font-medium whitespace-nowrap">Fim</th>
                                <th class="px-4 py-3 font-medium">Título</th>
                                <th class="px-4 py-3 font-medium">Descrição</th>
                                <th class="px-4 py-3 font-medium">Categoria</th>
                                <th class="px-4 py-3 font-medium">Projeto</th>
                                <th class="px-4 py-3 font-medium">Módulo</th>
                                <th class="px-4 py-3 font-medium whitespace-nowrap">Prioridade</th>
                                <th class="px-4 py-3 font-medium whitespace-nowrap">Dificuldade</th>
                                <th class="px-4 py-3 font-medium whitespace-nowrap">Duração</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium w-20">Ações</th>
                            </tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-4 py-2">
                                    <input v-model="filters.date_from" type="date" @change="applyFilters"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white" />
                                </th>
                                <th class="px-4 py-2">
                                    <input v-model="filters.search" type="text" placeholder="Buscar..."
                                        @input="onSearchInput"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                                </th>
                                <th class="px-4 py-2"></th>
                                <th class="px-4 py-2">
                                    <select v-model="filters.category_id" @change="applyFilters"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                        <option value="">Todas</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select v-model="filters.project_id" @change="applyFilters"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                        <option value="">Todos</option>
                                        <option v-for="proj in projects" :key="proj.id" :value="proj.id">{{ proj.name }}</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select v-model="filters.context_id" @change="applyFilters"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                        <option value="">Todos</option>
                                        <option v-for="mod in modules" :key="mod.id" :value="mod.id">{{ mod.name }}</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <input v-model="filters.description" type="text" placeholder="Buscar na descrição..."
                                        @input="onSearchInput"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                                </th>
                                <th class="px-4 py-2">
                                    <select v-model="filters.priority" @change="applyFilters"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                        <option value="">Todas</option>
                                        <option value="low">Baixa</option>
                                        <option value="medium">Média</option>
                                        <option value="high">Alta</option>
                                        <option value="critical">Crítica</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select v-model="filters.energy_level" @change="applyFilters"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                        <option value="">Todas</option>
                                        <option :value="1">1 - Muito Fácil</option>
                                        <option :value="2">2 - Fácil</option>
                                        <option :value="3">3 - Normal</option>
                                        <option :value="4">4 - Difícil</option>
                                        <option :value="5">5 - Muito Difícil</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select v-model="filters.status" @change="applyFilters"
                                        class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                        <option value="">Todos</option>
                                        <option value="in_progress">Em andamento</option>
                                        <option value="paused">Pausado</option>
                                        <option value="completed">Concluído</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="activity in activities.data" :key="activity.id"
                                @click="viewDetail(activity)"
                                class="border-b border-gray-50 dark:border-gray-800/50 text-sm hover:bg-gray-50 dark:hover:bg-gray-800/30 cursor-pointer">
                                <td class="px-4 py-3 text-gray-500 font-mono whitespace-nowrap">{{ formatDate(activity.started_at) }}</td>
                                <td class="px-4 py-3 text-gray-500 font-mono whitespace-nowrap">{{ formatDate(activity.ended_at) }}</td>
                                <td class="px-4 py-3 font-medium max-w-[200px]">
                                    <div class="flex items-center gap-2" :title="activity.title">
                                        {{ truncate(activity.title, 24) }}
                                        <span v-if="activity.type === 'interruption'"
                                            class="shrink-0 text-xs px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-950/30 text-red-600 dark:text-red-400">
                                            Int
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 max-w-[200px]">
                                    <span v-if="activity.description" :title="activity.description"
                                        class="text-gray-500 text-xs block truncate">
                                        {{ truncate(activity.description, 50) }}
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: activity.category?.color }" />
                                        {{ activity.category?.name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ activity.project?.name || '-' }}</td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ activity.context?.name || '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span v-if="activity.priority" class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                                        :class="priorityClass(activity.priority)">
                                        {{ priorityLabel(activity.priority) }}
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                     <span v-if="activity.energy_level" class="text-xs text-gray-500 font-mono">
                                         Dificuldade: {{ difficultyLabel(activity.energy_level) }}
                                     </span>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-500 whitespace-nowrap">{{ formatDuration(activity.duration_minutes) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap"
                                        :class="statusClass(activity.status)">
                                        {{ statusLabel(activity.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" @click.stop>
                                    <div class="flex items-center gap-1">
                                        <button @click.stop="edit(activity)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            </svg>
                                        </button>
                                        <button v-if="activity.status === 'paused'" @click="resume(activity)"
                                            class="p-1.5 rounded-lg text-yellow-500 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-950/20 transition-colors"
                                            title="Resumir">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </button>
                                        <button v-if="activity.status === 'completed'" @click="reopen(activity)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-green-500 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-950/20 transition-colors"
                                            title="Reabrir">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                        </button>
                                        <button @click="confirmDelete(activity)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="activities.data?.length === 0">
                                <td colspan="12" class="px-4 py-12 text-center text-gray-500">
                                    Nenhuma atividade encontrada.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span class="hidden sm:inline">Por página:</span>
                    <select v-model="filters.per_page" @change="applyFilters"
                        class="text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div v-if="activities.total > activities.per_page" class="flex gap-2">
                    <Link v-for="link in activities.links" :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                        :class="link.active
                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                            : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-gray-400 dark:hover:border-gray-500'" />
                </div>
            </div>
        </div>

        <EditActivityModal v-if="editingActivity" :activity="editingActivity" @close="closeEdit" @saved="closeEdit" />
        <ConfirmDeleteModal v-if="deletingActivity" :activity="deletingActivity" @close="deletingActivity = null" @confirm="(e) => doDelete(e.mode)" />
        <ActivityDetailModal v-if="viewingActivity" :activity="viewingActivity" @close="viewingActivity = null" />
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EditActivityModal from '@/Components/EditActivityModal.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import ActivityDetailModal from '@/Components/ActivityDetailModal.vue'

const page = usePage()
const categories = page.props.categories
const projects = page.props.projects
const modules = page.props.modules

const props = defineProps({
    activities: Object,
    inProgress: Object,
    filters: Object,
})

const filters = reactive({
    search: props.filters?.search || '',
    category_id: props.filters?.category_id || '',
    project_id: props.filters?.project_id || '',
    context_id: props.filters?.context_id || '',
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    description: props.filters?.description || '',
    priority: props.filters?.priority || '',
    energy_level: props.filters?.energy_level || '',
    per_page: props.filters?.per_page || '50',
})

let searchTimeout = null

const editingActivity = ref(null)
const deletingActivity = ref(null)
const viewingActivity = ref(null)

function edit(activity) {
    editingActivity.value = activity
}

function closeEdit() {
    editingActivity.value = null
}

function viewDetail(activity) {
    viewingActivity.value = activity
}

function confirmDelete(activity) {
    deletingActivity.value = activity
}

function resume(activity) {
    router.post(`/api/activities/${activity.id}/resume`, {}, {
        preserveState: false,
    })
}

function reopen(activity) {
    router.post(`/api/activities/${activity.id}/reopen`, {}, {
        preserveState: false,
    })
}

function doDelete(mode) {
    if (!deletingActivity.value) return
    router.delete(`/activities/${deletingActivity.value.id}?mode=${mode}`, {
        preserveState: false,
        onFinish: () => { deletingActivity.value = null },
    })
}

function applyFilters() {
    const params = {}
    for (const [key, value] of Object.entries(filters)) {
        if (value) params[key] = value
    }
    router.get('/activities', params, { preserveState: true, preserveScroll: true })
}

function onSearchInput() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 300)
}

function truncate(text, max) {
    if (!text) return ''
    return text.length > max ? text.slice(0, max) + '…' : text
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

function formatDuration(minutes) {
    if (!minutes && minutes !== 0) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
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

function difficultyLabel(level) {
    const map = {
        1: 'Muito Fácil',
        2: 'Fácil',
        3: 'Normal',
        4: 'Difícil',
        5: 'Muito Difícil',
    }
    return map[level] || level
}
</script>
