<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] bg-black/50" @click.self="$emit('close')">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <input ref="titleInput" v-model="form.title"
                    placeholder="Título da atividade"
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
                    <div class="flex flex-wrap gap-2">
                        <button v-for="proj in projects" :key="proj.id" @click="form.project_id = form.project_id === proj.id ? null : proj.id"
                            class="px-3 py-1 text-xs rounded-full border transition-colors"
                            :class="form.project_id === proj.id
                                ? 'border-gray-900 dark:border-white bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500'">
                            {{ proj.name }}
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descrição <span class="text-gray-400">(opcional)</span></label>
                    <textarea v-model="form.description" rows="2" placeholder="Adicione uma descrição..."
                        class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white resize-none placeholder-gray-400"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Início</label>
                        <input v-model="form.started_at" type="datetime-local"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                    </div>
                    <div v-if="!isInProgress">
                        <label class="block text-xs text-gray-500 mb-1">Fim</label>
                        <input v-model="form.ended_at" type="datetime-local"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Prioridade</label>
                        <select v-model="form.priority"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            <option value="">Normal</option>
                            <option value="low">Baixa</option>
                            <option value="medium">Média</option>
                            <option value="high">Alta</option>
                            <option value="critical">Crítica</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dificuldade</label>
                        <select v-model="form.energy_level"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                            <option :value="null">—</option>
                            <option :value="1">1 — Muito fácil</option>
                            <option :value="2">2 — Fácil</option>
                            <option :value="3">3 — Normal</option>
                            <option :value="4">4 — Difícil</option>
                            <option :value="5">5 — Muito difícil</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 p-4 border-t border-gray-100 dark:border-gray-800">
                <button @click="$emit('close')"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <button @click="submit"
                    class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                    Salvar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()
const categories = page.props.categories
const projects = page.props.projects

const props = defineProps({
    activity: Object,
})

const emit = defineEmits(['close', 'saved'])

const titleInput = ref(null)

function toDatetimeLocal(date) {
    if (!date) return ''
    const d = new Date(date)
    const pad = (n) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

const isInProgress = computed(() => props.activity?.status === 'in_progress')

const showNewCategory = ref(false)
const showNewProject = ref(false)
const newCategoryName = ref('')
const newProjectName = ref('')

const form = reactive({
    title: props.activity?.title || '',
    category_id: props.activity?.category?.id || props.activity?.category_id || null,
    project_id: props.activity?.project?.id || props.activity?.project_id || null,
    description: props.activity?.description || '',
    priority: props.activity?.priority || '',
    energy_level: props.activity?.energy_level || null,
    started_at: toDatetimeLocal(props.activity?.started_at),
    ended_at: toDatetimeLocal(props.activity?.ended_at),
})

function submit() {
    if (!form.title.trim() || !form.category_id) return

    const data = {
        title: form.title.trim(),
        category_id: form.category_id,
        project_id: form.project_id || null,
        description: form.description.trim() || null,
        priority: form.priority || null,
        energy_level: form.energy_level || null,
        started_at: form.started_at || null,
    }
    if (form.ended_at && !isInProgress.value) {
        data.ended_at = form.ended_at
    }

    router.put(`/activities/${props.activity.id}`, data, {
        preserveState: false,
        onSuccess: () => emit('saved'),
    })
}

onMounted(() => {
    titleInput.value?.focus()
})
</script>
