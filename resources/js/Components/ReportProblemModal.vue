<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[10vh] bg-black/20 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reportar Problema</h3>
                <button @click="$emit('close')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-800 px-4">
                <button v-for="(tab, i) in tabs" :key="i" @click="activeStep = i" type="button"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px"
                    :class="activeStep === i
                        ? 'border-gray-900 dark:border-white text-gray-900 dark:text-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                    {{ tab }}
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div v-if="activeStep === 0" class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">O que aconteceu? <span class="text-red-500">*</span></label>
                        <textarea v-model="form.description" rows="5" placeholder="Descreva o problema detalhadamente..."
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400 resize-y" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Gravidade</label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="s in severities" :key="s.value" type="button" @click="form.severity = s.value"
                                class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                                :class="form.severity === s.value
                                    ? s.class + ' border-transparent'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-400'">
                                {{ s.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="activeStep === 1" class="space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Página</span>
                            <span class="text-gray-900 dark:text-white font-mono text-xs break-all max-w-[280px] text-right">{{ form.url }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Navegador</span>
                            <span class="text-gray-900 dark:text-white text-xs text-right">{{ browserInfo.userAgent }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sistema</span>
                            <span class="text-gray-900 dark:text-white text-xs text-right">{{ browserInfo.platform }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Resolução</span>
                            <span class="text-gray-900 dark:text-white text-xs text-right">{{ browserInfo.screen }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="activeStep === 2" class="space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-2 text-sm">
                        <p><span class="text-gray-500">Problema:</span> {{ form.description }}</p>
                        <p><span class="text-gray-500">Gravidade:</span> {{ severityLabel(form.severity) }}</p>
                        <p><span class="text-gray-500">Página:</span> <span class="font-mono text-xs">{{ form.url }}</span></p>
                        <p><span class="text-gray-500">Navegador:</span> {{ browserInfo.userAgent }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 border-t border-gray-100 dark:border-gray-800">
                <div>
                    <span v-if="submitted" class="text-sm text-green-600 dark:text-green-400 font-medium">Relatório enviado!</span>
                    <span v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="activeStep > 0" @click="activeStep--" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Voltar
                    </button>
                    <button v-if="activeStep < 2" @click="activeStep++" type="button"
                        class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                        Próximo
                    </button>
                    <button v-if="activeStep === 2" @click="submit" :disabled="sending"
                        class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors disabled:opacity-50">
                        {{ sending ? 'Enviando...' : 'Enviar Relatório' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const emit = defineEmits(['close'])

const page = usePage()

const activeStep = ref(0)
const sending = ref(false)
const submitted = ref(false)
const error = ref('')

const tabs = ['Descrever', 'Detalhes', 'Revisar']

const severities = [
    { value: 'low', label: 'Baixa', class: 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' },
    { value: 'normal', label: 'Normal', class: 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400' },
    { value: 'medium', label: 'Média', class: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400' },
    { value: 'high', label: 'Alta', class: 'bg-orange-100 dark:bg-orange-950/30 text-orange-700 dark:text-orange-400' },
    { value: 'critical', label: 'Crítica', class: 'bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400' },
]

const browserInfo = reactive({
    userAgent: navigator.userAgent,
    platform: navigator.platform,
    screen: `${window.screen.width}x${window.screen.height}`,
})

const form = reactive({
    description: '',
    severity: 'normal',
    url: page.url || window.location.href,
    page_name: document.title || '',
})

function severityLabel(value) {
    return severities.find(s => s.value === value)?.label || value
}

async function submit() {
    if (!form.description.trim()) {
        error.value = 'Descreva o problema antes de enviar.'
        return
    }

    sending.value = true
    error.value = ''

    try {
        const response = await window.axios.post('/api/report-problem', {
            description: form.description.trim(),
            severity: form.severity,
            url: form.url,
            page_name: form.page_name,
            browser_info: JSON.stringify(browserInfo),
        })

        if (response.status === 201) {
            submitted.value = true
            setTimeout(() => emit('close'), 1500)
        }
    } catch (e) {
        error.value = 'Erro ao enviar relatório. Tente novamente.'
    } finally {
        sending.value = false
    }
}
</script>
