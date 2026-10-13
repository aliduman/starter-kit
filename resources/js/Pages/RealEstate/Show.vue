<script setup>
defineProps({ listing: { type: Object, required: true } });
</script>

<template>
    <h1 class="text-3xl font-bold">{{ listing.title }}</h1>
    <p class="mt-1 text-slate-600">{{ listing.city }} · {{ listing.district }}</p>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <div class="aspect-video rounded-xl bg-slate-100"></div>
            <div class="prose mt-6 max-w-none" v-html="listing.description" />
        </div>
        <aside class="card">
            <p class="text-2xl text-brand-600 font-bold">{{ listing.price }} {{ listing.currency }}</p>
            <dl class="mt-4 text-sm grid grid-cols-2 gap-y-2">
                <dt class="text-slate-500">Oda</dt><dd>{{ listing.rooms }}</dd>
                <dt class="text-slate-500">Banyo</dt><dd>{{ listing.bathrooms }}</dd>
                <dt class="text-slate-500">m²</dt><dd>{{ listing.area_m2 }}</dd>
            </dl>
        </aside>
    </div>
</template>
