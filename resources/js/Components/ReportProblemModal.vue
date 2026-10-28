<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-[10vh] bg-black/20 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reportar Problema</h3>
                <button @click="$emit('close')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

                <div v-if="!submittedReport" class="flex gap-1 border-b border-gray-200 dark:border-gray-800 px-4">
                    <button v-for="(tab, i) in tabs" :key="i" @click="goToStep(i)" type="button"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px"
                    :class="activeStep === i
                        ? 'border-gray-900 dark:border-white text-gray-900 dark:text-white'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                    {{ tab }}
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div v-if="submittedReport" class="space-y-4 text-center py-4">
                    <div class="w-12 h-12 mx-auto bg-green-100 dark:bg-green-950/30 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Relatório enviado!</h3>
                        <p class="text-xs text-gray-400 mt-1">Protocolo #{{ submittedReport.id }}</p>
                    </div>
                    <div v-if="submittedReport.images?.length" class="flex gap-2 justify-center flex-wrap pt-2">
                        <div v-for="(img, idx) in submittedReport.images" :key="idx"
                            class="relative w-16 h-14 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0"
                            :class="img.primary ? 'ring-2 ring-yellow-400' : ''">
                            <img :src="imageUrl(submittedReport.id, idx)" class="w-full h-full object-cover" />
                            <span v-if="img.primary" class="absolute top-0 left-0 text-[8px] px-1 py-0.5 bg-yellow-400 text-yellow-900 font-medium rounded-br">★</span>
                        </div>
                    </div>
                </div>

                <div v-if="activeStep === 0 && !submittedReport" class="space-y-4">
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

                <div v-if="activeStep === 1 && !submittedReport" class="space-y-4">
                    <label class="block text-xs text-gray-500 mb-1">Imagens (opcional, até 5)</label>
                    <ImageUploader
                        v-model="uploadedFiles"
                        :primary-index="primaryIndex"
                        @mark-primary="primaryIndex = $event"
                        @update:primary-index="primaryIndex = $event"
                        @preview="openLightbox" />
                </div>

                <div v-if="activeStep === 2 && !submittedReport" class="space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Página</span>
                            <span class="text-gray-900 dark:text-white font-mono text-xs break-all max-w-[250px] text-right">{{ form.url }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Versão</span>
                            <span class="text-gray-900 dark:text-white text-xs">{{ appVersion }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Navegador</span>
                            <span class="text-gray-900 dark:text-white text-xs text-right max-w-[250px] truncate" :title="browserInfo.userAgent">{{ browserInfo.userAgent }}</span>
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

                <div v-if="activeStep === 3 && !submittedReport" class="space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-2 text-sm">
                        <p><span class="text-gray-500">Problema:</span> {{ form.description }}</p>
                        <p><span class="text-gray-500">Gravidade:</span> {{ severityLabel(form.severity) }}</p>
                        <p><span class="text-gray-500">Versão:</span> {{ appVersion }}</p>
                        <p><span class="text-gray-500">Página:</span> <span class="font-mono text-xs">{{ form.url }}</span></p>
                        <p v-if="uploadedFiles.length">
                            <span class="text-gray-500">Imagens:</span>
                            <span class="text-xs text-gray-600 dark:text-gray-400"> {{ uploadedFiles.length }} anexada(s)</span>
                        </p>
                    </div>
                    <div v-if="uploadedFiles.length" class="flex gap-2 flex-wrap">
                        <div v-for="(file, idx) in uploadedFiles" :key="idx"
                            class="relative w-16 h-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer group"
                            :class="primaryIndex === idx ? 'ring-2 ring-yellow-400' : ''" @click="openLightbox(idx)">
                            <img :src="thumbPreviews[idx]" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                            <span v-if="primaryIndex === idx"
                                class="absolute top-0 left-0 text-[8px] px-1 py-0.5 bg-yellow-400 text-yellow-900 font-medium rounded-br">
                                ★
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        <div class="flex items-center justify-between p-4 border-t border-gray-100 dark:border-gray-800">
                <div>
                    <span v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button v-if="submittedReport" @click="$emit('close')" type="button"
                        class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                        Fechar
                    </button>
                    <template v-if="!submittedReport">
                        <button v-if="activeStep > 0" @click="prevStep" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Voltar
                        </button>
                        <button v-if="activeStep < 3" @click="nextStep" type="button"
                            class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                            Próximo
                        </button>
                        <button v-if="activeStep === 3" @click="submit" :disabled="sending"
                            class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors disabled:opacity-50">
                            {{ sending ? 'Enviando...' : 'Enviar Relatório' }}
                        </button>
                    </template>
                </div>
            </div>
    </div>
</div>

<div v-if="lightbox.show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
    @click.self="closeLightbox">
    <div class="relative max-w-3xl max-h-[90vh]">
        <button @click="closeLightbox"
            class="absolute -top-10 right-0 text-white/70 hover:text-white transition-colors text-sm">
            Fechar
        </button>
        <div class="flex items-center gap-3">
            <button v-if="lightbox.index > 0" @click="prevLightbox" class="text-white/70 hover:text-white transition-colors shrink-0">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <img :src="lightbox.src" class="max-w-full max-h-[80vh] rounded-lg" />
            <button v-if="lightbox.index < lightbox.total - 1" @click="nextLightbox" class="text-white/70 hover:text-white transition-colors shrink-0">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
        <div class="text-center mt-2 text-xs text-white/50">
            {{ lightbox.index + 1 }} / {{ lightbox.total }}
        </div>
    </div>
</div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ImageUploader from '@/Components/ImageUploader.vue'

const emit = defineEmits(['close'])

const page = usePage()

const activeStep = ref(0)
const sending = ref(false)
const submittedReport = ref(null)
const error = ref('')

const tabs = ['Descrever', 'Imagens', 'Detalhes', 'Revisar']

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

const appVersion = page.props.app_version || '0.1.0'

const form = reactive({
    description: '',
    severity: 'normal',
    url: page.url || window.location.href,
    page_name: document.title || '',
})

const uploadedFiles = ref([])
const primaryIndex = ref(0)
const thumbPreviews = reactive({})

const lightbox = reactive({
    show: false,
    src: '',
    index: 0,
    total: 0,
})

function severityLabel(value) {
    return severities.find(s => s.value === value)?.label || value
}

function imageUrl(reportId, index) {
    return route('admin.problem-reports.images', [reportId, index])
}

function openLightbox(index) {
    if (!thumbPreviews[index]) return
    lightbox.show = true
    lightbox.src = thumbPreviews[index]
    lightbox.index = index
    lightbox.total = uploadedFiles.value.length
}

function closeLightbox() {
    lightbox.show = false
}

function prevLightbox() {
    if (lightbox.index > 0) {
        lightbox.index--
        lightbox.src = thumbPreviews[lightbox.index]
    }
}

function nextLightbox() {
    if (lightbox.index < lightbox.total - 1) {
        lightbox.index++
        lightbox.src = thumbPreviews[lightbox.index]
    }
}

function goToStep(i) {
    if (activeStep.value === 1 && i > 1) {
        for (let j = 0; j < uploadedFiles.value.length; j++) {
            if (!thumbPreviews[j]) {
                thumbPreviews[j] = URL.createObjectURL(uploadedFiles.value[j])
            }
        }
    }
    activeStep.value = i
}

function prevStep() {
    if (activeStep.value > 0) activeStep.value--
}

function nextStep() {
    if (activeStep.value === 0 && !form.description.trim()) {
        error.value = 'Descreva o problema antes de continuar.'
        return
    }
    error.value = ''
    if (activeStep.value === 1) {
        for (let i = 0; i < uploadedFiles.value.length; i++) {
            if (!thumbPreviews[i]) {
                thumbPreviews[i] = URL.createObjectURL(uploadedFiles.value[i])
            }
        }
    }
    activeStep.value++
}

async function submit() {
    if (!form.description.trim()) {
        error.value = 'Descreva o problema antes de enviar.'
        return
    }

    sending.value = true
    error.value = ''

    try {
        const formData = new FormData()
        formData.append('description', form.description.trim())
        formData.append('severity', form.severity)
        formData.append('url', form.url)
        formData.append('page_name', form.page_name)
        formData.append('app_version', appVersion)
        formData.append('primary_index', primaryIndex.value)
        formData.append('browser_info', JSON.stringify(browserInfo))

        for (const file of uploadedFiles.value) {
            formData.append('images[]', file)
        }

        const response = await window.axios.post('/api/report-problem', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        if (response.status === 201) {
            submittedReport.value = response.data
        }
    } catch (e) {
        if (e.response?.data?.message) {
            error.value = e.response.data.message
        } else {
            error.value = 'Erro ao enviar relatório. Tente novamente.'
        }
    } finally {
        sending.value = false
    }
}
</script>
