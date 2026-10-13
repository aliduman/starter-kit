<script setup>
import { useCartStore } from '@/Stores/cart.js';

const props = defineProps({ product: { type: Object, required: true } });
const cart = useCartStore();
</script>

<template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="aspect-square bg-slate-100 rounded-xl"></div>
        <div>
            <h1 class="text-3xl font-bold">{{ product.name }}</h1>
            <p class="mt-2 text-2xl text-brand-600 font-semibold">
                {{ product.price }} {{ product.currency }}
            </p>
            <p class="mt-4 text-slate-600">{{ product.description }}</p>
            <button class="btn-primary mt-8" @click="cart.add(product)">Sepete ekle</button>
        </div>
    </div>
</template>
