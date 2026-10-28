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
                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="project in projects.data" :key="project.id"
                    @click="viewProject(project)"
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 space-y-3 cursor-pointer hover:border-gray-300 dark:hover:border-gray-700 transition-colors">

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <span class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: project.color }" />
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ project.name }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded-full shrink-0 font-medium"
                                :class="visibilityBadgeClass(project.visibility)">
                                {{ visibilityLabel(project.visibility) }}
                            </span>
                        </div>
                        <button @click.stop="openEdit(project)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors shrink-0"
                            title="Editar projeto">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        </button>
                    </div>

                    <div v-if="project.description" class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ project.description }}</div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ project.activities_count ?? 0 }} atividades
                    </div>
                </div>

                <div v-if="projects.data?.length === 0"
                    class="col-span-full text-center py-12 text-sm text-gray-500 dark:text-gray-400">
                    Nenhum projeto encontrado.
                </div>
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

            <Modal :show="showEditModal" @close="closeEdit" max-width="md">
                <div class="p-6 space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Editar Projeto</h2>

                    <form @submit.prevent="save" class="space-y-4">
                        <div>
                            <InputLabel for="edit-name" value="Nome" />
                            <TextInput id="edit-name" v-model="form.name" class="mt-1 w-full" required />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="edit-desc" value="Descrição" />
                            <textarea id="edit-desc" v-model="form.description" rows="2"
                                class="mt-1 w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400 resize-y" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div>
                            <InputLabel for="edit-color" value="Cor" />
                            <input id="edit-color" v-model="form.color" type="color"
                                class="mt-1 block w-12 h-9 rounded-md border-gray-300 shadow-sm cursor-pointer focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-800">
                            <button type="button" @click="closeEdit"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="form.processing">
                                Salvar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    projects: Object,
    filters: Object,
})

function randomColor() {
    return '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0')
}

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

function visibilityBadgeClass(v) {
    const base = 'inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium'
    const map = {
        global: 'bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-300',
        sector: 'bg-green-100 text-green-700 dark:bg-green-950/30 dark:text-green-300',
        user: 'bg-purple-100 text-purple-700 dark:bg-purple-950/30 dark:text-purple-300',
    }
    return `${base} ${map[v] || 'bg-gray-100 text-gray-500'}`
}

function visibilityLabel(v) {
    return { global: 'Global', sector: 'Setor', user: 'Pessoal' }[v] || v
}

const showEditModal = ref(false)
const editingProject = ref(null)

const form = useForm({
    name: '',
    description: '',
    color: randomColor(),
})

function openEdit(project) {
    editingProject.value = project
    form.name = project.name
    form.description = project.description || ''
    form.color = project.color || randomColor()
    form.clearErrors()
    showEditModal.value = true
}

function closeEdit() {
    showEditModal.value = false
    editingProject.value = null
}

function save() {
    form.put(route('projects.update', editingProject.value.id), {
        onSuccess: () => closeEdit(),
    })
}
</script>
