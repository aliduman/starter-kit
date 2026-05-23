import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
    }),
    getters: {
        count: (state) => state.items.reduce((sum, item) => sum + item.qty, 0),
        total: (state) =>
            state.items.reduce((sum, item) => sum + item.qty * Number(item.price), 0),
    },
    actions: {
        add(product, qty = 1) {
            const existing = this.items.find((i) => i.id === product.id);
            if (existing) {
                existing.qty += qty;
            } else {
                this.items.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    qty,
                });
            }
        },
        remove(id) {
            this.items = this.items.filter((i) => i.id !== id);
        },
        clear() {
            this.items = [];
        },
    },
    persist: true,
});
