<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Estatísticas</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visão geral do período</p>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="hasAnyData && hasPrev" @click="prevMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400"
                        title="Mês anterior">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-semibold min-w-[140px] text-center select-none">{{ monthLabel }}</span>
                    <button v-if="hasAnyData && hasNext" @click="nextMonth"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400"
                        title="Próximo mês">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <div v-if="hasAnyData" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Total no Mês" :value="monthly.total_hours" unit="h" color="blue" />
                <StatCard label="Interrupções" :value="monthly.interruptions" unit="" color="red" />
                <StatCard label="Méd. Foco" :value="monthly.avg_focus_minutes" unit="min" color="green" />
                <StatCard label="Reuniões" :value="meetingHours" unit="h" color="purple" />
            </div>

            <div v-if="hasAnyData" class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <div>
                    <h3 class="text-sm font-semibold">Relatório Mensal</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Envie o resumo do mês por e-mail para seu destinatário</p>
                </div>
                <div class="flex items-center gap-3">
                    <span v-if="statusMessage" class="text-sm" :class="statusError ? 'text-red-500' : 'text-green-500'">
                        {{ statusMessage }}
                    </span>
                    <button @click="sendReport" :disabled="sending"
                        class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors disabled:opacity-50">
                        {{ sending ? 'Enviando...' : 'Enviar Relatório' }}
                    </button>
                </div>
            </div>

            <div v-if="hasCategoryData || (isCurrentMonth && hasBarData)" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="hasCategoryData" :class="isCurrentMonth ? '' : 'md:col-span-2'" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Distribuição por Categoria</h3>
                    <div class="flex items-center justify-center" style="height: 220px">
                        <Doughnut :data="categoryChartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="cat in categoryDistribution" :key="cat.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: cat.color }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ cat.name }}</span>
                            <span class="text-gray-500">{{ categoryPercent(cat.minutes) }}%</span>
                        </div>
                    </div>
                </div>

                <div v-if="isCurrentMonth && hasBarData" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Últimos 7 Dias</h3>
                    <div style="height: 220px">
                        <Bar :data="weeklyChartData" :options="barOptions" />
                    </div>
                </div>
            </div>

            <div v-if="hasProjectData || hasContextData" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="hasProjectData" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Distribuição por Projeto</h3>
                    <div class="flex items-center justify-center" style="height: 220px">
                        <Doughnut :data="projectChartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="proj in projectDistribution" :key="proj.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: proj.color || projectColor(proj.name) }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ proj.name }}</span>
                            <span class="text-gray-500">{{ projectPercent(proj.minutes) }}%</span>
                        </div>
                    </div>
                </div>

                <div v-if="hasContextData" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Distribuição por Módulo <span class="text-gray-400 font-normal">(camada do sistema)</span></h3>
                    <div class="flex items-center justify-center" style="height: 220px">
                        <Doughnut :data="contextChartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="mod in contextDistribution" :key="mod.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: mod.color || '#6366f1' }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ mod.name }}</span>
                            <span class="text-gray-500">{{ contextPercent(mod.minutes) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="hasAnyData" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Mês</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th v-for="day in dayNames" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 font-medium text-xs">{{ day }}</th>
                                <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium text-xs">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(week, wi) in dailyBreakdown.weeks" :key="wi" class="border-b border-gray-100 dark:border-gray-800" :class="wi % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/20' : ''">
                                <td v-for="day in week.days" class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                                    <template v-if="day.is_current_month">
                                        <div class="font-medium">{{ day.day }}</div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500">{{ day.total_minutes != null ? formatHours(day.total_minutes) : '-' }}</div>
                                    </template>
                                    <template v-else>
                                        <div class="text-gray-300 dark:text-gray-600">-</div>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ formatHours(week.total_minutes) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 dark:border-gray-700 font-semibold">
                                <td colspan="7" class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">Total do Mês</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-bold">{{ formatHours(dailyBreakdown.total_minutes) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div v-if="hasAnyData" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Resumo Semanal</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">Semana</th>
                                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">Dias</th>
                                <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium">Total</th>
                                <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium">Interrupções</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(week, wi) in dailyBreakdown.weeks" :key="wi">
                                <tr v-if="weekDaysInMonth(week).length" class="border-b border-gray-100 dark:border-gray-800" :class="wi % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/20' : ''">
                                    <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Semana {{ weekIndex(wi) }}</td>
                                    <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs">{{ weekRange(week) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ formatHours(week.total_minutes) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ weekInterruptions(week) }}</td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 dark:border-gray-700 font-semibold">
                                <td colspan="2" class="px-4 py-3 text-gray-700 dark:text-gray-300">Total do Mês</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-bold">{{ formatHours(dailyBreakdown.total_minutes) }}</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-bold">{{ totalInterruptions }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div v-if="hasAnyData" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Top 5 Atividades</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">#</th>
                                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">Atividade</th>
                                <th class="px-4 py-3 text-left text-gray-500 dark:text-gray-400 font-medium">Categoria</th>
                                <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium">Duração</th>
                                <th class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 font-medium">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="topActivities.length === 0">
                                <td colspan="5" class="px-4 py-3 text-center text-gray-400 dark:text-gray-500">Nenhuma atividade registrada no período.</td>
                            </tr>
                            <tr v-for="(act, i) in topActivities" :key="act.id" class="border-b border-gray-100 dark:border-gray-800" :class="i % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/20' : ''">
                                <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs">{{ i + 1 }}°</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ act.title }}</td>
                                <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs">{{ act.category?.name || '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">{{ formatHours(act.duration_minutes) }}</td>
                                <td class="px-4 py-3 text-right text-gray-400 dark:text-gray-500 text-xs">{{ activityPercent(act.duration_minutes) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>

    <div v-if="showInProgressModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm"
        @click.self="showInProgressModal = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-5 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Atividade em Andamento</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Tem 1 atividade em andamento. Deseja incluir o tempo parcial no relatório?
                </p>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showInProgressModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        Cancelar
                    </button>
                    <button @click="sendWithInProgress(false)"
                        class="px-4 py-2 text-sm font-medium bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Não, só concluídas
                    </button>
                    <button @click="sendWithInProgress(true)"
                        class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                        Sim, incluir
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Doughnut, Bar } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement)

const props = defineProps({
    month: String,
    monthly: Object,
    categoryDistribution: Array,
    dailyBreakdown: Object,
    topActivities: Array,
    totalMinutes: Number,
    projectDistribution: Array,
    contextDistribution: Array,
    hasPrev: Boolean,
    hasNext: Boolean,
})

const dayNames = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom']

const weeklyData = ref([])

const currentMonth = ref(props.month || new Date().toISOString().slice(0, 7))
const monthLabel = computed(() => {
    const [y, m] = currentMonth.value.split('-').map(Number)
    const date = new Date(y, m - 1)
    return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
})

function goMonth(delta) {
    const [y, m] = currentMonth.value.split('-').map(Number)
    const d = new Date(y, m - 1 + delta, 1)
    const ym = d.toISOString().slice(0, 7)
    currentMonth.value = ym
    router.get('/stats', { month: ym }, { preserveState: true, preserveScroll: true })
}

function prevMonth() { goMonth(-1) }
function nextMonth() { goMonth(1) }

const isCurrentMonth = computed(() => {
    const now = new Date()
    const ym = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
    return currentMonth.value === ym
})

const hasAnyData = computed(() => (props.monthly?.total_minutes ?? 0) > 0)
const hasCategoryData = computed(() => props.categoryDistribution?.some(c => c.minutes > 0) ?? false)
const hasProjectData = computed(() => props.projectDistribution?.some(p => p.minutes > 0) ?? false)
const hasContextData = computed(() => props.contextDistribution?.some(c => c.minutes > 0) ?? false)
const hasBarData = computed(() => weeklyData.value?.some(d => d.total_minutes > 0) ?? false)

const meetingHours = computed(() => {
    if (!props.monthly?.meeting_minutes) return '0.0'
    return (props.monthly.meeting_minutes / 60).toFixed(1)
})

const totalMinutes = computed(() => {
    return props.categoryDistribution?.reduce((sum, c) => sum + c.minutes, 0) || 1
})

const projectTotalMinutes = computed(() => {
    return props.projectDistribution?.reduce((sum, p) => sum + p.minutes, 0) || 1
})

const projectColors = [
    '#22c55e', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899',
    '#14b8a6', '#a855f7', '#3b82f6', '#06b6d4', '#f97316',
    '#84cc16', '#10b981',
]

function projectColor(name) {
    let hash = 0
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
    return projectColors[Math.abs(hash) % projectColors.length]
}

const projectChartData = computed(() => ({
    labels: props.projectDistribution?.map(p => p.name) || [],
    datasets: [{
        data: props.projectDistribution?.map(p => p.minutes) || [],
        backgroundColor: props.projectDistribution?.map(p => p.color || projectColor(p.name)) || [],
        borderWidth: 0,
        hoverOffset: 6,
        borderRadius: 4,
    }],
}))

function projectPercent(minutes) {
    return ((minutes / projectTotalMinutes.value) * 100).toFixed(0)
}

const contextTotalMinutes = computed(() => {
    return props.contextDistribution?.reduce((sum, c) => sum + c.minutes, 0) || 1
})

const contextChartData = computed(() => ({
    labels: props.contextDistribution?.map(c => c.name) || [],
    datasets: [{
        data: props.contextDistribution?.map(c => c.minutes) || [],
        backgroundColor: props.contextDistribution?.map(c => c.color || '#6366f1') || [],
        borderWidth: 0,
        hoverOffset: 6,
        borderRadius: 4,
    }],
}))

function contextPercent(minutes) {
    return ((minutes / contextTotalMinutes.value) * 100).toFixed(0)
}

const categoryChartData = computed(() => ({
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
            displayColors: true,
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

const allCategoryNames = computed(() => {
    const names = new Set()
    weeklyData.value.forEach(d => {
        if (d.categories) Object.keys(d.categories).forEach(name => names.add(name))
    })
    return [...names]
})

const weeklyChartData = computed(() => ({
    labels: weeklyData.value.map(d => d.label),
    datasets: allCategoryNames.value.map(catName => {
        const firstDay = weeklyData.value.find(d => d.categories?.[catName])
        const color = firstDay?.categories?.[catName]?.color || '#6366f1'
        return {
            label: catName,
            data: weeklyData.value.map(d => d.categories?.[catName]?.minutes || 0),
            backgroundColor: color,
            borderRadius: 4,
            barPercentage: 0.7,
        }
    }),
}))

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#6b7280', font: { size: 12 } },
        },
        y: {
            grid: { color: '#37415120' },
            ticks: { color: '#6b7280', font: { size: 11 } },
            stacked: true,
            beginAtZero: true,
        },
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: '#1f2937',
            titleColor: '#f3f4f6',
            bodyColor: '#d1d5db',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
                label: (ctx) => ` ${ctx.dataset.label}: ${formatDuration(ctx.parsed.y)}`,
            },
        },
    },
}

function categoryPercent(minutes) {
    return ((minutes / totalMinutes.value) * 100).toFixed(0)
}

function formatHours(minutes) {
    if (minutes == null) return '-'
    return roundTo(minutes / 60, 1) + 'h'
}

function roundTo(value, decimals) {
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals)
}

function activityPercent(minutes) {
    return ((minutes / props.totalMinutes) * 100).toFixed(0)
}

function weekDaysInMonth(week) {
    return week.days.filter(d => d.is_current_month)
}

function weekIndex(wi) {
    return props.dailyBreakdown.weeks.slice(0, wi + 1).filter(w => weekDaysInMonth(w).length).length
}

function weekRange(week) {
    const days = weekDaysInMonth(week)
    if (!days.length) return ''
    const first = days[0]
    const last = days[days.length - 1]
    const [y, m] = (props.month || '').split('-').map(Number)
    const date = new Date(y, m - 1)
    const label = date.toLocaleDateString('pt-BR', { month: 'short' }).replace('.', '').replace(/^(\w)/, (c) => c.toLowerCase())
    return first.day + ' ' + label + ' - ' + last.day + ' ' + label
}

function weekInterruptions(week) {
    return weekDaysInMonth(week).reduce((sum, d) => sum + (d.interruptions || 0), 0)
}

const totalInterruptions = computed(() => {
    if (!props.dailyBreakdown?.weeks) return 0
    return props.dailyBreakdown.weeks.reduce((sum, w) => sum + weekInterruptions(w), 0)
})

const sending = ref(false)
const statusMessage = ref('')
const statusError = ref(false)
const showInProgressModal = ref(false)

async function sendReport() {
    if (props.inProgress) {
        showInProgressModal.value = true
        return
    }
    await doSend(false)
}

async function sendWithInProgress(include) {
    showInProgressModal.value = false
    await doSend(include)
}

async function doSend(includeInProgress) {
    sending.value = true
    statusMessage.value = ''
    statusError.value = false
    try {
        const res = await window.axios.post('/api/reports/send-monthly', {
            include_in_progress: includeInProgress,
            month: currentMonth.value,
        })
        const data = res.data
        if (data.success) {
            statusMessage.value = data.success
            statusError.value = false
        } else {
            statusMessage.value = data.error || 'Erro ao enviar'
            statusError.value = true
        }
    } catch (e) {
        statusMessage.value = 'Erro de conexão'
        statusError.value = true
    } finally {
        sending.value = false
        setTimeout(() => { statusMessage.value = '' }, 5000)
    }
}

function formatDuration(minutes) {
    if (!minutes) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}

onMounted(async () => {
    try {
        const monthParam = props.month ? `?month=${props.month}` : ''
        const res = await fetch(`/api/stats/daily${monthParam}`)
        weeklyData.value = await res.json()
    } catch (e) {
        console.error('Failed to load weekly data', e)
    }
})
</script>
