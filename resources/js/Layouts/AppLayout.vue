<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
        <div class="flex h-screen overflow-hidden">
            <!-- Desktop sidebar -->
            <aside class="hidden lg:flex w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex-col overflow-hidden">
                <div class="p-4">
                    <ApplicationLogo class="w-full h-auto" />
                </div>
                <nav class="flex-1 px-2.5 space-y-0.5 overflow-y-auto">
                    <template v-for="item in navItems" :key="item.label || item.section">
                        <div v-if="item.section"
                            class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 select-none">
                            {{ item.section }}
                        </div>
                        <div v-else-if="item.accordion" class="space-y-0.5">
                            <button @click="toggleAccordion(item.accordion)"
                                class="flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-sm font-medium transition-colors text-left"
                                :class="isAccordionActive(item.accordion)
                                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                                <span v-html="item.icon" class="w-5 h-5 shrink-0" />
                                <span class="flex-1">{{ item.label }}</span>
                                <svg class="w-4 h-4 transition-transform duration-200 shrink-0"
                                    :class="accordions[item.accordion] ? 'rotate-90' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div v-show="accordions[item.accordion]" class="ml-3 space-y-0.5">
                                <Link v-for="child in item.children" :key="child.href" :href="child.href"
                                    class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                    :class="$page.url === child.href
                                        ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                                    {{ child.label }}
                                </Link>
                            </div>
                        </div>
                        <Link v-else :href="item.href"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                            :class="[
                                $page.url === item.href
                                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                item.stats && !statsClicked ? 'animate-stats-pulse' : '',
                            ]"
                            @click="item.stats ? markStatsClicked() : undefined">
                            <span v-html="item.icon" class="w-5 h-5 shrink-0" />
                            {{ item.label }}
                        </Link>
                    </template>
                </nav>
                <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">
                    <Link :href="route('versions.index')"
                        class="block text-xs text-gray-500 hover:text-gray-900 dark:hover:text-white px-3 py-2 transition-colors">
                        v{{ $page.props.app_version }}
                    </Link>
                </div>
            </aside>

            <!-- Mobile drawer overlay -->
            <div v-if="showMobileMenu" class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="showMobileMenu = false"></div>

            <!-- Mobile drawer -->
            <aside :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col transform transition-transform duration-300 ease-in-out lg:hidden overflow-hidden',
                showMobileMenu ? 'translate-x-0' : '-translate-x-full',
            ]">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
                    <ApplicationLogo class="h-12 w-auto" />
                    <button @click="showMobileMenu = false"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 px-2.5 py-3 space-y-0.5 overflow-y-auto">
                    <template v-for="item in navItems" :key="item.label || item.section">
                        <div v-if="item.section"
                            class="px-3 pt-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 select-none">
                            {{ item.section }}
                        </div>
                        <div v-else-if="item.accordion" class="space-y-0.5">
                            <button @click="toggleAccordion(item.accordion)"
                                class="flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-sm font-medium transition-colors text-left"
                                :class="isAccordionActive(item.accordion)
                                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                                <span v-html="item.icon" class="w-5 h-5 shrink-0" />
                                <span class="flex-1">{{ item.label }}</span>
                                <svg class="w-4 h-4 transition-transform duration-200 shrink-0"
                                    :class="accordions[item.accordion] ? 'rotate-90' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6"/>
                                </svg>
                            </button>
                            <div v-show="accordions[item.accordion]" class="ml-3 space-y-0.5">
                                <Link v-for="child in item.children" :key="child.href" :href="child.href" @click="showMobileMenu = false"
                                    class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                    :class="$page.url === child.href
                                        ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                                    {{ child.label }}
                                </Link>
                            </div>
                        </div>
                        <Link v-else :href="item.href"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                            :class="[
                                $page.url === item.href
                                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                item.stats && !statsClicked ? 'animate-stats-pulse' : '',
                            ]"
                            @click="showMobileMenu = false; item.stats && markStatsClicked()">
                            <span v-html="item.icon" class="w-5 h-5 shrink-0" />
                            {{ item.label }}
                        </Link>
                    </template>
                </nav>
                <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">
                    <Link :href="route('versions.index')"
                        class="block text-xs text-gray-500 hover:text-gray-900 dark:hover:text-white px-3 py-2 transition-colors">
                        v{{ $page.props.app_version }}
                    </Link>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <button @click="showMobileMenu = true"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
                        </svg>
                    </button>

                    <div class="hidden lg:block"></div>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                                <span class="hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                                <span class="sm:hidden w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
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

                <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
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
            class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-40 w-10 h-10 rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors flex items-center justify-center text-sm font-bold"
            title="FAQ">
            ?
        </button>

        <Toast />
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
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

