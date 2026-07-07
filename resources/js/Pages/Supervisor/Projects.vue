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
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Setores</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Atividades</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Usuários</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="project in projects.data" :key="project.id"
                            @click="viewProject(project)"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: project.color }" />
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 dark:text-white truncate">{{ project.name }}</div>
                                        <div v-if="project.description" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate max-w-[200px]">{{ project.description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-xs">
                                {{ sectorNames(project) }}
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                {{ project.activities_count }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click.stop="manageUsers(project)"
                                    class="text-xs text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white underline">
                                    {{ project.users?.length || 0 }} usuário(s)
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right" @click.stop>
                                <button @click="openEdit(project)" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-3">Editar</button>
                                <button @click="confirmDelete(project)" class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400">Excluir</button>
                            </td>
                        </tr>
                        <tr v-if="projects.data?.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
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
                                    <input type="checkbox" :checked="createForm.sector_ids.includes(sector.id)"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
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
                                    <input type="checkbox" :checked="editForm.sector_ids.includes(sector.id)"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
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

const search = ref(props.filters?.search || '')

let searchTimer = null
function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get('/supervisor/projects', { search: search.value || '' }, { preserveState: true, preserveScroll: true })
    }, 300)
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
    color: '#6366f1',
    is_active: true,
})

const editForm = useForm({
    name: '',
    sector_ids: [],
    color: '#6366f1',
    is_active: true,
})

function sectorNames(project) {
    return project.sectors?.map(s => s.name).join(', ') || '—'
}

function toggleSector(form, id) {
    const idx = form.sector_ids.indexOf(id)
    if (idx === -1) {
        form.sector_ids.push(id)
    } else {
        form.sector_ids.splice(idx, 1)
    }
}

function openEdit(project) {
    editingProject.value = project
    editForm.name = project.name
    editForm.sector_ids = project.sectors?.map(s => s.id) || []
    editForm.color = project.color || '#6366f1'
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
