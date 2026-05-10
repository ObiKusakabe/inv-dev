<script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import type { Product } from '../Index.vue';
import { computed } from 'vue';
import { Plus, Minus } from 'lucide-vue-next';

const props = defineProps<{ product: Product }>();

const cart = useCartStore();

const cartItem = computed(() => cart.items.find(i => i.id === props.product.id));
const qty = computed(() => cartItem.value?.qty ?? 0);
const inCart = computed(() => qty.value > 0);

function formatIDR(v: number) {
    return new Intl.NumberFormat('id-ID').format(v);
}

function add() {
    if ((props.product.stock ?? 999999) <= 0) return;

    cart.add({
        id: props.product.id,
        name: props.product.name,
        price: props.product.price,
        image: props.product.image ?? null,
        stock: props.product.stock,
    });
}

function remove(e: Event) {
    e.stopPropagation();
    cart.remove(props.product.id);
}
</script>

<template>
    <div
        class="group rounded-lg border bg-background shadow-sm overflow-hidden cursor-pointer hover:shadow-md transition-all relative"
        :class="(product.stock ?? 1) <= 0 ? 'opacity-60 cursor-not-allowed' : ''"
        @click="add"
    >
        <!-- Product Image -->
        <div class="aspect-square bg-background relative overflow-hidden">
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
            />
            <div v-else class="h-full w-full flex items-center justify-center text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <!-- Add Icon (green +) when NOT in cart -->
            <button
                v-if="!inCart && (product.stock ?? 0) > 0"
                class="absolute top-2 right-2 h-7 w-7 rounded-full bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition-colors shadow-sm"
                @click.stop="add"
            >
                <Plus class="h-4 w-4" />
            </button>

            <!-- Remove Icon (red -) when IN cart -->
            <button
                v-if="inCart"
                class="absolute top-2 right-2 h-7 w-7 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm"
                @click.stop="remove"
            >
                <Minus class="h-4 w-4" />
            </button>

            <!-- Out of stock badge -->
            <span
                v-if="(product.stock ?? 0) <= 0"
                class="absolute top-2 right-2 rounded-full bg-red-500 px-2 py-1 text-xs text-white font-medium"
            >
                Habis
            </span>

            <!-- Qty badge when in cart -->
            <span
                v-if="inCart"
                class="absolute bottom-2 right-2 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground font-medium"
            >
                {{ qty }}x
            </span>
        </div>

        <!-- Product Info -->
        <div class="p-3">
            <!-- Category -->
            <p v-if="product.category" class="text-xs text-muted-foreground">
                {{ product.category }}
            </p>
            <h3 class="line-clamp-2 text-sm font-semibold">{{ product.name }}</h3>
            
            <!-- Separator -->
            <div class="my-2 border-t border-dashed border-border"></div>
            
            <!-- Stock & Price Row -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-rose-500 font-medium">
                    {{ product.stock ?? 0 }} Pcs
                </span>
                <span class="text-sm font-bold text-primary">
                    Rp {{ formatIDR(product.price) }}
                </span>
            </div>
        </div>
    </div>
</template>
