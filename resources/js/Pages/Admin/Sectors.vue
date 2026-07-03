<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Setores</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie os setores e seus supervisores</p>
                </div>
                <button @click="openCreateModal"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    + Novo Setor
                </button>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Nome</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Supervisor</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Usuários</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="sector in sectors" :key="sector.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ sector.name }}</div>
                                <div v-if="sector.description" class="text-xs text-gray-500 mt-0.5">{{ sector.description }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ sector.supervisor?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                                {{ sector.users_count }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(sector)" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-3">Editar</button>
                                <button @click="confirmDelete(sector)" class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400">Excluir</button>
                            </td>
                        </tr>
                        <tr v-if="sectors.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Nenhum setor cadastrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ editingSector ? 'Editar Setor' : 'Novo Setor' }}
                    </h2>
                    <form @submit.prevent="saveSector" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput id="name" v-model="form.name" class="mt-1 w-full" required />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="description" value="Descrição" />
                            <textarea id="description" v-model="form.description"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm"
                                rows="2" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div>
                            <InputLabel for="supervisor_id" value="Supervisor" />
                            <select id="supervisor_id" v-model="form.supervisor_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm">
                                <option :value="null">— Sem supervisor —</option>
                                <option v-for="sup in supervisors" :key="sup.id" :value="sup.id">
                                    {{ sup.name }} ({{ sup.role === 'admin' ? 'Admin' : 'Supervisor' }})
                                </option>
                            </select>
                            <InputError :message="form.errors.supervisor_id" />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="form.processing">
                                {{ editingSector ? 'Salvar' : 'Criar' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal :show="showDeleteModal" @close="showDeleteModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Excluir Setor</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Tem certeza que deseja excluir o setor <strong>{{ deletingSector?.name }}</strong>?
                        Os usuários serão removidos deste setor.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button @click="deleteSector"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
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
    sectors: Array,
    supervisors: Array,
})

const showModal = ref(false)
const showDeleteModal = ref(false)
const editingSector = ref(null)
const deletingSector = ref(null)

const form = useForm({
    name: '',
    description: '',
    supervisor_id: null,
})

function openCreateModal() {
    editingSector.value = null
    form.reset()
    form.clearErrors()
    showModal.value = true
}

function openEditModal(sector) {
    editingSector.value = sector
    form.name = sector.name
    form.description = sector.description || ''
    form.supervisor_id = sector.supervisor_id
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingSector.value = null
}

function saveSector() {
    if (editingSector.value) {
        form.put(route('admin.sectors.update', editingSector.value.id), {
            onSuccess: () => closeModal(),
        })
    } else {
        form.post(route('admin.sectors.store'), {
            onSuccess: () => closeModal(),
        })
    }
}

function confirmDelete(sector) {
    deletingSector.value = sector
    showDeleteModal.value = true
}

function deleteSector() {
    router.delete(route('admin.sectors.destroy', deletingSector.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            deletingSector.value = null
        },
    })
}
</script>
