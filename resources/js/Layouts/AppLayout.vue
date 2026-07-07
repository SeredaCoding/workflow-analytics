<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
        <div class="flex h-screen overflow-hidden">
            <aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col">
                <div class="p-6">
                    <ApplicationLogo class="w-full h-auto" />
                </div>
                <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
                    <template v-for="item in navItems" :key="item.label">
                        <div v-if="item.separator" class="border-t border-gray-200 dark:border-gray-800 my-2"></div>
                        <div v-else-if="item.children" class="space-y-1">
                            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 cursor-default">
                                <span v-html="item.icon" class="w-5 h-5" />
                                {{ item.label }}
                            </div>
                            <div class="ml-4 space-y-1">
                                <Link v-for="child in item.children" :key="child.href" :href="child.href"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                    :class="$page.url === child.href
                                        ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                                    {{ child.label }}
                                </Link>
                            </div>
                        </div>
                        <Link v-else :href="item.href"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                            :class="[
                                $page.url === item.href
                                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                item.stats && !statsClicked ? 'animate-stats-pulse' : '',
                            ]"
                            @click="item.stats ? markStatsClicked() : undefined">
                            <span v-html="item.icon" class="w-5 h-5" />
                            {{ item.label }}
                        </Link>
                    </template>
                </nav>
                <div class="p-3 border-t border-gray-200 dark:border-gray-800">
                    <div class="text-xs text-gray-500 px-3 py-2">
                        v0.1.0 MVP
                    </div>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                <div class="flex items-center justify-end px-6 py-3 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                                {{ $page.props.auth.user.name }}
                                <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                            <button @click="showReportProblem = true"
                                class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                Reportar Erro
                            </button>
                            <DropdownLink :href="route('logout')" method="post" as="button">Sair</DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <TimerBar
                    :in-progress="inProgress"
                    :latest-paused="latestPaused"
                    @start="showQuickStart = true"
                    @interrupt="showInterruption = true"
                    @manual="showManualEntry = true"
                />

                <main class="flex-1 overflow-y-auto p-6">
                    <slot />
                </main>
            </div>
        </div>

        <QuickStartModal v-if="showQuickStart" :categories="categories" :projects="projects" :modules="modules"
            @close="showQuickStart = false" @started="onStarted" />

        <InterruptionModal v-if="showInterruption"
            @close="showInterruption = false" @interrupted="onInterrupted" />

        <ManualEntryModal v-if="showManualEntry" :categories="categories" :projects="projects" :modules="modules"
            @close="showManualEntry = false" @registered="onRegistered" />

        <CommandPalette v-if="showCommandPalette" @close="showCommandPalette = false"
            @quick-start="showQuickStart = true" @manual-entry="showManualEntry = true"
            @interrupt="showInterruption = true" />

        <ReportProblemModal v-if="showReportProblem" @close="showReportProblem = false" />
        <FaqModal v-if="showFaq" :faqs="faqs" @close="showFaq = false" />

        <button @click="showFaq = true"
            class="fixed bottom-6 right-6 z-40 w-10 h-10 rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors flex items-center justify-center text-sm font-bold"
            title="FAQ">
            ?
        </button>

        <Toast />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import TimerBar from '@/Components/TimerBar.vue'
import QuickStartModal from '@/Components/QuickStartModal.vue'
import InterruptionModal from '@/Components/InterruptionModal.vue'
import ManualEntryModal from '@/Components/ManualEntryModal.vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import CommandPalette from '@/Components/CommandPalette.vue'
import ReportProblemModal from '@/Components/ReportProblemModal.vue'
import FaqModal from '@/Components/FaqModal.vue'
import Toast from '@/Components/Toast.vue'
import { useToast } from '@/Composables/useToast'

const page = usePage()
const toast = useToast()

defineProps({
    inProgress: Object,
})

const categories = computed(() => page.props.categories)
const projects = computed(() => page.props.projects)
const modules = computed(() => page.props.modules)
const faqs = computed(() => page.props.faqs || [])
const latestPaused = computed(() => page.props.latestPaused)
const hasStats = computed(() => page.props.hasStats)

const statsClicked = ref(localStorage.getItem('stats_visited') === 'true')

function markStatsClicked() {
    localStorage.setItem('stats_visited', 'true')
    statsClicked.value = true
}

const showQuickStart = ref(false)
const showInterruption = ref(false)
const showManualEntry = ref(false)
const showCommandPalette = ref(false)
const showReportProblem = ref(false)
const showFaq = ref(false)

function onKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault()
        showCommandPalette.value = !showCommandPalette.value
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.success(flash.success)
    if (flash?.error) toast.error(flash.error)
}, { immediate: true, deep: true })

const user = page.props.auth.user

const navItems = computed(() => {
    const items = [
        { label: 'Dashboard', href: '/', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>' },
        { label: 'Atividades', href: '/activities', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>' },
    ]

    if (hasStats.value) {
        items.push({ label: 'Estatísticas', href: '/stats', stats: true, icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>' })
    }

    items.push({ label: 'Projetos', href: '/projects', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>' })
    items.push({ label: 'Configurações', href: '/settings', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>' })
    items.push({ label: 'Perfil', href: '/profile', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' })

    if (user?.role_id === 2 || user?.role_id === 3) {
        items.splice(3, 0, {
            label: 'Meu Setor',
            href: '#',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            children: [
                { label: 'Visão Geral', href: '/supervisor/sector' },
                { label: 'Projetos', href: '/supervisor/projects' },
            ],
        })
    }

    if (user?.role_id === 3) {
        items.push({ separator: true })
        items.push({
            label: 'Admin',
            href: '#',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
            children: [
                { label: 'Setores', href: '/admin/sectors' },
                { label: 'Categorias', href: '/admin/categories' },
                { label: 'Módulos', href: '/admin/modules' },
                { label: 'Projetos', href: '/admin/projects' },
                { label: 'Usuários', href: '/admin/users' },
                { label: 'Relatórios', href: '/admin/problem-reports' },
                { label: 'FAQ', href: '/admin/faqs' },
            ],
        })
    }

    return items
})

function onStarted() {
    showQuickStart.value = false
    router.visit('/', { preserveState: false })
}

function onInterrupted() {
    showInterruption.value = false
    router.visit('/', { preserveState: false })
}

function onRegistered() {
    showManualEntry.value = false
    router.visit('/', { preserveState: false })
}
</script>

<style scoped>
@keyframes stats-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
    50% { box-shadow: 0 0 10px 3px rgba(99, 102, 241, 0.25); }
}
.animate-stats-pulse {
    animation: stats-pulse 2s ease-in-out infinite;
}
</style>
