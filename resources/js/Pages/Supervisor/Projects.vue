<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Projetos do Setor</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie os projetos do seu setor</p>
                </div>
                <button @click="showCreateModal = true"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    + Novo Projeto
                </button>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <input v-model="search" type="text" placeholder="Buscar projetos..."
                    @input="onSearch"
                    class="w-full sm:w-64 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                <div class="flex items-center gap-1">
                    <button v-for="opt in visibilityOptions" :key="opt.value" @click="setVisibility(opt.value)"
                        class="text-xs px-3 py-1.5 rounded-lg border transition-colors"
                        :class="filterVisibility === opt.value
                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                            : 'border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500'">
                        {{ opt.label }}
                    </button>
                </div>
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
                        <div class="flex items-center gap-0.5 shrink-0" @click.stop>
                            <button @click="openEdit(project)"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <button @click="confirmDelete(project)"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>

                    <div v-if="project.description" class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ project.description }}</div>

                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="truncate">{{ project.sectors?.map(s => s.name).join(', ') || '—' }}</span>
                        <span class="ml-auto">{{ project.activities_count }} atividades</span>
                        <button @click.stop="manageUsers(project)"
                            class="underline hover:text-gray-700 dark:hover:text-gray-300 shrink-0">
                            {{ project.users?.length || 0 }} usuário(s)
                        </button>
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

            <Modal :show="showCreateModal" @close="showCreateModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Novo Projeto</h2>
                    <form @submit.prevent="createProject" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput id="name" v-model="createForm.name" class="mt-1 w-full" required />
                            <InputError :message="createForm.errors.name" />
                        </div>
                        <div>
                            <InputLabel value="Setores" />
                            <div class="max-h-40 overflow-y-auto space-y-1.5 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <label v-for="sector in sectors" :key="sector.id" @click.prevent="toggleSector(createForm, sector.id)"
                                    class="flex items-center gap-2 text-sm cursor-pointer"
                                    :class="createForm.sector_ids.includes(sector.id) ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">
                                    <Checkbox :checked="createForm.sector_ids.includes(sector.id)" />
                                    {{ sector.name }}
                                </label>
                            </div>
                            <InputError :message="createForm.errors.sector_ids" />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="createForm.processing">Criar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal :show="showEditModal" @close="closeEdit">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Editar Projeto</h2>
                    <form @submit.prevent="updateProject" class="space-y-4">
                        <div>
                            <InputLabel for="edit-name" value="Nome" />
                            <TextInput id="edit-name" v-model="editForm.name" class="mt-1 w-full" required />
                            <InputError :message="editForm.errors.name" />
                        </div>
                        <div>
                            <InputLabel value="Setores" />
                            <div class="max-h-40 overflow-y-auto space-y-1.5 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <label v-for="sector in sectors" :key="sector.id" @click.prevent="toggleSector(editForm, sector.id)"
                                    class="flex items-center gap-2 text-sm cursor-pointer"
                                    :class="editForm.sector_ids.includes(sector.id) ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">
                                    <Checkbox :checked="editForm.sector_ids.includes(sector.id)" />
                                    {{ sector.name }}
                                </label>
                            </div>
                            <InputError :message="editForm.errors.sector_ids" />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeEdit"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="editForm.processing">Salvar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal :show="showDeleteModal" @close="showDeleteModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Excluir Projeto</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Tem certeza que deseja excluir o projeto <strong>{{ deletingProject?.name }}</strong>?
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button @click="deleteProject"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 dark:bg-red-700 rounded-lg hover:bg-red-700">
                            Excluir
                        </button>
                    </div>
                </div>
            </Modal>

            <Modal :show="showUsersModal" @close="showUsersModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        Gerenciar Usuários &mdash; {{ managingProject?.name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Associe ou remova usuários do setor a este projeto.
                    </p>
                    <div class="max-h-60 overflow-y-auto space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                        <div v-for="user in sectorUsers" :key="user.id"
                            class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ user.name }}</span>
                            <button v-if="isAssigned(user.id)" @click="removeUser(user.id)"
                                class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                Remover
                            </button>
                            <button v-else @click="addUser(user.id)"
                                class="text-xs text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                                Adicionar
                            </button>
                        </div>
                        <div v-if="sectorUsers.length === 0" class="text-sm text-gray-400 text-center py-4">
                            Nenhum usuário no setor.
                        </div>
                    </div>
                    <div class="flex justify-end pt-4">
                        <button @click="showUsersModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Fechar
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Checkbox from '@/Components/Checkbox.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    projects: Object,
    sectorUsers: Array,
    sectors: Array,
    filters: Object,
})

