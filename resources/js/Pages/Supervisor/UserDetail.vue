<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                        <Link :href="route('supervisor.sector.index')" class="hover:text-gray-700 dark:hover:text-gray-300">Meu Setor</Link>
                        <span>/</span>
                        <span class="text-gray-900 dark:text-white font-medium">{{ user.name }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }} · {{ user.sector }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="month" :value="month" @change="changeMonth"
                        class="text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white text-gray-900 dark:text-gray-100" />
                </div>
            </div>

            <div v-if="userInProgress" class="bg-green-50 dark:bg-green-950/20 rounded-xl border border-green-200 dark:border-green-900/50 p-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse shrink-0"></span>
                        <div class="min-w-0">
                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">Em andamento</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ userInProgress.title }}</h3>
                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5 flex-wrap">
                                <span>● {{ userInProgress.category }}</span>
                                <span v-if="userInProgress.project">· {{ userInProgress.project }}</span>
                                <span v-if="userInProgress.context">· {{ userInProgress.context }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-3xl font-mono font-bold tabular-nums text-green-600 dark:text-green-400 shrink-0">{{ userElapsed }}</div>
                </div>
            </div>

            <div v-if="hasAnyData" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Total no Mês" :value="monthly.total_hours" unit="h" color="blue" />
                <StatCard label="Interrupções" :value="monthly.interruptions" unit="" color="red" />
                <StatCard label="Méd. Foco" :value="monthly.avg_focus_minutes" unit="min" color="green" />
                <StatCard label="Reuniões" :value="meetingHours" unit="h" color="purple" />
            </div>

            <div v-if="categoryDistribution.length > 0 || projectDistribution.length > 0 || contextDistribution.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-if="categoryDistribution.length > 0" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Categorias</h3>
                    <div class="flex items-center justify-center" style="height: 190px">
                        <Doughnut :data="categoryChartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="cat in categoryDistribution" :key="cat.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: cat.color }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ cat.name }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ catPercent(cat.minutes) }}%</span>
                        </div>
                    </div>
                </div>
                <div v-if="projectDistribution.length > 0" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Projetos</h3>
                    <div class="flex items-center justify-center" style="height: 190px">
                        <Doughnut :data="projectChartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="proj in projectDistribution" :key="proj.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: proj.color || projectColor(proj.name) }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ proj.name }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ projPercent(proj.minutes) }}%</span>
                        </div>
                    </div>
                </div>
                <div v-if="contextDistribution.length > 0" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Módulos</h3>
                    <div class="flex items-center justify-center" style="height: 190px">
                        <Doughnut :data="contextChartData" :options="doughnutOptions" />
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-4">
                        <div v-for="mod in contextDistribution" :key="mod.name" class="flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: mod.color || '#6366f1' }" />
                            <span class="text-gray-600 dark:text-gray-400">{{ mod.name }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ ctxPercent(mod.minutes) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Resumo Diário</h3>
                    <div v-html="dailyBreakdownHtml" class="text-sm text-gray-600 dark:text-gray-400"></div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Resumo Semanal</h3>
                    <div v-html="weeklySummaryHtml" class="text-sm text-gray-600 dark:text-gray-400"></div>
                </div>
            </div>

            <div v-if="topActivitiesHtml" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Top Atividades</h3>
                <div v-html="topActivitiesHtml" class="text-sm text-gray-600 dark:text-gray-400"></div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Todas as Atividades</h3>
                <div class="space-y-1 max-h-96 overflow-y-auto">
                    <div v-for="a in activities" :key="a.id"
                        @click="viewDetail(a)"
                        class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 text-sm cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: a.category_color || '#6b7280' }"></span>
                            <span class="text-gray-900 dark:text-white font-medium truncate">{{ a.title }}</span>
                            <span class="text-gray-500 dark:text-gray-400 shrink-0">{{ a.category }}</span>
                        </div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs font-mono shrink-0 ml-2">{{ formatDuration(a.duration) }}</span>
                    </div>
                    <div v-if="activities.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8 text-sm">
                        Nenhuma atividade registrada neste mês.
                    </div>
                </div>
            </div>
        </div>

        <ActivityDetailModal v-if="viewingActivity" :activity="viewingActivity" @close="viewingActivity = null" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
import StatCard from '@/Components/StatCard.vue'
import ActivityDetailModal from '@/Components/ActivityDetailModal.vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement)

const props = defineProps({
    user: Object,
    month: String,
    monthly: Object,
    categoryDistribution: Array,
    projectDistribution: Array,
    contextDistribution: Array,
    dailyBreakdownHtml: String,
    weeklySummaryHtml: String,
    topActivitiesHtml: String,
    activities: Array,
    userInProgress: Object,
})

const hasAnyData = computed(() => (props.monthly?.total_minutes ?? 0) > 0)

const meetingHours = computed(() => {
    if (!props.monthly?.meeting_minutes) return '0.0'
    return (props.monthly.meeting_minutes / 60).toFixed(1)
})

const catTotal = computed(() => props.categoryDistribution?.reduce((s, c) => s + c.minutes, 0) || 1)
const projTotal = computed(() => props.projectDistribution?.reduce((s, p) => s + p.minutes, 0) || 1)
const ctxTotal = computed(() => props.contextDistribution?.reduce((s, c) => s + c.minutes, 0) || 1)

function catPercent(minutes) { return ((minutes / catTotal.value) * 100).toFixed(0) }
function projPercent(minutes) { return ((minutes / projTotal.value) * 100).toFixed(0) }
function ctxPercent(minutes) { return ((minutes / ctxTotal.value) * 100).toFixed(0) }

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

function formatDuration(minutes) {
    if (!minutes) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}

function changeMonth(e) {
    const month = e.target.value
    router.get(route('supervisor.users.show', props.user.id), { month }, { preserveState: true })
}

const viewingActivity = ref(null)
const userElapsed = ref('00:00')
let userTimer = null

function viewDetail(activity) {
    viewingActivity.value = activity
}

function updateUserElapsed() {
    if (!props.userInProgress?.started_at) {
        userElapsed.value = '00:00'
        return
    }
    const start = new Date(props.userInProgress.started_at).getTime()
    const now = Date.now()
    const raw = Math.floor((now - start) / 1000)

    const lunchStart = page.props.lunch_start
    const lunchEnd = page.props.lunch_end
    let deducted = 0
    if (lunchStart && lunchEnd) {
        const lh = new Date(now)
        const [lsh, lsm] = lunchStart.split(':')
        const [leh, lem] = lunchEnd.split(':')
        const ls = new Date(now); ls.setHours(parseInt(lsh), parseInt(lsm), 0, 0)
        const le = new Date(now); le.setHours(parseInt(leh), parseInt(lem), 0, 0)
        const oStart = Math.max(start, ls.getTime())
        const oEnd = Math.min(now, le.getTime())
        deducted = Math.max(0, Math.floor((oEnd - oStart) / 1000))
    }

    const diff = Math.max(0, raw - deducted)
    const h = String(Math.floor(diff / 3600)).padStart(2, '0')
    const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0')
    const s = String(diff % 60).padStart(2, '0')
    userElapsed.value = `${h}:${m}:${s}`
}

onMounted(() => {
    updateUserElapsed()
    userTimer = setInterval(updateUserElapsed, 1000)
})

onUnmounted(() => {
    if (userTimer) clearInterval(userTimer)
})
</script>
