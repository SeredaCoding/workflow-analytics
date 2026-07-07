<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <Link :href="route('projects.index')" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Voltar para Projetos
            </Link>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-start gap-3">
                    <span class="inline-block w-4 h-4 rounded-full mt-1 shrink-0" :style="{ backgroundColor: project.color }" />
                    <div class="min-w-0 flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ project.name }}</h1>
                        <p v-if="project.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 whitespace-pre-wrap">{{ project.description }}</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="visibilityClass(project.visibility)">
                                {{ visibilityLabel(project.visibility) }}
                            </span>
                            <span v-if="project.sectors?.length" v-for="s in project.sectors" :key="s.id"
                                class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400">
                                {{ s.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Total" :value="stats.total_hours" unit="h" color="blue" />
                <StatCard label="Atividades" :value="stats.total_activities" unit="" color="green" />
                <StatCard label="Usuários" :value="stats.unique_users" unit="" color="purple" />
                <StatCard label="Período" :value="stats.period_days" unit="dias" color="orange" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="categoryDistribution.length" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Categorias</h3>
                    <div class="flex items-center justify-center" style="height: 220px">
                        <Doughnut :data="chartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="cat in categoryDistribution" :key="cat.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: cat.color }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ cat.name }}</span>
                            <span class="text-gray-500">{{ cat.percentage }}%</span>
                        </div>
                    </div>
                </div>

                <div v-if="topUsers.length" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Quem mais contribuiu</h3>
                    <div class="space-y-3">
                        <div v-for="(u, i) in topUsers" :key="u.name" class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-400 w-5">{{ i + 1 }}°</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ u.name }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-gray-500">{{ formatDuration(u.minutes) }}</span>
                                <span class="text-gray-400 w-8 text-right">{{ u.percentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="monthlyHistory.length" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Histórico Mensal</h3>
                <div style="height: 200px">
                    <Bar :data="monthlyChartData" :options="barOptions" />
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Linha do Tempo</h3>

                <div class="space-y-0">
                    <template v-for="(group, gIdx) in timelineGroups" :key="gIdx">
                        <div class="text-xs font-medium text-gray-400 dark:text-gray-500 py-3 border-b border-gray-100 dark:border-gray-800">
                            {{ group.label }}
                        </div>
                        <div v-for="act in group.activities" :key="act.id"
                            class="flex items-start gap-3 py-3 border-b border-gray-50 dark:border-gray-800/50 text-sm">
                            <span class="text-xs text-gray-400 dark:text-gray-500 w-12 shrink-0 pt-0.5 text-right">{{ formatTime(act.started_at) }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 dark:text-white truncate">{{ act.title }}</div>
                                <div class="flex flex-wrap gap-2 mt-0.5">
                                    <span class="text-xs text-gray-500">{{ act.user?.name }}</span>
                                    <span v-if="act.category" class="inline-flex items-center gap-1 text-xs text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: act.category.color }" />
                                        {{ act.category.name }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0 pt-0.5">{{ formatDuration(act.duration_minutes) }}</span>
                        </div>
                    </template>
                    <div v-if="activities.data?.length === 0" class="py-8 text-center text-sm text-gray-400">
                        Nenhuma atividade registrada neste projeto.
                    </div>
                </div>

                <div v-if="activities.total > activities.per_page" class="flex justify-center gap-2 pt-4">
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
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Doughnut, Bar } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement)

const props = defineProps({
    project: Object,
    activities: Object,
    stats: Object,
    categoryDistribution: Array,
    topUsers: Array,
    monthlyHistory: Array,
})

const chartData = computed(() => ({
    labels: props.categoryDistribution?.map(c => c.name) || [],
    datasets: [{
        data: props.categoryDistribution?.map(c => c.minutes) || [],
        backgroundColor: props.categoryDistribution?.map(c => c.color) || [],
        borderWidth: 0,
        hoverOffset: 6,
        borderRadius: 4,
    }],
}))

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1f2937',
            titleColor: '#f3f4f6',
            bodyColor: '#d1d5db',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
                label: (ctx) => {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
                    const pct = ((ctx.parsed / total) * 100).toFixed(0)
                    return ` ${ctx.label}: ${formatDuration(ctx.parsed)} (${pct}%)`
                },
            },
        },
    },
}

const monthlyChartData = computed(() => ({
    labels: props.monthlyHistory?.map(m => m.month) || [],
    datasets: [{
        label: 'Horas',
        data: props.monthlyHistory?.map(m => roundTo(m.minutes / 60, 1)) || [],
        backgroundColor: '#6366f1',
        borderRadius: 4,
        barPercentage: 0.6,
    }],
}))

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#6b7280', font: { size: 11 } },
        },
        y: {
            grid: { color: '#37415120' },
            ticks: { color: '#6b7280', font: { size: 11 } },
            beginAtZero: true,
        },
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1f2937',
            titleColor: '#f3f4f6',
            bodyColor: '#d1d5db',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
                label: (ctx) => ` ${ctx.parsed.y}h`,
            },
        },
    },
}

const timelineGroups = computed(() => {
    const activities = props.activities?.data || []
    const groups = []
    let currentLabel = null
    let currentGroup = null

    for (const act of activities) {
        const label = dayLabel(act.started_at)
        if (label !== currentLabel) {
            currentGroup = { label, activities: [] }
            groups.push(currentGroup)
            currentLabel = label
        }
        currentGroup.activities.push(act)
    }

    return groups
})

function dayLabel(dateStr) {
    if (!dateStr) return ''
    const d = new Date(dateStr)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    const dateKey = d.toDateString()
    const todayKey = today.toDateString()
    const yesterdayKey = yesterday.toDateString()

    if (dateKey === todayKey) return 'Hoje'
    if (dateKey === yesterdayKey) return 'Ontem'
    return d.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' })
}

function formatTime(dateStr) {
    if (!dateStr) return ''
    const d = new Date(dateStr)
    return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

function formatDuration(minutes) {
    if (!minutes) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}

function roundTo(value, decimals) {
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals)
}

function visibilityClass(v) {
    if (v === 'global') return 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400'
    if (v === 'sector') return 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400'
    return 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400'
}

function visibilityLabel(v) {
    if (v === 'global') return 'Global'
    if (v === 'sector') return 'Setor'
    return 'Usuário'
}
</script>
