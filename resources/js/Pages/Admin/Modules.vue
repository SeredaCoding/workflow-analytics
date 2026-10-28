<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Módulos</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie os módulos do sistema (camadas / contextos / áreas)</p>
                </div>
                <button @click="openCreate"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    + Novo Módulo
                </button>
            </div>

            <div class="flex items-center gap-2 max-w-sm">
                <input v-model="search" type="text" placeholder="Buscar módulos..."
                    @input="onSearch"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div v-for="mod in modules.data" :key="mod.id"
                    @click="openEdit(mod)"
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:border-gray-300 dark:hover:border-gray-700 transition-colors cursor-pointer">
                    <div class="flex items-start gap-3">
                        <span class="inline-block w-3 h-3 rounded-full mt-0.5 shrink-0" :style="{ backgroundColor: mod.color }" />
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 dark:text-white">{{ mod.name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">/{{ mod.slug }}</div>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span v-if="!mod.is_active" class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium">Inativo</span>
                                <span class="text-xs text-gray-500">Ordem: {{ mod.sort_order }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="modules.data?.length === 0" class="col-span-full py-12 text-center text-sm text-gray-400">
                    Nenhum módulo encontrado.
                </div>
            </div>

            <div v-if="modules.total > modules.per_page" class="flex justify-center gap-2">
                <Link v-for="link in modules.links" :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                    :class="link.active
                        ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                        : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-gray-400 dark:hover:border-gray-500'" />
            </div>

            <Modal :show="showModal" @close="closeModal" max-width="md">
                <div class="p-6 space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ isCreating ? 'Novo Módulo' : editingModule?.name }}
                    </h2>

                    <form @submit.prevent="save" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput id="name" v-model="form.name" class="mt-1 w-full" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="color" value="Cor" />
                                <input id="color" v-model="form.color" type="color"
                                    class="mt-1 block w-12 h-9 rounded-md border-gray-300 shadow-sm cursor-pointer focus:border-gray-500 focus:ring-gray-500 dark:border-gray-700" />
                            </div>
                            <div>
                                <InputLabel for="sort" value="Ordem" />
                                <TextInput id="sort" v-model="form.sort_order" type="number" class="mt-1 w-full" />
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <Checkbox v-model="form.is_active" />
                            Ativo
                        </label>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-800">
                            <button v-if="!isCreating" type="button" @click="confirmDeletion"
                                class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium">
                                Excluir módulo
                            </button>
                            <div v-else></div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="closeModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                    Cancelar
                                </button>
                                <PrimaryButton :disabled="form.processing">
                                    {{ isCreating ? 'Criar' : 'Salvar' }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>

                    <div v-if="showDeleteConfirm" class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/30 rounded-lg p-4">
                        <p class="text-sm text-red-700 dark:text-red-400 mb-3">
                            Tem certeza que deseja excluir <strong>{{ editingModule?.name }}</strong>?
                        </p>
                        <div class="flex justify-end gap-3">
                            <button @click="showDeleteConfirm = false"
                                class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancelar
                            </button>
                            <button @click="destroy"
                                class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 dark:bg-red-700 rounded-lg hover:bg-red-700">
                                Confirmar Exclusão
                            </button>
                        </div>
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
    modules: Object,
    filters: Object,
})

const search = ref(props.filters?.search || '')

let searchTimer = null
function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get('/admin/modules', { search: search.value || '' }, { preserveState: true, preserveScroll: true })
    }, 300)
}

const showModal = ref(false)
const editingModule = ref(null)
const isCreating = ref(false)
const showDeleteConfirm = ref(false)

const form = useForm({
    name: '',
    color: '#6366f1',
    is_active: true,
    sort_order: 0,
})

function openCreate() {
    isCreating.value = true
    editingModule.value = null
    showDeleteConfirm.value = false
    form.reset()
    form.color = '#6366f1'
    form.is_active = true
    form.sort_order = 0
    form.clearErrors()
    showModal.value = true
}

function openEdit(mod) {
    isCreating.value = false
    editingModule.value = mod
    showDeleteConfirm.value = false
    form.name = mod.name
    form.color = mod.color || '#6366f1'
    form.is_active = mod.is_active
    form.sort_order = mod.sort_order || 0
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingModule.value = null
}

function save() {
    if (isCreating.value) {
        form.post(route('admin.modules.store'), {
            onSuccess: () => closeModal(),
        })
    } else {
        form.put(route('admin.modules.update', editingModule.value.id), {
            onSuccess: () => closeModal(),
        })
    }
}

function confirmDeletion() {
    showDeleteConfirm.value = true
}

function destroy() {
    router.delete(route('admin.modules.destroy', editingModule.value.id), {
        onSuccess: () => closeModal(),
    })
}
</script>
