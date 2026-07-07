<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Projetos</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie os projetos de atividades</p>
                </div>
                <button @click="openCreate"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    + Novo Projeto
                </button>
            </div>

            <div class="flex items-center gap-2 max-w-sm">
                <input v-model="search" type="text" placeholder="Buscar projetos..."
                    @input="onSearch"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div v-for="project in projects.data" :key="project.id"
                    @click="openManage(project)"
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:border-gray-300 dark:hover:border-gray-700 transition-colors cursor-pointer">
                    <div class="flex items-start gap-3">
                        <span class="inline-block w-3.5 h-3.5 rounded-full mt-1 shrink-0" :style="{ backgroundColor: project.color }" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ project.name }}</div>
                                    <p v-if="project.description" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">{{ project.description }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2">
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    {{ project.activities_count }} atividades
                                </span>
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                                    {{ project.users?.length || 0 }} usuários
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    :class="visibilityClass(project)">
                                    {{ visibilityLabel(project) }}
                                </span>
                                <span v-if="!project.is_active" class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium">
                                    Inativo
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="projects.data?.length === 0" class="py-12 text-center text-sm text-gray-400">
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

            <Modal :show="showManageModal" @close="closeManage" max-width="2xl">
                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ isCreating ? 'Novo Projeto' : managingProject?.name }}
                        </h2>
                        <span v-if="!isCreating" class="text-xs text-gray-400">#{{ managingProject?.id }}</span>
                    </div>

                    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-800">
                        <button v-for="tab in tabs" :key="tab.key"
                            @click="activeTab = tab.key"
                            type="button"
                            :disabled="tab.disabled?.()"
                            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                            :class="[
                                activeTab === tab.key
                                    ? 'border-gray-900 dark:border-white text-gray-900 dark:text-white'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300',
                                tab.disabled?.() ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                            ]">
                            {{ tab.label }}
                        </button>
                    </div>

                    <form @submit.prevent="saveProject">
                        <div v-if="activeTab === 'info'" class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <InputLabel for="m-name" value="Nome" />
                                    <TextInput id="m-name" v-model="form.name" class="mt-1 w-full" required />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="md:col-span-2">
                                    <InputLabel for="m-desc" value="Descrição" />
                                    <textarea id="m-desc" v-model="form.description" rows="2"
                                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500 placeholder-gray-400" />
                                    <InputError :message="form.errors.description" />
                                </div>
                                <div>
                                    <InputLabel for="m-color" value="Cor" />
                                    <input id="m-color" v-model="form.color" type="color"
                                        class="mt-1 block w-12 h-9 rounded-md border-gray-300 shadow-sm cursor-pointer focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700" />
                                </div>
                                <div class="flex items-end pb-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input v-model="form.is_active" type="checkbox"
                                            class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
                                        Ativo
                                    </label>
                                </div>
                                <div class="md:col-span-2">
                                    <InputLabel for="m-visibility" value="Visibilidade" />
                                    <select id="m-visibility" v-model="form.visibility"
                                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                        <option value="global">Global — visível para todos</option>
                                        <option value="sector">Setor específico</option>
                                        <option value="user">Usuário(s) específico(s)</option>
                                    </select>
                                    <InputError :message="form.errors.visibility" />
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'sectors'" class="space-y-3">
                            <InputLabel value="Setores" />
                            <div class="relative">
                                <input v-model="sectorSearch" type="text" placeholder="Filtrar setores..."
                                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pl-9 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <div class="max-h-56 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <label v-for="sector in filteredSectors" :key="sector.id" @click.prevent="toggleSector(sector.id)"
                                    class="flex items-center gap-2 text-sm cursor-pointer py-1"
                                    :class="form.sector_ids.includes(sector.id) ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">
                                    <input type="checkbox" :checked="form.sector_ids.includes(sector.id)"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
                                    {{ sector.name }}
                                </label>
                                <div v-if="filteredSectors.length === 0" class="text-sm text-gray-400 text-center py-2">Nenhum setor encontrado.</div>
                            </div>
                            <div v-if="form.sector_ids.length" class="text-xs text-gray-400">{{ form.sector_ids.length }} selecionado(s)</div>
                            <InputError :message="form.errors.sector_ids" />
                        </div>

                        <div v-if="activeTab === 'users'" class="space-y-3">
                            <InputLabel value="Usuários" />
                            <div class="relative">
                                <input v-model="userSearch" type="text" placeholder="Filtrar usuários..."
                                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pl-9 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <div class="max-h-56 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <label v-for="user in filteredUsers" :key="user.id" @click.prevent="toggleUser(user.id)"
                                    class="flex items-center gap-2 text-sm cursor-pointer py-1"
                                    :class="form.user_ids.includes(user.id) ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">
                                    <input type="checkbox" :checked="form.user_ids.includes(user.id)"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
                                    <div class="min-w-0">
                                        <div class="truncate">{{ user.name }}</div>
                                        <div v-if="user.email" class="text-xs text-gray-400 truncate">{{ user.email }}</div>
                                    </div>
                                </label>
                                <div v-if="filteredUsers.length === 0" class="text-sm text-gray-400 text-center py-2">Nenhum usuário encontrado.</div>
                            </div>
                            <div v-if="form.user_ids.length" class="text-xs text-gray-400">{{ form.user_ids.length }} selecionado(s)</div>
                            <InputError :message="form.errors.user_ids" />
                        </div>

                        <div v-if="activeTab === 'delete' && !isCreating" class="space-y-4">
                            <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/30 rounded-lg p-5">
                                <p class="text-sm text-red-700 dark:text-red-400 mb-4">
                                    Tem certeza que deseja excluir <strong>{{ managingProject?.name }}</strong>?
                                    As atividades relacionadas terão o projeto removido.
                                </p>
                                <button type="button" @click="deleteProject"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 dark:bg-red-700 rounded-lg hover:bg-red-700">
                                    Confirmar Exclusão
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 mt-5 border-t border-gray-200 dark:border-gray-800">
                            <div></div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="closeManage"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                    Cancelar
                                </button>
                                <PrimaryButton :disabled="form.processing">
                                    {{ isCreating ? 'Criar' : 'Salvar' }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    projects: Object,
    sectors: Array,
    users: Array,
    filters: Object,
})

