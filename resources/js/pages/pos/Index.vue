<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import pos from '@/routes/pos';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'POS',
        href: pos.index().url,
    },
];
</script>

<template>
    <Head title="POS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            POS
        </div>
    </AppLayout>
</template>
