<template>
    <AppLayout :in-progress="inProgress">
        <div class="max-w-6xl mx-auto space-y-8">
            <div>
                <h2 class="text-2xl font-bold">Estatísticas</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visão geral do mês</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Total no Mês" :value="monthly.total_hours" unit="h" color="blue" />
                <StatCard label="Interrupções" :value="monthly.interruptions" unit="" color="red" />
                <StatCard label="Méd. Foco" :value="monthly.avg_focus_minutes" unit="min" color="green" />
                <StatCard label="Reuniões" :value="meetingHours" unit="h" color="purple" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Distribuição por Categoria</h3>
                    <div class="flex items-center justify-center" style="height: 220px">
                        <Doughnut :data="categoryChartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="cat in categoryDistribution" :key="cat.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: categoryColor(cat.name) }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ cat.name }}</span>
                            <span class="text-gray-500">{{ categoryPercent(cat.minutes) }}%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Últimos 7 Dias</h3>
                    <div style="height: 220px">
                        <Bar :data="weeklyChartData" :options="barOptions" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <div>
                    <h3 class="text-sm font-semibold">Relatório Mensal</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Envie o resumo do mês por e-mail para seu chefe</p>
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
        </div>
    </AppLayout>

    <div v-if="showInProgressModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
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
import { Doughnut, Bar } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement)

const props = defineProps({
    monthly: Object,
    categoryDistribution: Array,
    inProgress: Object,
})

const weeklyData = ref([])

const meetingHours = computed(() => {
    if (!props.monthly?.meeting_minutes) return '0.0'
    return (props.monthly.meeting_minutes / 60).toFixed(1)
})

const totalMinutes = computed(() => {
    return props.categoryDistribution?.reduce((sum, c) => sum + c.minutes, 0) || 1
})

const categoryChartData = computed(() => ({
    labels: props.categoryDistribution?.map(c => c.name) || [],
    datasets: [{
        data: props.categoryDistribution?.map(c => c.minutes) || [],
        backgroundColor: props.categoryDistribution?.map(c => categoryColor(c.name)) || [],
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

const weeklyChartData = computed(() => ({
    labels: weeklyData.value.map(d => d.label),
    datasets: [
        {
            label: 'Desenvolvimento',
            data: weeklyData.value.map(d => d.development_minutes),
            backgroundColor: '#22c55e',
            borderRadius: 4,
            barPercentage: 0.7,
        },
        {
            label: 'Suporte',
            data: weeklyData.value.map(d => d.support_minutes),
            backgroundColor: '#f59e0b',
            borderRadius: 4,
            barPercentage: 0.7,
        },
    ],
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
                label: (ctx) => ` ${ctx.dataset.label}: ${formatDuration(ctx.parsed)}`,
            },
        },
    },
}

function categoryColor(name) {
    const map = {
        'Desenvolvimento': '#22c55e',
        'Bug': '#ef4444',
        'Suporte': '#f59e0b',
        'Investigação': '#8b5cf6',
        'Reunião': '#ec4899',
        'Documentação': '#14b8a6',
        'Estudo': '#a855f7',
    }
    return map[name] || '#6366f1'
}

function categoryPercent(minutes) {
    return ((minutes / totalMinutes.value) * 100).toFixed(0)
}

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
        const res = await fetch('/api/reports/send-monthly', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ include_in_progress: includeInProgress }),
        })
        const data = await res.json()
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
        const res = await fetch('/api/stats/daily')
        weeklyData.value = await res.json()
    } catch (e) {
        console.error('Failed to load weekly data', e)
    }
})
</script>
