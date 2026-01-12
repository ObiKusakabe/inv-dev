import { defineStore } from 'pinia';
import { ref, computed } from 'vue';


export interface CartItem {
    id: string | number;
    name: string;
    price: number;
    qty: number;
    image?: string | null;
}

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>([]);


    // load from localStorage (simple persistence)
    if (typeof window !== 'undefined') {
        const saved = localStorage.getItem('pos_cart');
        if (saved) {
            try { items.value = JSON.parse(saved); } catch (e) { items.value = []; }
        }
    }


    function persist() {
        if (typeof window !== 'undefined') {
            localStorage.setItem('pos_cart', JSON.stringify(items.value));
        }
    }


    function findIndex(id: string | number) {
        return items.value.findIndex(i => i.id === id);
    }


    function add(payload: { id: string | number; name: string; price: number; image?: string | null }) {
        const idx = findIndex(payload.id);
        if (idx === -1) {
            items.value.push({ ...payload, qty: 1 });
        } else {
            items.value[idx].qty += 1;
        }
        persist();
    }


    function remove(id: string | number) {
        const idx = findIndex(id);
        if (idx !== -1) items.value.splice(idx, 1);
        persist();
    }


    function increase(id: string | number) {
        const idx = findIndex(id);
        if (idx !== -1) {
            items.value[idx].qty += 1;
            persist();
        }
    }


    function decrease(id: string | number) {
        const idx = findIndex(id);
        if (idx !== -1) {
            items.value[idx].qty -= 1;
            if (items.value[idx].qty <= 0) items.value.splice(idx, 1);
            persist();
        }
    }


    function clear() {
        items.value = [];
        persist();
    }


    const totalQty = computed(() => items.value.reduc