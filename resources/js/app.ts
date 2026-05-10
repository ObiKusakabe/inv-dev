import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h, ref, onMounted, onUnmounted } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { createPinia } from 'pinia';
import NoInternet from '@/components/NoInternet.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const isOnline = ref(true);
        
        const updateOnlineStatus = () => {
            isOnline.value = navigator.onLine;
        };
        
        const app = createApp({
            setup() {
                onMounted(() => {
                    window.addEventListener('online', updateOnlineStatus);
                    window.addEventListener('offline', updateOnlineStatus);
                    updateOnlineStatus();
                });
                
                onUnmounted(() => {
                    window.removeEventListener('online', updateOnlineStatus);
                    window.removeEventListener('offline', updateOnlineStatus);
                });
                
                const handleRetry = () => {
                    window.location.reload();
                };
                
                return () => isOnline.value ? h(App, props) : h(NoInternet, { onRetry: handleRetry });
            }
        })
            .use(plugin)
            .use(createPinia())
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Disable prefetch on hover - hanya prefetch saat mount jika diperlukan
// Ini mengurangi loading indicator yang muncul saat hover link
declare module '@inertiajs/vue3' {
    interface VisitOptions {
        prefetch?: boolean | 'mount' | 'hover';
    }
}

// This will set light / dark mode on page load...
initializeTheme();
