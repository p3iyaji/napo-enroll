import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

//my imports
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';

import { resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers'
import { createApp, createSSRApp, h, type DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob('./pages/**/*.vue'),
        ) as Promise<DefineComponent>,
    setup({ el, App, props, plugin}) {
        // SSR passes el: null; use createSSRApp and return the app for renderToString.
        // Client hydration uses data-server-rendered; plain client uses createApp.
        const app =
            el === null || el?.hasAttribute('data-server-rendered')
                ? createSSRApp({ render: () => h(App, props) })
                : createApp({ render: () => h(App, props) });
        app.use(plugin);

        //register element plus
        app.use(ElementPlus);

        if (el) {
            app.mount(el);
        }

        return app;
    },

    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
