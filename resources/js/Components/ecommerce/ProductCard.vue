<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    product: { type: Object, required: true },
});
</script>

<template>
    <Link :href="`/shop/products/${product.slug}`" class="card hover:shadow-md transition">
        <div class="aspect-video rounded bg-slate-100 mb-3"></div>
        <h3 class="font-medium text-slate-900">{{ product.name }}</h3>
        <p class="mt-1 text-brand-600 font-semibold">
            {{ product.price }} {{ product.currency }}
        </p>
    </Link>
</template>
