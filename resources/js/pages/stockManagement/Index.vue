<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import stockManagement from '@/routes/stockManagement';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kelola Stok',
        href: stockManagement.index().url,
    },
];
</script>

<template>
    <Head title="Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            Manajemen Stok
        </div>
    </AppLayout>
</template>
