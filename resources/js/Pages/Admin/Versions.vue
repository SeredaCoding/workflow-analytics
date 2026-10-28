<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Versões</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie o histórico de versões do sistema</p>
                </div>
                <button @click="openCreateModal"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    + Nova Versão
                </button>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Versão</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Data</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Mudanças</th>
                            <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ativo</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="v in versions.data" :key="v.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">v{{ v.version }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ formatDate(v.release_date) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ v.changes?.length || 0 }} mudanças</td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    :class="v.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'">
                                    {{ v.is_active ? 'Sim' : 'Não' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditModal(v)" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-3">Editar</button>
                                <button @click="confirmDelete(v)" class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400">Excluir</button>
                            </td>
                        </tr>
                        <tr v-if="versions.data?.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Nenhuma versão cadastrada.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ editingVersion ? 'Editar Versão' : 'Nova Versão' }}
                    </h2>
                    <form @submit.prevent="saveVersion" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="version" value="Versão" />
                                <TextInput id="version" v-model="form.version" class="mt-1 w-full" placeholder="0.1.2" required />
                                <InputError :message="form.errors.version" />
                            </div>
                            <div>
                                <InputLabel for="release_date" value="Data de Lançamento" />
                                <input id="release_date" v-model="form.release_date" type="date"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm" />
                                <InputError :message="form.errors.release_date" />
                            </div>
                        </div>
                        <div>
                            <InputLabel for="description" value="Descrição" />
                            <textarea id="description" v-model="form.description"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm"
                                rows="2" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div>
                            <InputLabel for="changes" value="Mudanças (uma por linha)" />
                            <textarea id="changes" v-model="changesText"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 text-sm font-mono"
                                rows="6" placeholder="Correção no login&#10;Nova tela de relatórios&#10;Atualização de dependências" />
                            <InputError :message="form.errors.changes" />
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox id="is_active" v-model="form.is_active" />
                            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Ativo (aparece na timeline)</label>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <PrimaryButton :disabled="form.processing">
                                {{ editingVersion ? 'Salvar' : 'Criar' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal :show="showDeleteModal" @close="showDeleteModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Excluir Versão</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Tem certeza que deseja excluir a versão <strong>v{{ deletingVersion?.version }}</strong>?
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button @click="deleteVersion"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 dark:bg-red-700 rounded-lg hover:bg-red-700">
                            Excluir
                        </button>
                    </div>
                </div>
            </Modal>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    Total: {{ versions.total }} versões
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Checkbox from '@/Components/Checkbox.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    versions: Object,
})

const showModal = ref(false)
const showDeleteModal = ref(false)
const editingVersion = ref(null)
const deletingVersion = ref(null)

const changesText = ref('')

const form = useForm({
    version: '',
    release_date: '',
    description: '',
    changes: [],
    is_active: true,
})

watch(changesText, (val) => {
    form.changes = val.split('\n').filter(l => l.trim())
})

function openCreateModal() {
    editingVersion.value = null
    form.reset()
    form.is_active = true
    changesText.value = ''
    showModal.value = true
}

function openEditModal(v) {
    editingVersion.value = v
    form.version = v.version
    form.release_date = v.release_date ? v.release_date.slice(0, 10) : ''
    form.description = v.description || ''
    form.changes = v.changes || []
    form.is_active = v.is_active
    changesText.value = (v.changes || []).join('\n')
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingVersion.value = null
}

function saveVersion() {
    if (editingVersion.value) {
        form.put(route('admin.versions.update', editingVersion.value.id), {
            onSuccess: () => closeModal(),
        })
    } else {
        form.post(route('admin.versions.store'), {
            onSuccess: () => closeModal(),
        })
    }
}

function confirmDelete(v) {
    deletingVersion.value = v
    showDeleteModal.value = true
}

function deleteVersion() {
    router.delete(route('admin.versions.destroy', deletingVersion.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            deletingVersion.value = null
        },
    })
}

function formatDate(date) {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('pt-BR')
}
</script>
