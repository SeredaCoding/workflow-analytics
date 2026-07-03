<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
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
                        class="rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Horas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ monthly.total_hours }}h</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dias com Atividade</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ monthly.active_days }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Interrupções</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ monthly.interruptions }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Atividades</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ monthly.total_activities }}</p>
                </div>
            </div>

            <div v-if="categoryDistribution.length > 0" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribuição por Categoria</h2>
                <div class="space-y-3">
                    <div v-for="cat in categoryDistribution" :key="cat.name" class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color }"></span>
                        <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ cat.name }}</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ cat.percentage }}%</span>
                        <div class="w-32 bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                            <div class="h-2 rounded-full" :style="{ width: cat.percentage + '%', backgroundColor: cat.color }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumo Diário</h2>
                    <div v-html="dailyBreakdownHtml" class="text-sm text-gray-600 dark:text-gray-400 prose prose-sm max-w-none dark:prose-invert"></div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resumo Semanal</h2>
                    <div v-html="weeklySummaryHtml" class="text-sm text-gray-600 dark:text-gray-400 prose prose-sm max-w-none dark:prose-invert"></div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Atividades</h2>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <div v-for="a in activities" :key="a.id"
                        class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: a.category_color || '#6b7280' }"></span>
                            <div>
                                <span class="text-gray-900 dark:text-white font-medium">{{ a.title }}</span>
                                <span class="text-gray-500 dark:text-gray-400 ml-2">{{ a.category }}</span>
                            </div>
                        </div>
                        <span class="text-gray-600 dark:text-gray-400 text-xs">{{ a.duration ? a.duration.toFixed(1) + 'min' : '—' }}</span>
                    </div>
                    <div v-if="activities.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8 text-sm">
                        Nenhuma atividade registrada neste mês.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user: Object,
    month: String,
    monthly: Object,
    categoryDistribution: Array,
    dailyBreakdownHtml: String,
    weeklySummaryHtml: String,
    topActivitiesHtml: String,
    activities: Array,
})

function changeMonth(e) {
    const month = e.target.value
    router.get(route('supervisor.users.show', props.user.id), { month }, { preserveState: true })
}
</script>
