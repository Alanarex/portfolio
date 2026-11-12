import { createInertiaApp, router } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
});

// Indique une navigation Inertia en cours (atténuation du contenu, cf. dashboard.css).
router.on('start', () => document.documentElement.setAttribute('data-navigating', ''));
router.on('finish', () => document.documentElement.removeAttribute('data-navigating'));
