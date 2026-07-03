<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Meu Setor</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visão geral da produtividade do seu setor</p>
            </div>

            <div class="flex items-center gap-2">
                <input type="month" :value="month" @change="changeMonth"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Horas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ total_hours }}h</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Usuários Ativos</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ active_users }} / {{ total_users }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Média por Usuário</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ averageHours }}h</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Setores</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ sectors.length }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Usuário</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Setor</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Horas no Mês</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Interrupções</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Atividade Atual</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ user.sector }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="hoursBadgeClass(user.total_hours)">{{ user.total_hours }}h</span>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">{{ user.interruptions }}</td>
                            <td class="px-6 py-4">
                                <span v-if="user.inProgress" class="inline-flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                    <span class="text-gray-600 dark:text-gray-400 text-xs">{{ user.inProgress.title }}</span>
                                </span>
                                <span v-else class="text-gray-400 dark:text-gray-500 text-xs">—</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('supervisor.users.show', user.id)"
                                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    Detalhes
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Nenhum usuário encontrado nos seus setores.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sectors: Array,
    users: Array,
    month: String,
    total_hours: Number,
    total_minutes: Number,
    active_users: Number,
    total_users: Number,
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
</script>
