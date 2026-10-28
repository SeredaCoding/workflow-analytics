<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Versões</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Histórico de atualizações do sistema</p>
            </div>

            <div v-if="versions.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                Nenhuma versão registrada ainda.
            </div>

            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-px bg-gray-200 dark:bg-gray-800" />

                <div v-for="(v, i) in versions" :key="v.version"
                    class="relative pl-12 pb-10">
                    <div class="absolute left-2.5 top-1 w-3 h-3 rounded-full border-2"
                        :class="i === 0
                            ? 'bg-gray-900 dark:bg-white border-gray-900 dark:border-white'
                            : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600'" />

                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <div>
                                <span class="text-lg font-bold text-gray-900 dark:text-white">v{{ v.version }}</span>
                                <span v-if="v.release_date" class="text-sm text-gray-500 dark:text-gray-400 ml-3">{{ v.release_date }}</span>
                            </div>
                            <span v-if="i === 0"
                                class="text-xs px-2 py-0.5 rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-medium shrink-0">
                                Atual
                            </span>
                        </div>

                        <p v-if="v.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ v.description }}</p>

                        <ul v-if="v.changes?.length" class="space-y-1.5">
                            <li v-for="(change, ci) in v.changes" :key="ci"
                                class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-500 shrink-0" />
                                {{ change }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    versions: Array,
})
</script>
