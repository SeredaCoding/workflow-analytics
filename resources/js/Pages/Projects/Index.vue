<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Meus Projetos</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Projetos que você tem acesso</p>
            </div>

            <div class="flex items-center gap-2 max-w-sm">
                <input v-model="search" type="text" placeholder="Buscar projetos..."
                    @input="onSearch"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Nome</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Visibilidade</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Atividades</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="project in projects.data" :key="project.id"
                            @click="viewProject(project)"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/30 cursor-pointer">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: project.color }" />
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ project.name }}</div>
                                        <div v-if="project.description" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate max-w-xs">{{ project.description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    :class="visibilityClass(project.visibility)">
                                    {{ visibilityLabel(project.visibility) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">
                                {{ project.activities_count ?? '-' }}
                            </td>
                        </tr>
                        <tr v-if="projects.data?.length === 0">
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Nenhum projeto encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="projects.total > projects.per_page" class="flex justify-center gap-2">
                <Link v-for="link in projects.links" :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                    :class="link.active
                        ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                        : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-gray-400 dark:hover:border-gray-500'" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    projects: Object,
    filters: Object,
})

const search = ref(props.filters?.search || '')

let searchTimer = null
function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get('/projects', { search: search.value || '' }, { preserveState: true, preserveScroll: true })
    }, 300)
}

function viewProject(project) {
    router.visit(`/projects/${project.id}`)
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