const search = ref(props.filters?.search || '')

let searchTimer = null
function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get('/admin/projects', { search: search.value || '' }, { preserveState: true, preserveScroll: true })
    }, 300)
}

const showManageModal = ref(false)
const managingProject = ref(null)
const isCreating = computed(() => !managingProject.value)
const activeTab = ref('info')

const sectorSearch = ref('')
const userSearch = ref('')

const filteredSectors = computed(() => {
    const q = sectorSearch.value.toLowerCase()
    if (!q) return props.sectors
    return props.sectors.filter(s => s.name.toLowerCase().includes(q))
})

const filteredUsers = computed(() => {
    const q = userSearch.value.toLowerCase()
    if (!q) return props.users
    return props.users.filter(u => u.name.toLowerCase().includes(q) || (u.email && u.email.toLowerCase().includes(q)))
})

const tabs = computed(() => [
    { key: 'info', label: 'Informações' },
    { key: 'sectors', label: 'Setores', disabled: () => form.visibility !== 'sector' },
    { key: 'users', label: 'Usuários', disabled: () => form.visibility !== 'user' },
    { key: 'delete', label: 'Excluir', disabled: () => isCreating.value },
])

const form = useForm({
    name: '',
    description: '',
    color: '#6366f1',
    is_active: true,
    visibility: 'global',
    sector_ids: [],
    user_ids: [],
})

function openCreate() {
    managingProject.value = null
    activeTab.value = 'info'
    sectorSearch.value = ''
    userSearch.value = ''
    form.reset()
    form.color = '#6366f1'
    form.is_active = true
    form.visibility = 'global'
    form.sector_ids = []
    form.user_ids = []
    form.clearErrors()
    showManageModal.value = true
}

function openManage(project) {
    managingProject.value = project
    activeTab.value = 'info'
    sectorSearch.value = ''
    userSearch.value = ''
    form.name = project.name
    form.description = project.description || ''
    form.color = project.color || '#6366f1'
    form.is_active = project.is_active
    form.visibility = project.visibility || 'global'
    form.sector_ids = project.sectors?.map(s => s.id) || []
    form.user_ids = project.users?.map(u => u.id) || []
    form.clearErrors()
    showManageModal.value = true
}

function closeManage() {
    showManageModal.value = false
    managingProject.value = null
}

function toggleSector(id) {
    const idx = form.sector_ids.indexOf(id)
    if (idx === -1) form.sector_ids.push(id)
    else form.sector_ids.splice(idx, 1)
}

function toggleUser(id) {
    const idx = form.user_ids.indexOf(id)
    if (idx === -1) form.user_ids.push(id)
    else form.user_ids.splice(idx, 1)
}

function saveProject() {
    if (isCreating.value) {
        form.post(route('admin.projects.store'), {
            onSuccess: () => closeManage(),
        })
    } else {
        form.put(route('admin.projects.update', managingProject.value.id), {
            onSuccess: () => closeManage(),
        })
    }
}

function deleteProject() {
    router.delete(route('admin.projects.destroy', managingProject.value.id), {
        onSuccess: () => closeManage(),
    })
}

function visibilityClass(project) {
    if (project.visibility === 'global') return 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400'
    if (project.visibility === 'sector') return 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400'
    return 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400'
}

function visibilityLabel(project) {
    if (project.visibility === 'global') return 'Global'
    if (project.visibility === 'sector') {
        const names = project.sectors?.map(s => s.name) || []
        if (names.length === 0) return 'Setor'
        return names.join(', ')
    }
    return project.users?.length + ' usuário(s)'
}
</script>
