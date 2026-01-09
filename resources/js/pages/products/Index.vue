<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import products from '@/routes/products';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pakaian',
        href: products.index().url,
    },
];
</script>

<template>
    <Head title="Pakaian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            test
        </div>
    </AppLayout>
</template>
