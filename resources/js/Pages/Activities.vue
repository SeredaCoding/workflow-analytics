<template>
    <AppLayout :in-progress="inProgress">
        <div class="max-w-6xl mx-auto space-y-6">
            <div>
                <h2 class="text-2xl font-bold">Atividades</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Todas as atividades registradas</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800 text-left text-sm text-gray-500">
                            <th class="px-5 py-3 font-medium">Data</th>
                            <th class="px-5 py-3 font-medium">Título</th>
                            <th class="px-5 py-3 font-medium">Categoria</th>
                            <th class="px-5 py-3 font-medium">Projeto</th>
                            <th class="px-5 py-3 font-medium">Duração</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="activity in activities.data" :key="activity.id"
                            class="border-b border-gray-50 dark:border-gray-800/50 text-sm hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-5 py-3 text-gray-500 font-mono">{{ formatDate(activity.started_at) }}</td>
                            <td class="px-5 py-3 font-medium">
                                <div class="flex items-center gap-2">
                                    {{ activity.title }}
                                    <span v-if="activity.type === 'interruption'"
                                        class="text-xs px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-950/30 text-red-600 dark:text-red-400">
                                        Interrupção
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: activity.category?.color }" />
                                    {{ activity.category?.name }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ activity.project?.name || '-' }}</td>
                            <td class="px-5 py-3 font-mono text-gray-500">{{ formatDuration(activity.duration_minutes) }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2 py-1 rounded-full"
                                    :class="statusClass(activity.status)">
                                    {{ statusLabel(activity.status) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="activities.data?.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                Nenhuma atividade registrada.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    activities: Object,
    inProgress: Object,
})

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })
}

function formatDuration(minutes) {
    if (!minutes) return '-'
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
</script>
