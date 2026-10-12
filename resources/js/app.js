import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import AppLayout from '@/Layouts/AppLayout.vue';
import { route } from './route';

const appName = import.meta.env.VITE_APP_NAME || 'Starter Kit';

createInertiaApp({
    // App\Support\Seo\Seo::fullTitle ile aynı kural; site adını zaten içeren başlıklar olduğu gibi kullanılır.
    title: (title, page) => {
        const siteName = page?.props?.site?.name || appName;
        const separator = page?.props?.site?.title_separator ?? ' · ';

        if (!title) {
            return siteName;
        }

        return title.includes(siteName) ? title : `${title}${separator}${siteName}`;
    },
    // Sayfa `head` prop'undaki SEO etiketleri (App\Support\Seo\Seo) gezinmede de güncellenir.
    serverHead: true,
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

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia);

        // Şablonlarda route('ad', parametreler); <script setup> içinde '@/route' içe aktarılır.
        app.config.globalProperties.route = route;
        app.mount(el);
    },
    progress: { color: '#4f46e5' },
});
