<template>
    <div class="space-y-3">
        <div v-if="files.length < maxFiles" class="flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-gray-400 dark:hover:border-gray-500 transition-colors cursor-pointer"
            :class="{ 'opacity-50': dragging }" @dragover.prevent="dragging = true" @dragleave="dragging = false" @drop.prevent="onDrop">
            <label class="flex flex-col items-center gap-2 cursor-pointer">
                <svg class="w-8 h-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Clique para selecionar, arraste ou cole imagens
                </span>
                <span class="text-xs text-gray-400">
                    PNG, JPEG, WebP ou GIF · até {{ maxSizeMb }}MB cada
                </span>
                <input type="file" :accept="accept" multiple :disabled="files.length >= maxFiles"
                    @change="onSelect" class="hidden" ref="fileInput" />
            </label>
        </div>

        <div v-if="errors.length" class="text-xs text-red-500 space-y-1">
            <p v-for="(err, i) in errors" :key="i">{{ err }}</p>
        </div>

        <div v-if="files.length" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
            <div v-for="(file, index) in files" :key="index"
                class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 aspect-square">
                <img :src="previews[index]" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center gap-1">
                    <button @click="($emit('preview', index)); $event.stopPropagation()"
                        class="p-1 rounded-full text-white opacity-0 group-hover:opacity-100 transition-opacity hover:text-blue-300">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button @click="$emit('mark-primary', index); $event.stopPropagation()"
                        class="p-1 rounded-full transition-opacity"
                        :class="primaryIndex === index
                            ? 'text-yellow-400 opacity-100'
                            : 'text-white opacity-0 group-hover:opacity-100'">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="primaryIndex === index ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </button>
                    <button @click="remove(index)"
                        class="p-1 rounded-full text-red-400 opacity-0 group-hover:opacity-100 transition-opacity hover:text-red-500">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
                <span v-if="primaryIndex === index"
                    class="absolute top-1 left-1 text-xs px-1.5 py-0.5 rounded bg-yellow-400 text-yellow-900 font-medium">
                    Principal
                </span>
                <span class="absolute bottom-1 right-1 text-[10px] px-1 py-0.5 rounded bg-black/60 text-white">
                    {{ formatSize(file.size) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    maxFiles: { type: Number, default: 5 },
    maxSizeMb: { type: Number, default: 5 },
    accept: { type: String, default: 'image/png,image/jpeg,image/webp,image/gif' },
    primaryIndex: { type: Number, default: 0 },
})

const emit = defineEmits(['update:modelValue', 'update:primaryIndex', 'mark-primary', 'preview'])

const fileInput = ref(null)
const dragging = ref(false)
const errors = ref([])
const previews = reactive({})
const files = ref(props.modelValue)

const allowedTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif']
const hashCache = new Map()

async function computeFileHash(file) {
    if (hashCache.has(file)) return hashCache.get(file)
    const buf = await file.arrayBuffer()
    const hash = await crypto.subtle.digest('SHA-256', buf)
    const hex = Array.from(new Uint8Array(hash)).map(b => b.toString(16).padStart(2, '0')).join('')
    hashCache.set(file, hex)
    return hex
}

onMounted(() => {
    for (let i = 0; i < files.value.length; i++) {
        if (!previews[i]) {
            previews[i] = URL.createObjectURL(files.value[i])
        }
    }
    window.addEventListener('paste', handleGlobalPaste)
})

onUnmounted(() => {
    window.removeEventListener('paste', handleGlobalPaste)
})

watch(() => props.modelValue, (val) => {
    files.value = val
}, { deep: true })

async function onSelect(e) {
    await addFiles(Array.from(e.target.files))
    e.target.value = ''
}

async function onDrop(e) {
    dragging.value = false
    await addFiles(Array.from(e.dataTransfer.files))
}

async function handleGlobalPaste(e) {
    const items = e.clipboardData?.items
    if (!items) return

    const pastedFiles = []
    for (const item of items) {
        if (item.type.startsWith('image/')) {
            const file = item.getAsFile()
            if (file) {
                const ext = file.type.split('/')[1] || 'png'
                pastedFiles.push(new File([file], `clipboard-${Date.now()}.${ext}`, { type: file.type }))
            }
        }
    }

    if (pastedFiles.length) {
        e.preventDefault()
        await addFiles(pastedFiles)
    }
}

async function addFiles(newFiles) {
    errors.value = []

    const total = files.value.length + newFiles.length
    if (total > props.maxFiles) {
        errors.value.push(`Máximo de ${props.maxFiles} imagens.`)
        return
    }

    const existingHashes = await Promise.all(files.value.map(f => computeFileHash(f)))

    const valid = []
    for (const file of newFiles) {
        if (!allowedTypes.includes(file.type)) {
            errors.value.push(`"${file.name}" não é um tipo de imagem permitido.`)
            continue
        }
        if (file.size > props.maxSizeMb * 1024 * 1024) {
            errors.value.push(`"${file.name}" excede ${props.maxSizeMb}MB.`)
            continue
        }
        const hash = await computeFileHash(file)
        if (existingHashes.includes(hash)) {
            errors.value.push(`"${file.name}" é uma imagem duplicada.`)
            continue
        }
        existingHashes.push(hash)
        valid.push(file)
    }

    const startIdx = files.value.length
    for (let i = 0; i < valid.length; i++) {
        const idx = startIdx + i
        const url = URL.createObjectURL(valid[i])
        previews[idx] = url
        files.value.push(valid[i])
    }

    if (!errors.value.length) {
        emit('update:modelValue', [...files.value])
    }
}

function remove(index) {
    URL.revokeObjectURL(previews[index])
    delete previews[index]
    files.value.splice(index, 1)
    // Rebuild previews
    for (let i = 0; i < files.value.length; i++) {
        if (!previews[i]) {
            previews[i] = URL.createObjectURL(files.value[i])
        }
    }
    if (props.primaryIndex >= files.value.length) {
        emit('update:primaryIndex', Math.max(0, files.value.length - 1))
    }
    emit('update:modelValue', [...files.value])
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + 'B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + 'KB'
    return (bytes / (1024 * 1024)).toFixed(1) + 'MB'
}
</script>
