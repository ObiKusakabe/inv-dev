<!-- <script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { computed, ref } from 'vue';
import ProductCard from './ProductCard.vue';

// Product type
interface Product {
    id: number | string;
    name: string;
    price: number; // in smallest currency unit, e.g. IDR as integer
    image?: string | null;
    stock?: number;
    description?: string;
}

// Accept products as props in future (when you plug DB/inertia)
// For now, local fallback data
const localProducts = ref<Product[]>([
    {
        id: 1,
        name: 'Kaos Oversize Hitam',
        price: 99000,
        image: null,
        stock: 10,
        description: 'Cotton combed 24s',
    },
    {
        id: 2,
        name: 'Jaket Denim',
        price: 250000,
        image: null,
        stock: 5,
        description: 'Denim original',
    },
    {
        id: 3,
        name: 'Hoodie Abu',
        price: 150000,
        image: null,
        stock: 7,
        description: 'Fleece nyaman',
    },
]);

// In future you'll accept props like: defineProps<{ products?: Product[] }>()
// and then use them if present. For now use localProducts directly.

const search = ref('');
const cart = useCartStore();

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return localProducts.value;
    return localProducts.value.filter(
        (p) =>
            p.name.toLowerCase().includes(q) ||
            (p.description || '').toLowerCase().includes(q),
    );
});

function addToCart(product: Product) {
    cart.add({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
    });
}
</script>

<template>
    <div>
        <div class="mb-4 flex items-center gap-2">
            <input
                v-model="search"
                placeholder="Cari produk..."
                class="input input-bordered w-full max-w-sm"
            />
            <div class="ml-auto text-sm text-muted-foreground">
                {{ filtered.length }} produk
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
            <ProductCard
                v-for="p in filtered"
                :key="p.id"
                :product="p"
                @add="addToCart(p)"
            />
        </div>
    </div>
</template> -->
<script setup lang="ts">
import ProductCard from './ProductCard.vue';

const products = [
    { id: 1, name: 'Kaos Polos', price: 50000 },
    { id: 2, name: 'Hoodie', price: 150000 },
    { id: 3, name: 'Jaket', price: 250000 },
];
</script>

<template>
    <div class="grid grid-cols-3 gap-4">
        <ProductCard
            v-for="product in products"
            :key="product.id"
            :product="product"
        />
    </div>
</template>
