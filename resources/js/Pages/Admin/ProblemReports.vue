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
                    <option value="pending">Aguardando</option>
                    <option value="analyzing">Em análise</option>
                    <option value="resolved">Finalizado</option>
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
                                <span v-if="report.app_version" class="text-xs text-gray-400">v{{ report.app_version }}</span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 whitespace-pre-wrap">{{ report.description }}</p>
                            <div v-if="report.url" class="text-xs text-gray-400 mt-1 font-mono truncate">{{ report.url }}</div>

                            <div v-if="report.images?.length" class="flex gap-2 mt-3">
                                <div v-for="(img, idx) in report.images" :key="idx" @click="openLightbox(report, idx)"
                                    class="relative w-16 h-12 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-80 transition-opacity shrink-0"
                                    :class="img.primary ? 'ring-2 ring-yellow-400' : ''">
                                    <img :src="imageUrl(report.id, idx)" class="w-full h-full object-cover" @error="onImgError" />
                                    <span v-if="img.primary" class="absolute top-0 left-0 text-[8px] px-1 py-0.5 bg-yellow-400 text-yellow-900 font-medium rounded-br">★</span>
                                </div>
                            </div>

                            <div v-if="report.status === 'resolved' && report.resolution_notes" class="mt-3 p-3 bg-green-50 dark:bg-green-950/20 rounded-lg border border-green-200 dark:border-green-900">
                                <div class="text-xs font-medium text-green-600 dark:text-green-400 mb-1">Resolução</div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ report.resolution_notes }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 shrink-0">
                            <select v-model="report.status" @change="onStatusChange(report)"
                                class="text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                                <option value="pending">Aguardando</option>
                                <option value="analyzing">Em análise</option>
                                <option value="resolved">Finalizado</option>
                            </select>
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

        <div v-if="lightbox.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
            @click.self="lightbox.show = false">
            <div class="relative max-w-4xl max-h-[90vh]">
                <button @click="lightbox.show = false"
                    class="absolute -top-10 right-0 text-white/70 hover:text-white transition-colors text-sm">
                    Fechar
                </button>
                <img :src="lightbox.url" class="max-w-full max-h-[85vh] rounded-lg" />
                <div class="text-center mt-2 text-xs text-white/50">
                    {{ lightbox.index + 1 }} / {{ lightbox.total }}
                </div>
            </div>
        </div>

        <div v-if="resolutionModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm p-4"
            @click.self="closeResolution">
            <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Finalizar Relatório</h3>
                <p class="text-xs text-gray-500">Descreva o que foi feito para resolver o problema.</p>
                <textarea v-model="resolutionNotes" rows="4" placeholder="Descreva a resolução..."
                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400 resize-y" />
                <span v-if="resolutionError" class="text-xs text-red-500">{{ resolutionError }}</span>
                <div class="flex justify-end gap-3">
                    <button @click="closeResolution"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                    <button @click="confirmResolution"
                        class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                        Confirmar
                    </button>
                </div>
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
})

const filters = reactive({
    status: props.filters?.status || '',
    severity: props.filters?.severity || '',
})

const lightbox = reactive({
    show: false,
    url: '',
    index: 0,
    total: 0,
})

const resolutionModal = reactive({
    show: false,
    report: null,
})
const resolutionNotes = ref('')
const resolutionError = ref('')

function imageUrl(reportId, index) {
    return route('admin.problem-reports.images', [reportId, index])
}

function openLightbox(report, index) {
    lightbox.show = true
    lightbox.url = imageUrl(report.id, index)
    lightbox.index = index
    lightbox.total = report.images?.length || 0
}

function onImgError(e) {
    e.target.style.display = 'none'
}

function applyFilters() {
    const params = {}
    for (const [key, value] of Object.entries(filters)) {
        if (value) params[key] = value
    }
    router.get('/admin/problem-reports', params, { preserveState: true, preserveScroll: true })
}

function onStatusChange(report) {
    if (report.status === 'resolved') {
        resolutionModal.show = true
        resolutionModal.report = report
        resolutionNotes.value = ''
        resolutionError.value = ''
        report.status = report._prevStatus || report.status
    } else {
        router.put(route('admin.problem-reports.update', report.id), { status: report.status }, {
            preserveScroll: true,
        })
    }
}

function closeResolution() {
    resolutionModal.show = false
    resolutionModal.report = null
    resolutionNotes.value = ''
    resolutionError.value = ''
}

function confirmResolution() {
    if (!resolutionNotes.value.trim()) {
        resolutionError.value = 'Descreva a resolução para finalizar.'
        return
    }
    const report = resolutionModal.report
    router.put(route('admin.problem-reports.update', report.id), {
        status: 'resolved',
        resolution_notes: resolutionNotes.value.trim(),
    }, {
        preserveScroll: true,
        onSuccess: () => closeResolution(),
    })
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

function statusClass(status) {
    const map = {
        pending: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400',
        analyzing: 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400',
        resolved: 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400',
    }
    return map[status] || 'bg-gray-100 dark:bg-gray-800 text-gray-500'
}

function statusLabel(status) {
    const map = { pending: 'Aguardando', analyzing: 'Em análise', resolved: 'Finalizado' }
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
