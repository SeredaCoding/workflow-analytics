import { ref } from 'vue'

const toasts = ref([])
let nextId = 0

export function useToast() {
    function add({ type = 'info', message, duration = 5000 }) {
        const id = ++nextId
        toasts.value.push({ id, type, message })
        if (duration > 0) {
            setTimeout(() => remove(id), duration)
        }
        return id
    }

    function remove(id) {
        const idx = toasts.value.findIndex(t => t.id === id)
        if (idx !== -1) toasts.value.splice(idx, 1)
    }

    function success(message, duration) {
        return add({ type: 'success', message, duration })
    }

    function error(message, duration) {
        return add({ type: 'error', message, duration })
    }

    function warning(message, duration) {
        return add({ type: 'warning', message, duration })
    }

    function info(message, duration) {
        return add({ type: 'info', message, duration })
    }

    return { toasts, add, remove, success, error, warning, info }
}
