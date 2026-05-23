import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Starter Kit';

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        );
        page.then((module) => {
            module.default.layout = module.default.layout || AppLayout;
        });
        return page;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: { color: '#4f46e5' },
});
