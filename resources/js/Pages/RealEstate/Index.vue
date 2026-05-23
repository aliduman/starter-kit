<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    listings: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});
</script>

<template>
    <Head title="Emlak" />
    <h1 class="text-2xl font-bold">Emlak İlanları</h1>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link v-for="l in listings.data" :key="l.id"
              :href="`/emlak/${l.slug}`" class="card hover:shadow-md transition">
            <div class="aspect-video bg-slate-100 rounded mb-3"></div>
            <h3 class="font-medium">{{ l.title }}</h3>
            <p class="text-sm text-slate-600">{{ l.city }} · {{ l.district }}</p>
            <p class="mt-2 text-brand-600 font-semibold">{{ l.price }} {{ l.currency }}</p>
        </Link>
    </div>
</template>
