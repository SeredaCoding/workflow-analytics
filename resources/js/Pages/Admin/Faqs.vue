<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">FAQ</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie as perguntas frequentes do sistema</p>
                </div>
                <button @click="openCreate"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                    + Nova Pergunta
                </button>
            </div>

            <div class="flex items-center gap-2 max-w-sm">
                <input v-model="search" type="text" placeholder="Buscar perguntas..."
                    @input="onSearch"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
            </div>

            <div class="space-y-2">
                <div v-for="faq in faqs.data" :key="faq.id"
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 font-mono">#{{ faq.sort_order }}</span>
                                <span v-if="!faq.is_active" class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">Inativo</span>
                            </div>
                            <div class="font-medium text-gray-900 dark:text-white mt-1">{{ faq.question }}</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ faq.answer }}</p>
                        </div>
                        <button @click="openEdit(faq)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors shrink-0">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        </button>
                    </div>
                </div>
                <div v-if="faqs.data?.length === 0" class="py-12 text-center text-sm text-gray-400">
                    Nenhuma pergunta encontrada.
                </div>
            </div>

            <div v-if="faqs.total > faqs.per_page" class="flex justify-center gap-2">
                <Link v-for="link in faqs.links" :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                    :class="link.active
                        ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                        : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-gray-400 dark:hover:border-gray-500'" />
            </div>

            <Modal :show="showModal" @close="closeModal" max-width="lg">
                <div class="p-6 space-y-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ isCreating ? 'Nova Pergunta' : 'Editar Pergunta' }}
                    </h2>

                    <form @submit.prevent="save" class="space-y-4">
                        <div>
                            <InputLabel for="question" value="Pergunta" />
                            <TextInput id="question" v-model="form.question" class="mt-1 w-full" required />
                            <InputError :message="form.errors.question" />
                        </div>
                        <div>
                            <InputLabel for="answer" value="Resposta" />
                            <textarea id="answer" v-model="form.answer" rows="5"
                                class="mt-1 w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400 resize-y" />
                            <InputError :message="form.errors.answer" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="order" value="Ordem" />
                                <TextInput id="order" v-model="form.sort_order" type="number" class="mt-1 w-full" />
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input v-model="form.is_active" type="checkbox"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700" />
                                    Ativo
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-800">
                            <button v-if="!isCreating" type="button" @click="confirmDelete"
                                class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium">
                                Excluir pergunta
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
                        <p class="text-sm text-red-700 dark:text-red-400 mb-3">Excluir esta pergunta?</p>
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
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    faqs: Object,
    filters: Object,
})

const search = ref(props.filters?.search || '')

let searchTimer = null
function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
        router.get('/admin/faqs', { search: search.value || '' }, { preserveState: true, preserveScroll: true })
    }, 300)
}

const showModal = ref(false)
const editingFaq = ref(null)
const isCreating = ref(false)
const showDeleteConfirm = ref(false)

const form = useForm({
    question: '',
    answer: '',
    sort_order: 0,
    is_active: true,
})

function openCreate() {
    isCreating.value = true
    editingFaq.value = null
    showDeleteConfirm.value = false
    form.reset()
    form.sort_order = 0
    form.is_active = true
    form.clearErrors()
    showModal.value = true
}

function openEdit(faq) {
    isCreating.value = false
    editingFaq.value = faq
    showDeleteConfirm.value = false
    form.question = faq.question
    form.answer = faq.answer
    form.sort_order = faq.sort_order || 0
    form.is_active = faq.is_active
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingFaq.value = null
}

function save() {
    if (isCreating.value) {
        form.post(route('admin.faqs.store'), { onSuccess: () => closeModal() })
    } else {
        form.put(route('admin.faqs.update', editingFaq.value.id), { onSuccess: () => closeModal() })
    }
}

function confirmDelete() { showDeleteConfirm.value = true }

function destroy() {
    router.delete(route('admin.faqs.destroy', editingFaq.value.id), { onSuccess: () => closeModal() })
}
</script>
