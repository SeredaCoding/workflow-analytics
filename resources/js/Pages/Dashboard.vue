<template>
    <AppLayout :in-progress="inProgress">
        <div class="max-w-6xl mx-auto space-y-8">
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Dashboard</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ dateDisplay }}
                    </p>
                </div>
                <span class="text-xs text-gray-400 font-mono">{{ hoursWorked }}h hoje</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard label="Horas Trabalhadas" :value="stats.total_hours" unit="h" color="blue" />
                <StatCard label="Interrupções" :value="stats.interruptions_count" unit="" color="red" />
                <StatCard label="Desenvolvimento" :value="devHours" unit="h" color="green" />
                <StatCard label="Suporte" :value="supportHours" unit="h" color="yellow" />
            </div>

            <div>
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold">Linha do Tempo</h3>
                </div>
                <div class="relative">
                    <div v-if="extendedTimeline.length === 0" class="text-center py-12 text-gray-500">
                        Nenhuma atividade registrada nos últimos 3 dias.
                    </div>
                    <div class="space-y-1">
                        <template v-for="(day, di) in groupedTimeline" :key="di">
                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ day.dayLabel }}</div>
                                <div class="text-xs text-gray-400 font-mono ml-auto">{{ formatDayTotal(day) }}</div>
                            </div>
                            <template v-for="(group, gi) in day.groups" :key="gi">
                                <div class="flex items-center gap-3 px-4 py-2">
                                    <div class="text-xs font-medium text-gray-400 font-mono">{{ group.label }}</div>
                                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-800" />
                                </div>
                                <div v-for="item in group.items" :key="item.id">
                                    <div v-if="item.type === 'interruption'" class="flex items-center gap-3 pl-8 pr-4 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <div class="w-1.5 h-1.5 rounded-full bg-red-400" />
                                            <div class="w-px h-4 bg-gray-200 dark:bg-gray-800" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm text-red-600 dark:text-red-400">{{ item.title }}</div>
                                            <div class="text-xs text-gray-500">
                                                <span class="text-red-500 font-medium">Interrupção</span>
                                                <span v-if="item.project"> · {{ item.project }}</span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">{{ formatItemTime(item) }}</div>
                                    </div>
                                    <div v-else class="flex items-start gap-3 px-4 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors group">
                                        <div class="flex flex-col items-center gap-0.5 pt-0.5">
                                            <div class="w-2 h-2 rounded-full" :class="item.status === 'in_progress' ? 'bg-green-500 animate-pulse' : ''"
                                                :style="item.status !== 'in_progress' ? { backgroundColor: item.category_color || '#6366f1' } : {}" />
                                            <div class="w-px h-full min-h-[24px] bg-gray-200 dark:bg-gray-800" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium truncate" :class="item.status === 'in_progress' ? 'text-green-600 dark:text-green-400' : ''">
                                                {{ item.title }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ item.category }}
                                                <span v-if="item.project"> · {{ item.project }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <div class="text-sm font-mono tabular-nums" :class="item.status === 'in_progress' ? 'text-green-600 dark:text-green-400' : 'text-gray-500'">
                                                <template v-if="item.status === 'in_progress'">{{ liveElapsed }}</template>
                                                <template v-else>{{ formatDuration(item.duration) }}</template>
                                            </div>
                                            <div class="text-xs text-gray-400 hidden sm:block">{{ formatItemTime(item) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'

const props = defineProps({
    stats: Object,
    inProgress: Object,
    timeline: Array,
})

const liveElapsed = ref('00:00')
let timer = null
let refreshInterval = null

const dateDisplay = computed(() => {
    return new Date().toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

const devHours = computed(() => {
    if (!props.stats?.development_minutes) return '0.0'
    return (props.stats.development_minutes / 60).toFixed(1)
})

const supportHours = computed(() => {
    if (!props.stats?.support_minutes) return '0.0'
    return (props.stats.support_minutes / 60).toFixed(1)
})

const hoursWorked = computed(() => {
    if (!props.stats?.total_minutes) return '0.0'
    return (props.stats.total_minutes / 60).toFixed(1)
})

const timestamp = ref(Date.now())

const extendedTimeline = computed(() => {
    const items = [...props.timeline]
    if (props.inProgress) {
        const exists = items.some(i => i.id === props.inProgress.id)
        if (!exists) {
            items.push({
                id: props.inProgress.id,
                title: props.inProgress.title,
                type: props.inProgress.type || 'activity',
                parent_id: props.inProgress.parent_id,
                category: props.inProgress.category,
                category_color: props.inProgress.category_color,
                project: props.inProgress.project,
                started_at: props.inProgress.started_at,
                ended_at: null,
                duration: null,
                status: 'in_progress',
            })
        }
    }
    return items
})

function getHourGroup(h) {
    return String(h).padStart(2, '0') + ':00 — ' + String(h + 1).padStart(2, '0') + ':00'
}

const groupedTimeline = computed(() => {
    const sorted = [...extendedTimeline.value].sort((a, b) => {
        return new Date(b.started_at) - new Date(a.started_at)
    })

    const byDate = {}
    for (const item of sorted) {
        const dateKey = item.date || new Date(item.started_at).toISOString().slice(0, 10)
        if (!byDate[dateKey]) {
            byDate[dateKey] = []
        }
        byDate[dateKey].push(item)
    }

    const dayGroups = []
    for (const [dateKey, items] of Object.entries(byDate)) {
        const groups = []
        let currentHour = null
        let currentGroup = null

        for (const item of items) {
            const hour = new Date(item.started_at).getHours()
            if (hour !== currentHour) {
                currentHour = hour
                currentGroup = { label: getHourGroup(hour), items: [] }
                groups.push(currentGroup)
            }
            currentGroup.items.push(item)
        }

        const d = new Date(dateKey + 'T12:00:00')
        const dayLabel = d.toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit' })

        dayGroups.push({
            date: dateKey,
            dayLabel,
            groups,
        })
    }

    return dayGroups
})

function formatDayTotal(day) {
    let total = 0
    for (const group of day.groups) {
        for (const item of group.items) {
            if (item.duration) total += item.duration
        }
    }
    return total ? formatDuration(total) : '-'
}

function formatDuration(minutes) {
    if (!minutes && minutes !== 0) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}

function formatItemTime(item) {
    const start = new Date(item.started_at)
    const startStr = String(start.getHours()).padStart(2, '0') + ':' + String(start.getMinutes()).padStart(2, '0')
    if (item.ended_at) {
        const end = new Date(item.ended_at)
        const endStr = String(end.getHours()).padStart(2, '0') + ':' + String(end.getMinutes()).padStart(2, '0')
        return `${startStr} - ${endStr}`
    }
    return startStr
}

function updateElapsed() {
    if (!props.inProgress?.started_at) {
        liveElapsed.value = '00:00'
        return
    }
    const start = new Date(props.inProgress.started_at).getTime()
    const now = Date.now()
    const diff = Math.floor((now - start) / 1000)
    const h = String(Math.floor(diff / 3600)).padStart(2, '0')
    const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0')
    const s = String(diff % 60).padStart(2, '0')
    liveElapsed.value = `${h}:${m}:${s}`
}

function refresh() {
    router.visit('/', { preserveState: true, preserveScroll: true })
}

onMounted(() => {
    updateElapsed()
    timer = setInterval(updateElapsed, 1000)
    refreshInterval = setInterval(refresh, 30000)
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
    if (refreshInterval) clearInterval(refreshInterval)
})
</script>
