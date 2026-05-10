<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import LoadingOverlay from '@/components/ui/loading-overlay/LoadingOverlay.vue';
import PrefetchLinks from '@/components/PrefetchLinks.vue';
import AppFooter from '@/components/AppFooter.vue';
import { Toaster } from 'vue-sonner';
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Global page loading state
const isPageLoading = ref(false);

onMounted(() => {
    router.on('start', () => {
        isPageLoading.value = true;
    });
    router.on('finish', () => {
        isPageLoading.value = false;
    });
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
            <AppFooter />
        </AppContent>
    </AppShell>
    <PrefetchLinks />
    <LoadingOverlay :show="isPageLoading" message="Memuat halaman…" />
    <Toaster 
        position="top-right" 
        richColors 
        expand 
        :visible-toasts="4" 
        :class-name="'!fixed !top-4 !right-4 !z-[9999]'"
    />
</template>
