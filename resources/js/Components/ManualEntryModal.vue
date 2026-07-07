<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] bg-black/20 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <input ref="titleInput" v-model="form.title"
                    placeholder="O que você fez?"
                    class="w-full text-lg bg-transparent border-none outline-none placeholder-gray-400"
                    @keydown.enter="submit" @keydown.escape="$emit('close')" />
            </div>

            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-2">Categoria</label>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="cat in categories" :key="cat.id" @click="form.category_id = cat.id"
                            class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                            :class="form.category_id === cat.id
                                ? 'border-gray-900 dark:border-white bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500'">
                            {{ cat.name }}
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-2">Projeto <span class="text-gray-400">(opcional)</span></label>
                    <select v-model="form.project_id"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                        <option :value="null">—</option>
                        <option v-for="proj in projects" :key="proj.id" :value="proj.id">{{ proj.name }}</option>
                    </select>
                </div>
                <div>
                    <ModulePicker :modules="modules" :model-value="form.context_id"
                        @update:model-value="form.context_id = $event" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Início</label>
                        <input type="time" v-model="form.start_time"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Fim</label>
                        <input type="time" v-model="form.end_time"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descrição <span class="text-gray-400">(opcional)</span></label>
                    <textarea v-model="form.description" rows="2" placeholder="Adicione uma descrição..."
                        class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 p-4 border-t border-gray-100 dark:border-gray-800">
                <button @click="$emit('close')"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <button @click="submit"
                    class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                    Registrar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import ModulePicker from '@/Components/ModulePicker.vue'

const props = defineProps({
    categories: Array,
    projects: Array,
    modules: Array,
})

const emit = defineEmits(['close', 'registered'])

const titleInput = ref(null)
const today = new Date()

function toTimeString(date) {
    return String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0')
}

const end = new Date()
const start = new Date(end.getTime() - 60 * 60 * 1000)

function toDateString(date) {
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0')
}

const form = reactive({
    title: '',
    category_id: null,
    project_id: null,
    context_id: null,
    start_time: toTimeString(start),
    end_time: toTimeString(end),
    description: '',
})

function submit() {
    if (!form.title.trim() || !form.category_id) return

    const dateStr = toDateString(today)

    router.post('/api/activities/manual', {
        title: form.title.trim(),
        category_id: form.category_id,
        project_id: form.project_id || null,
        context_id: form.context_id || null,
        started_at: `${dateStr} ${form.start_time}:00`,
        ended_at: `${dateStr} ${form.end_time}:00`,
        description: form.description.trim() || null,
    }, {
        preserveState: false,
        onSuccess: () => emit('registered'),
    })
}

onMounted(() => {
    titleInput.value?.focus()
})
</script>
