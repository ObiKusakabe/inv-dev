<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import category from '@/routes/category';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Kategori',
        href: category.index().url,
    },
];
</script>

<template>
    <Head title="Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            Kategorii
        </div>
    </AppLayout>
</template>
