<script setup>
import ProductCard from '~ecommerce/ProductCard.vue';

defineProps({ products: { type: Object, required: true } });
</script>

<template>
    <h1 class="text-2xl font-bold">Ürünler</h1>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <ProductCard v-for="product in products.data" :key="product.id" :product="product" />
    </div>

    <p v-if="!products.data.length" class="mt-8 text-slate-500">Henüz ürün eklenmemiş.</p>
</template>
