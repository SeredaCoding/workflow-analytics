<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Visão Geral do Setor</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Produtividade da equipe</p>
                </div>
                <input type="month" :value="month" @change="changeMonth"
                    class="text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white text-gray-900 dark:text-gray-100" />
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <StatCard label="Total de Horas" :value="total_hours" unit="h" color="blue" />
                <StatCard label="Usuários Ativos" :value="`${active_users} / ${total_users}`" color="green" />
                <StatCard label="Média por Usuário" :value="averageHours" unit="h" />
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Desenvolvimento</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ dev_hours }}</span>
                        <span class="text-lg text-gray-400">h</span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Suporte</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ support_hours }}</span>
                        <span class="text-lg text-gray-400">h</span>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Reunião</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ meeting_hours }}</span>
                        <span class="text-lg text-gray-400">h</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Usuário</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Setor</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Total</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Dev</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Suporte</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Reunião</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Int.</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Atividade</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-500 dark:text-gray-400 shrink-0">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ user.name }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ user.sector }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span :class="hoursBadgeClass(user.total_hours)">{{ user.total_hours }}h</span>
                            </td>
                            <td class="px-4 py-3 text-center text-indigo-600 dark:text-indigo-400 font-medium whitespace-nowrap">{{ user.dev_hours }}h</td>
                            <td class="px-4 py-3 text-center text-purple-600 dark:text-purple-400 font-medium whitespace-nowrap">{{ user.sup_hours }}h</td>
                            <td class="px-4 py-3 text-center text-amber-600 dark:text-amber-400 font-medium whitespace-nowrap">{{ user.mtg_hours }}h</td>
                            <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ user.interruptions }}</td>
                            <td class="px-4 py-3 max-w-[160px] overflow-hidden">
                                <span v-if="user.inProgress" @click.stop="viewDetail(user.inProgress)"
                                    class="flex items-center gap-1.5 min-w-0 cursor-pointer hover:opacity-75 transition-opacity">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse shrink-0"></span>
                                    <span class="text-gray-500 dark:text-gray-400 text-xs truncate">{{ user.inProgress.title }}</span>
                                </span>
                                <span v-else class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <Link :href="route('supervisor.users.show', user.id)"
                                    class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white underline underline-offset-2">
                                    Detalhes
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Nenhum usuário encontrado nos setores.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ActivityDetailModal v-if="viewingActivity" :activity="viewingActivity" @close="viewingActivity = null" />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import ActivityDetailModal from '@/Components/ActivityDetailModal.vue'

const props = defineProps({
    sectors: Array,
    users: Array,
    month: String,
    total_hours: Number,
    total_minutes: Number,
    active_users: Number,
    total_users: Number,
    dev_hours: Number,
    support_hours: Number,
    meeting_hours: Number,
    avg_focus: Number,
})

const averageHours = computed(() => {
    if (props.total_users === 0) return '0.0'
    return (props.total_hours / props.total_users).toFixed(1)
})

function hoursBadgeClass(hours) {
    const base = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium'
    if (hours >= 120) return `${base} bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300`
    if (hours >= 60) return `${base} bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300`
    if (hours > 0) return `${base} bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300`
    return `${base} bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400`
}

function changeMonth(e) {
    const month = e.target.value
    router.get(route('supervisor.sector.index'), { month }, { preserveState: true })
}

const viewingActivity = ref(null)

function viewDetail(activity) {
    viewingActivity.value = activity
}
</script>
