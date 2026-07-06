<template>
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="$emit('start')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Nova Atividade
                </button>
                <button @click="$emit('manual')"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Registrar
                </button>

                <div v-if="inProgress" class="flex items-center gap-3 px-4 py-1.5 rounded-lg"
                    :class="isInterruption
                        ? 'bg-red-50 dark:bg-red-950/30'
                        : 'bg-green-50 dark:bg-green-950/30'">
                    <span class="w-2 h-2 rounded-full animate-pulse"
                        :class="isInterruption ? 'bg-red-500' : 'bg-green-500'" />
                    <span class="text-sm font-medium">{{ inProgress.title }}</span>
                    <span v-if="isInterruption" class="text-xs px-1.5 py-0.5 rounded bg-red-200 dark:bg-red-800 text-red-700 dark:text-red-300 font-medium">Interrupção</span>
                    <span v-if="inProgress.project" class="text-xs text-gray-500">· {{ inProgress.project }}</span>
                    <span class="text-sm font-mono tabular-nums"
                        :class="isInterruption ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">{{ elapsed }}</span>
                </div>
            </div>

            <div v-if="inProgress" class="flex items-center gap-2">
                <template v-if="isInterruption">
                    <form @submit.prevent="resolveInterruption">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-green-50 dark:bg-green-950/30 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 dark:hover:bg-green-950/50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            Resolvi
                        </button>
                    </form>
                </template>
                <template v-else>
                    <button @click="$emit('interrupt')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-950/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Fui Interrompido
                    </button>
                    <form @submit.prevent="pauseActivity">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                            Pausar
                        </button>
                    </form>
                    <form @submit.prevent="stopActivity">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
                            Parar
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
    inProgress: Object,
})

defineEmits(['start', 'interrupt', 'manual'])

const elapsed = ref('00:00')
let timer = null

const isInterruption = computed(() => props.inProgress?.type === 'interruption')

function getLunchOverlapSeconds(startDate, endDate) {
    const lunchStart = page.props.lunch_start
    const lunchEnd = page.props.lunch_end
    if (!lunchStart || !lunchEnd) return 0

    const lunchStartToday = new Date(endDate)
    const [lh, lm] = lunchStart.split(':')
    lunchStartToday.setHours(parseInt(lh), parseInt(lm), 0, 0)

    const lunchEndToday = new Date(endDate)
    const [leh, lem] = lunchEnd.split(':')
    lunchEndToday.setHours(parseInt(leh), parseInt(lem), 0, 0)

    const overlapStart = Math.max(startDate.getTime(), lunchStartToday.getTime())
    const overlapEnd = Math.min(endDate.getTime(), lunchEndToday.getTime())

    return Math.max(0, Math.floor((overlapEnd - overlapStart) / 1000))
}

function updateElapsed() {
    if (!props.inProgress?.started_at) {
        elapsed.value = '00:00'
        return
    }
    const start = new Date(props.inProgress.started_at).getTime()
    const now = Date.now()
    const raw = Math.floor((now - start) / 1000)
    const diff = Math.max(0, raw - getLunchOverlapSeconds(new Date(start), new Date(now)))
    const h = String(Math.floor(diff / 3600)).padStart(2, '0')
    const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0')
    const s = String(diff % 60).padStart(2, '0')
    elapsed.value = `${h}:${m}:${s}`
}

function pauseActivity(e) {
    e.preventDefault()
    if (props.inProgress?.id) {
        router.post(`/api/activities/${props.inProgress.id}/pause`, {}, {
            preserveState: false,
        })
    }
}

function stopActivity(e) {
    e.preventDefault()
    if (props.inProgress?.id) {
        router.post(`/api/activities/${props.inProgress.id}/stop`, {}, {
            preserveState: false,
        })
    }
}

function resolveInterruption(e) {
    e.preventDefault()
    if (props.inProgress?.id) {
        router.post(`/api/activities/${props.inProgress.id}/resolve-interruption`, {}, {
            preserveState: false,
        })
    }
}

onMounted(() => {
    updateElapsed()
    timer = setInterval(updateElapsed, 1000)
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>
