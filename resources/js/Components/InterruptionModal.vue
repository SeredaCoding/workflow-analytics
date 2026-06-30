<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] bg-black/50" @click.self="$emit('close')">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Motivo da interrupção</h3>
                <input ref="titleInput" v-model="form.title"
                    placeholder="O que aconteceu?"
                    class="w-full text-lg bg-transparent border-none outline-none placeholder-gray-400"
                    @keydown.enter="submit" @keydown.escape="$emit('close')" />
            </div>

            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Origem</label>
                    <select v-model="form.source"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                        <option value="">Não especificado</option>
                        <option value="chat">Chat</option>
                        <option value="email">E-mail</option>
                        <option value="phone">Telefone</option>
                        <option value="person">Pessoalmente</option>
                        <option value="other">Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pessoa</label>
                    <input v-model="form.person" placeholder="Quem interrompeu?"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                </div>
            </div>

            <div class="flex justify-end gap-2 p-4 border-t border-gray-100 dark:border-gray-800">
                <button @click="$emit('close')" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <button @click="submit"
                    class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Interromper
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const emit = defineEmits(['close', 'interrupted'])

const titleInput = ref(null)
const form = reactive({
    title: '',
    source: '',
    person: '',
})

function submit() {
    if (!form.title.trim()) return
    router.post('/api/activities/interrupt', {
        title: form.title.trim(),
        source: form.source || null,
        person: form.person || null,
    }, {
        preserveState: false,
        onSuccess: () => emit('interrupted'),
    })
}

onMounted(() => {
    titleInput.value?.focus()
})
</script>
