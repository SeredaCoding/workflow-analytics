<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-950/30 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold">Excluir "{{ activity?.title }}"</h3>
                        <p v-if="children.length === 0" class="text-sm text-gray-500 mt-1">Tem certeza? Esta ação não pode ser desfeita.</p>
                        <p v-else class="text-sm text-gray-500 mt-1">
                            Esta atividade possui <strong>{{ children.length }} interrupção{{ children.length > 1 ? 'ões' : '' }}</strong> vinculada{{ children.length > 1 ? 's' : '' }}.
                        </p>
                    </div>
                </div>

                <div v-if="children.length > 0" class="space-y-2">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 max-h-32 overflow-y-auto space-y-1">
                        <div v-for="child in children" :key="child.id" class="flex items-center justify-between text-xs">
                            <span class="truncate mr-2">{{ child.title }}</span>
                            <span class="text-gray-500 font-mono shrink-0">{{ formatDuration(child.duration_minutes) }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 rounded-lg cursor-pointer text-sm"
                            :class="mode === 'cascade' ? 'bg-red-50 dark:bg-red-950/20 ring-1 ring-red-200 dark:ring-red-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" name="mode" value="cascade" v-model="mode"
                                class="mt-0.5 text-red-600 focus:ring-red-500">
                            <div>
                                <span class="font-medium">Excluir tudo</span>
                                <p class="text-xs text-gray-500">Apaga a atividade e as {{ children.length }} interrupção{{ children.length > 1 ? 'ões' : '' }} permanentemente.</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 rounded-lg cursor-pointer text-sm"
                            :class="mode === 'convert' ? 'bg-blue-50 dark:bg-blue-950/20 ring-1 ring-blue-200 dark:ring-blue-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800'">
                            <input type="radio" name="mode" value="convert" v-model="mode"
                                class="mt-0.5 text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="font-medium">Converter interrupções</span>
                                <p class="text-xs text-gray-500">Transforma as interrupções em atividades normais e exclui apenas esta atividade.</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-100 dark:border-gray-800">
                <button @click="$emit('close')"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Cancelar
                </button>
                <button @click="confirm"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                    :class="mode === 'cascade'
                        ? 'bg-red-600 text-white hover:bg-red-700'
                        : 'bg-blue-600 text-white hover:bg-blue-700'">
                    {{ mode === 'cascade' ? 'Excluir' : 'Converter e Excluir' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    activity: Object,
})

const emit = defineEmits(['close', 'confirm'])

const children = computed(() => props.activity?.children || [])
const mode = ref(children.value.length > 0 ? 'cascade' : 'cascade')

function confirm() {
    emit('confirm', { mode: mode.value })
}

function formatDuration(minutes) {
    if (!minutes && minutes !== 0) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}
</script>
