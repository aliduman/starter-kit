import { createSSRApp, h } from 'vue';
import { renderToString } from '@vue/server-renderer';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route } from './route';

const appName = import.meta.env.VITE_APP_NAME || 'Starter Kit';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => (title ? `${title} · ${appName}` : appName),
        resolve: (name) =>
            resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ App, props, plugin }) {
            // Sunucuda `@routes` yok; route.js'in okuduğu küresel Ziggy sayfa prop'undan kurulur.
            globalThis.Ziggy = { ...page.props.ziggy, location: new URL(page.props.ziggy.location) };

            const app = createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(createPinia());

            app.config.globalProperties.route = route;

            return app;
        },
    }),
);
