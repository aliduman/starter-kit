<script setup>
defineProps({ announcement: { type: Object, required: true } });

const date = (value) => (value ? new Date(value).toLocaleDateString('tr-TR', { dateStyle: 'long' }) : null);
</script>

<template>
    <article class="mx-auto max-w-3xl">
        <p class="text-sm text-slate-400">{{ date(announcement.published_at) }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ announcement.title }}</h1>
        <p v-if="announcement.summary" class="mt-4 text-lg text-slate-600">{{ announcement.summary }}</p>
        <img v-if="announcement.cover" :src="announcement.cover" :alt="announcement.title" class="mt-6 w-full rounded-lg">
        <div v-if="announcement.body" class="prose mt-6 max-w-none" v-html="announcement.body" />
        <a v-if="announcement.link" :href="announcement.link" class="btn-primary mt-8 inline-flex" target="_blank" rel="noopener">
            {{ announcement.button_text || 'Ayrıntılar' }}
        </a>
    </article>
</template>