function randomColor() {
    return '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0')
}

const search = ref(props.filters?.search || '')
const filterVisibility = ref(props.filters?.visibility || '')

const visibilityOptions = [
    { value: '', label: 'Todos' },
    { value: 'global', label: 'Global' },
    { value: 'sector', label: 'Setor' },
    { value: 'user', label: 'Pessoal' },
]

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

let searchTimer = null
function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(), 300)
}

function setVisibility(value) {
    filterVisibility.value = value
    applyFilters()
}

function applyFilters() {
    router.get('/supervisor/projects', {
        search: search.value || '',
        visibility: filterVisibility.value || '',
    }, { preserveState: true, preserveScroll: true })
}

function viewProject(project) {
    router.visit(`/supervisor/projects/${project.id}`)
}

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const showUsersModal = ref(false)
const editingProject = ref(null)
const deletingProject = ref(null)
const managingProject = ref(null)

const createForm = useForm({
    name: '',
    sector_ids: [],
    color: randomColor(),
    is_active: true,
})

const editForm = useForm({
    name: '',
    sector_ids: [],
    color: randomColor(),
    is_active: true,
})

function toggleSector(form, id) {
    form.sector_ids = form.sector_ids.includes(id)
        ? form.sector_ids.filter(sid => sid !== id)
        : [...form.sector_ids, id]
}

function openEdit(project) {
    editingProject.value = project
    editForm.name = project.name
    editForm.sector_ids = project.sectors?.map(s => s.id) || []
    editForm.color = project.color || randomColor()
    editForm.is_active = project.is_active
    editForm.clearErrors()
    showEditModal.value = true
}

function closeEdit() {
    showEditModal.value = false
    editingProject.value = null
}

function createProject() {
    createForm.post(route('supervisor.projects.store'), {
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
    })
}

function updateProject() {
    editForm.put(route('supervisor.projects.update', editingProject.value.id), {
        onSuccess: () => closeEdit(),
    })
}

function confirmDelete(project) {
    deletingProject.value = project
    showDeleteModal.value = true
}

function deleteProject() {
    router.delete(route('supervisor.projects.destroy', deletingProject.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            deletingProject.value = null
        },
    })
}

function manageUsers(project) {
    managingProject.value = project
    showUsersModal.value = true
}

function isAssigned(userId) {
    return managingProject.value?.users?.some(u => u.id === userId)
}

async function addUser(userId) {
    try {
        const res = await fetch(route('supervisor.projects.assign-user', managingProject.value.id), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ user_id: userId }),
        })
        if (!res.ok) {
            const data = await res.json()
            alert(data.message || 'Erro ao associar usuário')
            return
        }
        if (!managingProject.value.users) managingProject.value.users = []
        managingProject.value.users.push({ id: userId })
    } catch (e) {
        alert('Erro de conexão')
    }
}

async function removeUser(userId) {
    try {
        const res = await fetch(route('supervisor.projects.remove-user', [managingProject.value.id, userId]), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        })
        if (!res.ok) {
            alert('Erro ao remover usuário')
            return
        }
        if (managingProject.value.users) {
            managingProject.value.users = managingProject.value.users.filter(u => u.id !== userId)
        }
    } catch (e) {
        alert('Erro de conexão')
    }
}
</script>
