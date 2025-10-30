import '../css/app.css';
import '../js/bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import Toast, { type PluginOptions } from "vue-toastification";
import "vue-toastification/dist/index.css"; // Importa el CSS

const toastOptions: PluginOptions = {
    timeout: 5000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false
};
import { initializeTheme } from './composables/useAppearance';
import '@vueform/multiselect/themes/default.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),

    // --- AQUÍ ESTÁ LA CORRECCIÓN ---
    setup({ el, App, props, plugin }) {
        // 1. Guarda la app en una constante
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, toastOptions); // 2. Llama a .use(Toast, ...)

        // 3. Llama a .mount(el) AL FINAL
        app.mount(el);
    },
    // --- FIN DE LA CORRECCIÓN ---

    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();