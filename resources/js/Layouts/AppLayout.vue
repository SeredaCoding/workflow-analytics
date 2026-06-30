<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
        <div class="flex h-screen overflow-hidden">
            <aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col">
                <div class="p-6">
                    <h1 class="text-lg font-bold tracking-tight">WorkFlow Analytics</h1>
                </div>
                <nav class="flex-1 px-3 space-y-1">
                    <Link v-for="item in navItems" :key="item.href" :href="item.href"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="$page.url === item.href
                            ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                        <span v-html="item.icon" class="w-5 h-5" />
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="p-3 border-t border-gray-200 dark:border-gray-800">
                    <div class="text-xs text-gray-500 px-3 py-2">
                        v0.1.0 MVP
                    </div>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                <TimerBar
                    :in-progress="inProgress"
                    @start="showQuickStart = true"
                    @interrupt="showInterruption = true"
                    @manual="showManualEntry = true"
                />

                <main class="flex-1 overflow-y-auto p-6">
                    <slot />
                </main>
            </div>
        </div>

        <QuickStartModal v-if="showQuickStart" :categories="categories" :projects="projects"
            @close="showQuickStart = false" @started="onStarted" />

        <InterruptionModal v-if="showInterruption"
            @close="showInterruption = false" @interrupted="onInterrupted" />

        <ManualEntryModal v-if="showManualEntry" :categories="categories" :projects="projects"
            @close="showManualEntry = false" @registered="onRegistered" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import TimerBar from '@/Components/TimerBar.vue'
import QuickStartModal from '@/Components/QuickStartModal.vue'
import InterruptionModal from '@/Components/InterruptionModal.vue'
import ManualEntryModal from '@/Components/ManualEntryModal.vue'

const page = usePage()

defineProps({
    inProgress: Object,
})

const categories = computed(() => page.props.categories)
const projects = computed(() => page.props.projects)

const showQuickStart = ref(false)
const showInterruption = ref(false)
const showManualEntry = ref(false)

const navItems = [
    { label: 'Dashboard', href: '/', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>' },
    { label: 'Atividades', href: '/activities', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>' },
    { label: 'Estatísticas', href: '/stats', icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>' },
]

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
