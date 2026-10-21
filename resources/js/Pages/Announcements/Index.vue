<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ announcements: { type: Object, required: true } });

const date = (value) => (value ? new Date(value).toLocaleDateString('tr-TR', { dateStyle: 'long' }) : null);
</script>

<template>
    <h1 class="text-2xl font-bold">Duyurular</h1>

    <p v-if="!announcements.data.length" class="mt-6 text-slate-600">Henüz duyuru yok.</p>

    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Link v-for="item in announcements.data" :key="item.id" :href="item.url" class="card hover:shadow-md transition">
            <img v-if="item.cover" :src="item.cover" :alt="item.title" class="mb-4 aspect-video w-full rounded-md object-cover">
            <p class="text-xs text-slate-400">{{ date(item.published_at) }}</p>
            <h2 class="mt-1 font-medium">{{ item.title }}</h2>
            <p v-if="item.summary" class="mt-2 text-sm text-slate-600">{{ item.summary }}</p>
        </Link>
    </div>

    <nav v-if="announcements.last_page > 1" class="mt-8 flex gap-2" aria-label="Sayfalar">
        <template v-for="link in announcements.links" :key="link.label">
            <Link v-if="link.url" :href="link.url" class="btn-ghost" :class="{ 'font-bold': link.active }" v-html="link.label" />
        </template>
    </nav>
</template>
