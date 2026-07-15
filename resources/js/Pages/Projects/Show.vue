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
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ project.name }}</h1>
                                <p v-if="project.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 whitespace-pre-wrap">{{ project.description }}</p>
                            </div>
                            <button @click="openEdit"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors shrink-0">
                                Editar
                            </button>
                        </div>
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

            <div v-if="localLinks.length" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Links</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <a v-for="link in localLinks" :key="link.id" :href="link.url" target="_blank" rel="noopener noreferrer"
                        class="block bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:border-gray-300 dark:hover:border-gray-600 transition-colors group">
                        <div v-if="link.image_url" class="h-32 bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            <img :src="link.image_url" :alt="link.title" class="w-full h-full object-cover" @error="onImgError($event)" />
                        </div>
                        <div class="p-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:underline">{{ link.title }}</div>
                            <p v-if="link.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ link.description }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-400 truncate">{{ extractDomain(link.url) }}</span>
                                <button @click.prevent="confirmDeleteLink = link" class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Remover
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Adicionar Link</h3>
                <form @submit.prevent="addLink" class="flex items-center gap-3">
                    <input v-model="newLinkUrl" type="url" placeholder="https://..."
                        class="flex-1 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                    <button type="submit" :disabled="!newLinkUrl.trim() || addingLink"
                        class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors disabled:opacity-50 shrink-0">
                        {{ addingLink ? 'Buscando...' : 'Adicionar' }}
                    </button>
                </form>
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

            <Modal :show="showEditModal" @close="showEditModal = false" max-width="md">
                <div class="p-6 space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Editar Projeto</h2>
                    <form @submit.prevent="saveEdit" class="space-y-4">
                        <div>
                            <InputLabel for="s-name" value="Nome" />
                            <TextInput id="s-name" v-model="editForm.name" class="mt-1 w-full" required />
                            <InputError :message="editForm.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="s-desc" value="Descrição" />
                            <textarea id="s-desc" v-model="editForm.description" rows="2"
                                class="mt-1 w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400 resize-y" />
                            <InputError :message="editForm.errors.description" />
                        </div>
                        <div>
                            <InputLabel for="s-color" value="Cor" />
                            <input id="s-color" v-model="editForm.color" type="color"
                                class="mt-1 block w-12 h-9 rounded-md border-gray-300 shadow-sm cursor-pointer focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700" />
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-800">
                            <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="editForm.processing">Salvar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal :show="!!confirmDeleteLink" @close="confirmDeleteLink = null" max-width="sm">
                <div class="p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Remover Link</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Remover <strong>{{ confirmDeleteLink?.title }}</strong>?
                    </p>
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-800">
                        <button @click="confirmDeleteLink = null"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button @click="doDeleteLink"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 dark:bg-red-700 rounded-lg hover:bg-red-700">
                            Confirmar Exclusão
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { Doughnut, Bar } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement } from 'chart.js'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { useToast } from '@/Composables/useToast'
import axios from 'axios'

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement)

const props = defineProps({
    project: Object,
    activities: Object,
    stats: Object,
    categoryDistribution: Array,
    topUsers: Array,
    monthlyHistory: Array,
})

const newLinkUrl = ref('')
const addingLink = ref(false)
const localLinks = ref([...(props.project?.links || [])])
const showEditModal = ref(false)
const confirmDeleteLink = ref(null)

const editForm = useForm({
    name: props.project?.name || '',
    description: props.project?.description || '',
    color: props.project?.color || '#6366f1',
})

function openEdit() {
    editForm.name = props.project.name
    editForm.description = props.project.description || ''
    editForm.color = props.project.color || '#6366f1'
    editForm.clearErrors()
    showEditModal.value = true
}

function saveEdit() {
    editForm.put(route('projects.update', props.project.id), {
        onSuccess: () => { showEditModal.value = false }
    })
}

async function addLink() {
    if (!newLinkUrl.value.trim() || addingLink.value) return
    addingLink.value = true
    try {
        const res = await axios.post(`/projects/${props.project.id}/links`, { url: newLinkUrl.value.trim() })
        localLinks.value.push(res.data)
        newLinkUrl.value = ''
    } catch (e) {
        if (e.response?.status === 409) {
            useToast().error('Este link já foi adicionado.')
        } else {
            console.error('Erro ao adicionar link', e)
        }
    } finally {
        addingLink.value = false
    }
}

async function doDeleteLink() {
    if (!confirmDeleteLink.value) return
    try {
        await axios.delete(`/projects/${props.project.id}/links/${confirmDeleteLink.value.id}`)
        const idx = localLinks.value.indexOf(confirmDeleteLink.value)
        if (idx !== -1) localLinks.value.splice(idx, 1)
        confirmDeleteLink.value = null
    } catch (e) {
        console.error('Erro ao remover link', e)
    }
}

function extractDomain(url) {
    try { return new URL(url).hostname } catch { return url }
}

function onImgError(e) {
    e.target.parentElement.style.display = 'none'
}

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
            backgroundColor: '#1f2937', titleColor: '#f3f4f6', bodyColor: '#d1d5db',
            padding: 10, cornerRadius: 8,
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
        x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } } },
        y: { grid: { color: '#37415120' }, ticks: { color: '#6b7280', font: { size: 11 } }, beginAtZero: true },
    },
    plugins: {
        legend: { display: false },
        tooltip: { backgroundColor: '#1f2937', titleColor: '#f3f4f6', bodyColor: '#d1d5db', padding: 10, cornerRadius: 8, callbacks: { label: (ctx) => ` ${ctx.parsed.y}h` } },
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
    return new Date(dateStr).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

function formatDuration(minutes) {
    if (!minutes) return '-'
    if (minutes < 60) return `${minutes}m`
    return `${Math.floor(minutes / 60)}h${minutes % 60 > 0 ? (minutes % 60) + 'm' : ''}`
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
