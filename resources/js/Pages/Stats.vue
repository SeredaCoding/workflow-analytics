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
                    <div class="space-y-3">
                        <div v-for="cat in categoryDistribution" :key="cat.name" class="flex items-center gap-3">
                            <div class="flex-1">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>{{ cat.name }}</span>
                                    <span class="text-gray-500">{{ categoryPercent(cat.minutes) }}%</span>
                                </div>
                                <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all" :style="{ width: categoryPercent(cat.minutes) + '%', backgroundColor: categoryColor(cat.name) }" />
                                </div>
                            </div>
                            <span class="text-sm text-gray-500 font-mono">{{ formatDuration(cat.minutes) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <h3 class="text-sm font-medium text-gray-500 mb-4">Últimos 7 Dias</h3>
                    <div class="space-y-3">
                        <div v-for="day in weeklyData" :key="day.date" class="flex items-center gap-3">
                            <span class="w-8 text-sm font-medium">{{ day.label }}</span>
                            <div class="flex-1 h-8 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden flex">
                                <div v-if="day.development_minutes > 0" class="h-full bg-green-500/30" :style="{ width: dayPercent(day.development_minutes) + '%' }" title="Desenvolvimento" />
                                <div v-if="day.support_minutes > 0" class="h-full bg-yellow-500/30" :style="{ width: dayPercent(day.support_minutes) + '%' }" title="Suporte" />
                            </div>
                            <span class="text-sm text-gray-500 font-mono w-16 text-right">{{ formatDuration(day.total_minutes) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'

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

function categoryPercent(minutes) {
    return ((minutes / totalMinutes.value) * 100).toFixed(0)
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

function formatDuration(minutes) {
    if (!minutes) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}

function dayPercent(minutes) {
    const max = Math.max(...weeklyData.value.map(d => d.total_minutes), 1)
    return (minutes / max) * 100
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
