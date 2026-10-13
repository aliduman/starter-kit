import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import AppLayout from '@/Layouts/AppLayout.vue';
import { route } from './route';
import { startAnalytics } from './analytics';

createInertiaApp({
    // Başlık dahil SEO etiketlerinin tek kaynağı sayfanın `head` prop'u (App\Support\Seo\Seo); gezinmede de
    // güncellenir. Sayfalar <Head> ile başlık vermez, verirse sunucu başlığını ezer.
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

        // Önceki ziyarette onay verildiyse ölçüm hemen başlar; yoksa çerez bandı (CookieConsent) sorar.
        startAnalytics(props.initialPage.props.analytics);
    },
    progress: { color: '#4f46e5' },
});
