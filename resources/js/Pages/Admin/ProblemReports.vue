<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Relatórios de Problemas</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Problemas reportados pelos usuários</p>
            </div>

            <div class="flex items-center gap-3">
                <select v-model="filters.status" @change="applyFilters"
                    class="text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                    <option value="">Todos os status</option>
                    <option value="open">Aberto</option>
                    <option value="in_progress">Em andamento</option>
                    <option value="resolved">Resolvido</option>
                </select>
                <select v-model="filters.severity" @change="applyFilters"
                    class="text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                    <option value="">Todas as gravidades</option>
                    <option value="low">Baixa</option>
                    <option value="normal">Normal</option>
                    <option value="medium">Média</option>
                    <option value="high">Alta</option>
                    <option value="critical">Crítica</option>
                </select>
            </div>

            <div class="space-y-3">
                <div v-for="report in reports.data" :key="report.id"
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    :class="statusClass(report.status)">{{ statusLabel(report.status) }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    :class="severityClass(report.severity)">{{ severityLabel(report.severity) }}</span>
                                <span class="text-xs text-gray-400">{{ report.user?.name }}</span>
                                <span class="text-xs text-gray-400">{{ formatDate(report.created_at) }}</span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 whitespace-pre-wrap">{{ report.description }}</p>
                            <div v-if="report.url" class="text-xs text-gray-400 mt-1 font-mono truncate">{{ report.url }}</div>
                        </div>
                        <div v-if="canManage" class="flex items-center gap-2 shrink-0">
                            <select v-model="report.status" @change="updateStatus(report)"
                                class="text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                <option value="open">Aberto</option>
                                <option value="in_progress">Em andamento</option>
                                <option value="resolved">Resolvido</option>
                            </select>
                            <button @click="confirmDelete(report)"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="reports.data?.length === 0" class="py-12 text-center text-sm text-gray-400">
                    Nenhum relatório encontrado.
                </div>
            </div>

            <div v-if="reports.total > reports.per_page" class="flex justify-center gap-2">
                <Link v-for="link in reports.links" :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                    :class="link.active
                        ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                        : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-gray-400 dark:hover:border-gray-500'" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    reports: Object,
    filters: Object,
    canManage: Boolean,
})

const filters = reactive({
    status: props.filters?.status || '',
    severity: props.filters?.severity || '',
})

function applyFilters() {
    const params = {}
    for (const [key, value] of Object.entries(filters)) {
        if (value) params[key] = value
    }
    router.get('/admin/problem-reports', params, { preserveState: true, preserveScroll: true })
}

function updateStatus(report) {
    router.put(route('admin.problem-reports.update', report.id), { status: report.status }, {
        preserveScroll: true,
    })
}

function confirmDelete(report) {
    if (confirm('Excluir este relatório?')) {
        router.delete(route('admin.problem-reports.destroy', report.id), {
            preserveScroll: true,
        })
    }
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

function statusClass(status) {
    const map = {
        open: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400',
        in_progress: 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400',
        resolved: 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400',
    }
    return map[status] || 'bg-gray-100 dark:bg-gray-800 text-gray-500'
}

function statusLabel(status) {
    const map = { open: 'Aberto', in_progress: 'Em andamento', resolved: 'Resolvido' }
    return map[status] || status
}

function severityClass(severity) {
    const map = {
        low: 'bg-gray-100 dark:bg-gray-800 text-gray-500',
        normal: 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400',
        medium: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400',
        high: 'bg-orange-100 dark:bg-orange-950/30 text-orange-700 dark:text-orange-400',
        critical: 'bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400',
    }
    return map[severity] || ''
}

function severityLabel(severity) {
    const map = { low: 'Baixa', normal: 'Normal', medium: 'Média', high: 'Alta', critical: 'Crítica' }
    return map[severity] || severity
}
</script>