const categories = computed(() => page.props.categories)
const projects = computed(() => page.props.projects)
const modules = computed(() => page.props.modules)
const faqs = computed(() => page.props.faqs || [])
const latestPaused = computed(() => page.props.latestPaused)
const inProgress = computed(() => page.props.inProgress)
const hasStats = computed(() => page.props.hasStats)

const statsClicked = ref(localStorage.getItem('stats_visited') === 'true')

function markStatsClicked() {
    localStorage.setItem('stats_visited', 'true')
    statsClicked.value = true
}

const accordions = reactive({
    projetos: localStorage.getItem('sidebar_acc_projetos') === 'true',
    sistema: localStorage.getItem('sidebar_acc_sistema') === 'true',
    conteudo: localStorage.getItem('sidebar_acc_conteudo') === 'true',
})

function toggleAccordion(name) {
    accordions[name] = !accordions[name]
    localStorage.setItem(`sidebar_acc_${name}`, accordions[name])
}

function isAccordionActive(name) {
    const children = navItems.value
        .filter(i => i.accordion === name)
        .flatMap(i => i.children || [])
    return children.some(c => c.href === page.url)
}

watch(() => page.url, (url) => {
    if (url.startsWith('/projects') || url.startsWith('/supervisor/projects') || url.startsWith('/admin/projects')) {
        accordions.projetos = true
    }
    if (/^\/admin\/(sectors|categories|modules|users)(\/|$)/.test(url)) {
        accordions.sistema = true
    }
    if (/^\/admin\/(problem-reports|faqs|faq-topics)(\/|$)/.test(url)) {
        accordions.conteudo = true
    }
}, { immediate: true })

const showMobileMenu = ref(false)
const showQuickStart = ref(false)
const showInterruption = ref(false)
const showManualEntry = ref(false)
const showCommandPalette = ref(false)
const showReportProblem = ref(false)
const showFaq = ref(false)

function closeMobileMenu() {
    showMobileMenu.value = false
}

watch(() => page.url, () => {
    closeMobileMenu()
})

function onKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault()
        showCommandPalette.value = !showCommandPalette.value
    }
    if (e.key === 'Escape' && showMobileMenu.value) {
        closeMobileMenu()
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))

watch(showMobileMenu, (open) => {
    if (open) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = ''
    }
})

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

    const projectChildren = []

    if (user?.role_id === 2) {
        projectChildren.push({ label: 'Projetos do Setor', href: '/supervisor/projects' })
    } else if (user?.role_id === 1) {
        projectChildren.push({ label: 'Visão Geral', href: '/projects' })
    }

    if (user?.role_id === 3) {
        projectChildren.push({ label: 'Gerenciar', href: '/admin/projects' })
    }

    items.push(
        {
            label: 'Projetos',
            accordion: 'projetos',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
            children: projectChildren,
        },
    )

    items.push(
        { section: 'Conta', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>' },
        { label: 'Configurações', href: '/settings', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>' },
        { label: 'Perfil', href: '/profile', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' },
    )

    if (user?.role_id === 2 || user?.role_id === 3) {
        items.push(
            { section: 'Supervisão', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>' },
            { label: 'Visão Geral do Setor', href: '/supervisor/sector', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>' },
        )
    }

    if (user?.role_id === 3) {
        items.push({ section: 'Administração', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>' })
        items.push({
            label: 'Sistema',
            accordion: 'sistema',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
            children: [
                { label: 'Setores', href: '/admin/sectors' },
                { label: 'Categorias', href: '/admin/categories' },
                { label: 'Módulos', href: '/admin/modules' },
                { label: 'Usuários', href: '/admin/users' },
            ],
        })
        items.push({
            label: 'Conteúdo',
            accordion: 'conteudo',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
            children: [
                { label: 'Relatórios', href: '/admin/problem-reports' },
                { label: 'FAQ', href: '/admin/faqs' },
                { label: 'Versões', href: '/admin/versions' },
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
