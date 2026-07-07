<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categorias</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie as categorias de atividades</p>
                </div>
                <button @click="openCreateModal"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    + Nova Categoria
                </button>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Nome</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cor</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Visibilidade</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ativo</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Atividades</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ category.name }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center">
                                    <span class="inline-block w-5 h-5 rounded-full border border-gray-200 dark:border-gray-700" :style="{ backgroundColor: category.color }" />
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="category.visibility === 'global'" class="text-xs px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400 font-medium">
                                    Global
                                </span>
                                <span v-else-if="category.visibility === 'sector'" class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 font-medium">
                                    {{ sectorLabel(category) }}
                                </span>
                                <span v-else-if="category.visibility === 'user'" class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400 font-medium">
                                    {{ userLabel(category) }}
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="category.is_active" class="text-green-600 dark:text-green-400 text-xs font-medium">Sim</span>
                                <span v-else class="text-gray-400 dark:text-gray-500 text-xs">Não</span>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                {{ category.activities_count }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(category)" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-3">Editar</button>
                                <button @click="confirmDelete(category)" class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400">Excluir</button>
                            </td>
                        </tr>
                        <tr v-if="categories.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Nenhuma categoria cadastrada.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ editingCategory ? 'Editar Categoria' : 'Nova Categoria' }}
                    </h2>
                    <form @submit.prevent="saveCategory" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput id="name" v-model="form.name" class="mt-1 w-full" required />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="color" value="Cor" />
                            <input id="color" v-model="form.color" type="color"
                                class="mt-1 block w-12 h-9 rounded-md border-gray-300 shadow-sm cursor-pointer focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700" />
                            <div class="mt-1 text-xs text-gray-400">{{ form.color }}</div>
                            <InputError :message="form.errors.color" />
                        </div>
                        <div>
                            <InputLabel for="visibility" value="Visibilidade" />
                            <select id="visibility" v-model="form.visibility"
                                class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <option value="global">Global — visível para todos</option>
                                <option value="sector">Setor específico</option>
                                <option value="user">Usuário(s) específico(s)</option>
                            </select>
                            <InputError :message="form.errors.visibility" />
                        </div>

                        <div v-if="form.visibility === 'sector'" class="space-y-2">
                            <InputLabel value="Setores" />
                            <div class="max-h-40 overflow-y-auto space-y-1.5 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <label v-for="sector in sectors" :key="sector.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer"
                                    :class="form.sector_ids.includes(sector.id) ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">
                                    <input type="checkbox" :value="sector.id" v-model="form.sector_ids"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
                                    {{ sector.name }}
                                </label>
                                <div v-if="!sectors.length" class="text-sm text-gray-400">Nenhum setor cadastrado.</div>
                            </div>
                            <InputError :message="form.errors.sector_ids" />
                        </div>

                        <div v-if="form.visibility === 'user'" class="space-y-2">
                            <InputLabel value="Usuários" />
                            <div class="max-h-40 overflow-y-auto space-y-1.5 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <label v-for="user in users" :key="user.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer"
                                    :class="form.user_ids.includes(user.id) ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">
                                    <input type="checkbox" :value="user.id" v-model="form.user_ids"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
                                    {{ user.name }}
                                </label>
                                <div v-if="!users.length" class="text-sm text-gray-400">Nenhum usuário cadastrado.</div>
                            </div>
                            <InputError :message="form.errors.user_ids" />
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input v-model="form.is_active" type="checkbox"
                                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
                                Ativo
                            </label>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="form.processing">
                                {{ editingCategory ? 'Salvar' : 'Criar' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal :show="showDeleteModal" @close="showDeleteModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Excluir Categoria</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Tem certeza que deseja excluir a categoria <strong>{{ deletingCategory?.name }}</strong>?
                        As atividades relacionadas terão a categoria removida.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button @click="deleteCategory"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 dark:bg-red-700 rounded-lg hover:bg-red-700">
                            Excluir
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    categories: Array,
    sectors: Array,
    users: Array,
})

const showModal = ref(false)
const showDeleteModal = ref(false)
const editingCategory = ref(null)
const deletingCategory = ref(null)

const form = useForm({
    name: '',
    color: '#6366f1',
    is_active: true,
    visibility: 'global',
    sector_ids: [],
    user_ids: [],
})

function openCreateModal() {
    editingCategory.value = null
    form.reset()
    form.color = '#6366f1'
    form.is_active = true
    form.visibility = 'global'
    form.sector_ids = []
    form.user_ids = []
    form.clearErrors()
    showModal.value = true
}

function openEditModal(category) {
    editingCategory.value = category
    form.name = category.name
    form.color = category.color || '#6366f1'
    form.is_active = category.is_active
    form.visibility = category.visibility || 'global'
    form.sector_ids = category.sectors?.map(s => s.id) || []
    form.user_ids = category.users?.map(u => u.id) || []
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingCategory.value = null
}

function saveCategory() {
    if (editingCategory.value) {
        form.put(route('admin.categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal(),
        })
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => closeModal(),
        })
    }
}

function confirmDelete(category) {
    deletingCategory.value = category
    showDeleteModal.value = true
}

function deleteCategory() {
    router.delete(route('admin.categories.destroy', deletingCategory.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            deletingCategory.value = null
        },
    })
}

function sectorLabel(category) {
    const names = category.sectors?.map(s => s.name) || []
    if (names.length === 0) return 'Setor: —'
    if (names.length === 1) return 'Setor: ' + names[0]
    return 'Setores: ' + names.join(', ')
}

function userLabel(category) {
    const count = category.users?.length || 0
    if (count === 0) return 'Usuários: —'
    if (count === 1) return 'Usuário: ' + category.users[0].name
    return 'Usuários: ' + count
}
</script>
