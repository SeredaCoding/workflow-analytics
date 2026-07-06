<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] bg-black/50" @click.self="$emit('close')">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <input ref="titleInput" v-model="form.title"
                    placeholder="O que você vai fazer?"
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
                        <button type="button" @click="showNewCategory = !showNewCategory"
                            class="px-3 py-1.5 text-sm rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                            + Nova Categoria
                        </button>
                        <transition name="fade">
                            <div v-if="showNewCategory" class="w-full">
                                <input v-model="newCategoryName" type="text" placeholder="Nome da nova categoria"
                                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400"
                                    @keyup.enter="addCategory" />
                                <div class="flex gap-2 mt-2">
                                    <button type="button" @click="addCategory" :disabled="!newCategoryName.trim()"
                                        class="px-3 py-1 text-sm rounded-lg bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        Criar
                                    </button>
                                    <button type="button" @click="showNewCategory = false"
                                        class="px-3 py-1 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-2">Setor <span class="text-gray-400">(opcional)</span></label>
                    <div class="flex flex-wrap gap-2">
                    <button v-for="proj in projects" :key="proj.id" @click="form.project_id = form.project_id === proj.id ? null : proj.id"
                        class="px-3 py-1 text-xs rounded-full border transition-colors"
                        :class="form.project_id === proj.id
                            ? 'border-gray-900 dark:border-white bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500'">
                        {{ proj.name }}
                    </button>
                        <button type="button" @click="showNewProject = !showNewProject"
                            class="px-3 py-1 text-xs rounded-full border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                            + Novo Setor
                        </button>
                        <transition name="fade">
                            <div v-if="showNewProject" class="w-full">
                                <input v-model="newProjectName" type="text" placeholder="Nome do novo setor"
                                    class="w-full text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400"
                                    @keyup.enter="addProject" />
                                <div class="flex gap-2 mt-2">
                                    <button type="button" @click="addProject" :disabled="!newProjectName.trim()"
                                        class="px-3 py-1 text-xs rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        Criar
                                    </button>
                                    <button type="button" @click="showNewProject = false"
                                        class="px-3 py-1 text-xs rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descrição <span class="text-gray-400">(opcional)</span></label>
                    <textarea v-model="form.description" rows="2" placeholder="Adicione uma descrição..."
                        class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white resize-none placeholder-gray-400"></textarea>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-2">Iniciar</label>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="startMode = 'now'"
                            class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                            :class="startMode === 'now'
                                ? 'border-gray-900 dark:border-white bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500'">
                            Agora
                        </button>
                        <button type="button" @click="startMode = 'custom'"
                            class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                            :class="startMode === 'custom'
                                ? 'border-gray-900 dark:border-white bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500'">
                            Outro horário
                        </button>
                        <input v-if="startMode === 'custom'" type="time" v-model="customTime"
                            class="text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 p-4 border-t border-gray-100 dark:border-gray-800">
                <button @click="$emit('close')" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <button @click="submit"
                    class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                    Iniciar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    categories: Array,
    projects: Array,
})

const emit = defineEmits(['close', 'started'])

const titleInput = ref(null)
const startMode = ref('now')
const customTime = ref('')
const showNewCategory = ref(false)
const showNewProject = ref(false)
const newCategoryName = ref('')
const newProjectName = ref('')

function nowString() {
    const d = new Date()
    return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0')
}

const form = reactive({
    title: '',
    category_id: props.categories?.[0]?.id || null,
    project_id: null,
    description: '',
})

function addCategory() {
    if (!newCategoryName.value.trim()) return

    router.post('/api/categories', { name: newCategoryName.value }, {
        preserveState: false,
        onSuccess: (response) => {
            const newCat = response.props.categories?.find(c => c.name === newCategoryName.value)
            if (newCat) {
                form.category_id = newCat.id
                if (props.categories) {
                    props.categories.push(newCat)
                }
            }
            showNewCategory.value = false
            newCategoryName.value = ''
        },
        onError: () => {
            console.error('Erro ao criar categoria')
        },
    })
}

function addProject() {
    if (!newProjectName.value.trim()) return

    router.post('/api/projects', { name: newProjectName.value }, {
        preserveState: false,
        onSuccess: (response) => {
            const newProj = response.props.projects?.find(p => p.name === newProjectName.value)
            if (newProj) {
                form.project_id = newProj.id
                if (props.projects) {
                    props.projects.push(newProj)
                }
            }
            showNewProject.value = false
            newProjectName.value = ''
        },
        onError: () => {
            console.error('Erro ao criar setor')
        },
    })
}

function submit() {
    if (!form.title.trim() || !form.category_id) return

    const payload = {
        title: form.title.trim(),
        category_id: form.category_id,
        project_id: form.project_id,
        description: form.description.trim() || null,
    }

    if (startMode.value === 'custom' && customTime.value) {
        const today = new Date()
        const dateStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0')
        payload.started_at = dateStr + ' ' + customTime.value + ':00'
    }

    router.post('/api/activities/start', payload, {
        preserveState: false,
        onSuccess: () => emit('started'),
    })
}

onMounted(() => {
    titleInput.value?.focus()
    customTime.value = nowString()
})

onMounted(() => {
    titleInput.value?.focus()
})
</script>
