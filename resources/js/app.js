import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

router.on('error', (event) => {
    const toast = useToast()
    const response = event?.detail?.response
    let message = 'Ocorreu um erro inesperado.'

    if (response?.status === 403) {
        message = 'Você não tem permissão para realizar esta ação.'
    } else if (response?.status === 404) {
        message = 'Recurso não encontrado.'
    } else if (response?.status === 422 && response?.data?.errors) {
        const errors = response.data.errors
        const firstKey = Object.keys(errors)[0]
        if (firstKey) message = errors[firstKey][0]
        else message = 'Dados inválidos.'
    } else if (response?.data?.message) {
        message = response.data.message
    }

    toast.error(message)
})

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
