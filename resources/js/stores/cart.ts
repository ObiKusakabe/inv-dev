import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export interface CartItem {
    id: string | number;
    name: string;
    price: number;
    qty: number;
    image?: string | null;
    stock?: number; // optional, buat limit qty nantinya
}

const STORAGE_KEY = 'inv-dev.cart';

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>([]);

    // Load from localStorage
    if (typeof window !== 'undefined') {
        try {
            const raw = window.localStorage.getItem(STORAGE_KEY);
            if (raw) items.value = JSON.parse(raw) as CartItem[];
        } catch {
            // ignore
        }
    }

    function persist() {
        if (typeof window === 'undefined') return;
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value));
    }

    function findIndex(id: string | number) {
        return items.value.findIndex((i) => i.id === id);
    }

    function add(payload: Omit<CartItem, 'qty'>, qty = 1) {
        const idx = findIndex(payload.id);
        if (idx !== -1) {
            const next = items.value[idx].qty + qty;

            // optional stock limit
            if (typeof items.value[idx].stock === 'number') {
                items.value[idx].qty = Math.min(next, items.value[idx].stock!);
            } else {
                items.value[idx].qty = next;
            }

            persist();
            return;
        }

        const initialQty = Math.max(1, qty);
        items.value.push({ ...payload, qty: initialQty });
        persist();
    }

    function remove(id: string | number) {
        const idx = findIndex(id);
        if (idx !== -1) {
            items.value.splice(idx, 1);
            persist();
        }
    }

    function increase(id: string | number) {
        const idx = findIndex(id);
        if (idx !== -1) {
            const item = items.value[idx];
            const next = item.qty + 1;

            if (typeof item.stock === 'number') {
                item.qty = Math.min(next, item.stock);
            } else {
                item.qty = next;
            }

            persist();
        }
    }

    function decrease(id: string | number) {
        const idx = findIndex(id);
        if (idx !== -1) {
            const item = items.value[idx];
            item.qty -= 1;
            if (item.qty <= 0) items.value.splice(idx, 1);
            persist();
        }
    }

    function setQty(id: string | number, qty: number) {
        const idx = findIndex(id);
        if (idx === -1) return;

        const safeQty = Math.max(1, Math.floor(qty || 1));
        const item = items.value[idx];

        if (typeof item.stock === 'number') {
            item.qty = Math.min(safeQty, item.stock);
        } else {
            item.qty = safeQty;
        }

        persist();
    }

    function clear() {
        items.value = [];
        persist();
    }

    const totalQty = computed(() =>
        items.value.reduce((sum, item) => sum + item.qty, 0),
    );

    const totalPrice = computed(() =>
        items.value.reduce((sum, item) => sum + item.price * item.qty, 0),
    );

    const isEmpty = computed(() => items.value.length === 0);

    return {
        items,
        add,
        remove,
        increase,
        decrease,
        setQty,
        clear,
        totalQty,
        totalPrice,
        isEmpty,
    };
});
