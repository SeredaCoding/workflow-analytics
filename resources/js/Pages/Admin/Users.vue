<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Usuários</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie papéis e setores dos usuários</p>
            </div>

            <div class="flex gap-3">
                <select v-model="filters.role_id"
                    @change="applyFilters"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                    <option value="">Todos os papéis</option>
                    <option value="1">Usuário</option>
                    <option value="2">Supervisor</option>
                    <option value="3">Admin</option>
                </select>
                <select v-model="filters.sector_id"
                    @change="applyFilters"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                    <option value="">Todos os setores</option>
                    <option v-for="s in sectors" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Nome</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">E-mail</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Papel</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Setor</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <span :class="roleBadgeClass(user.role_id)">{{ roleLabel(user.role_id) }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ user.sector?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(user)" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Editar</button>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Editar Usuário: {{ editingUser?.name }}
                    </h2>
                    <form @submit.prevent="saveUser" class="space-y-4">
                        <div>
                            <InputLabel value="Papel" />
                            <select v-model="form.role_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                                <option :value="1">Usuário</option>
                                <option :value="2">Supervisor</option>
                                <option :value="3">Admin</option>
                            </select>
                            <InputError :message="form.errors.role_id" />
                        </div>
                        <div>
                            <InputLabel value="Setor" />
                            <select v-model="form.sector_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                                <option :value="null">— Sem setor —</option>
                                <option v-for="s in sectors" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <InputError :message="form.errors.sector_id" />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="form.processing">Salvar</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    users: Array,
    sectors: Array,
    roles: Array,
    filters: Object,
})

const showModal = ref(false)
const editingUser = ref(null)

const filters = reactive({
    role_id: props.filters?.role_id || '',
    sector_id: props.filters?.sector_id || '',
})

const form = useForm({
    role_id: 1,
    sector_id: null,
})

function roleLabel(roleId) {
    return { 1: 'Usuário', 2: 'Supervisor', 3: 'Admin' }[roleId] || 'Usuário'
}

function roleBadgeClass(roleId) {
    const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
    const colors = {
        3: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        2: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        1: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    }
    return `${base} ${colors[roleId] || colors[1]}`
}

function applyFilters() {
    router.get(route('admin.users.index'), {
        role_id: filters.role_id || undefined,
        sector_id: filters.sector_id || undefined,
    }, { preserveState: true })
}

function openEditModal(user) {
    editingUser.value = user
    form.role_id = user.role_id
    form.sector_id = user.sector_id
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingUser.value = null
}

function saveUser() {
    form.put(route('admin.users.update', editingUser.value.id), {
        onSuccess: () => closeModal(),
    })
}
</script>