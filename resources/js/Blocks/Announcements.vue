<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    data: { type: Object, required: true },
});
</script>

<template>
    <section v-if="data.announcements.length" class="pb-16">
        <div class="mb-6 flex items-end justify-between">
            <h2 class="font-display text-3xl font-bold text-brand-900">{{ title }}</h2>
            <Link :href="data.url" class="text-sm font-semibold text-brand-700">Tümü →</Link>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <Link v-for="item in data.announcements" :key="item.id" :href="item.url" class="card hover:shadow-md transition">
                <h3 class="font-medium">{{ item.title }}</h3>
                <p v-if="item.summary" class="mt-2 text-sm text-slate-600">{{ item.summary }}</p>
            </Link>
        </div>
    </section>
</template>
