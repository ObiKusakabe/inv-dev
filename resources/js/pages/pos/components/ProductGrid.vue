<script setup lang="ts">
import { computed, ref } from 'vue';
import ProductCard from './ProductCard.vue';
import type { Product } from '../Index.vue';

const props = defineProps<{
    products: Product[];
}>();

const search = ref('');

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.products;
    return props.products.filter((p) => {
        const name = (p.name || '').toLowerCase();
        const desc = (p.description || '').toLowerCase();
        return name.includes(q) || desc.includes(q);
    });
});
</script>

<template>
    <div>
        <div class="mb-4 flex items-center gap-2">
            <input
                v-model="search"
                placeholder="Cari produk..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
            <div class="text-sm text-muted-foreground">
                {{ filtered.length }} produk
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <ProductCard v-for="p in filtered" :key="p.id" :product="p" />
        </div>
    </div>
</template>
