<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({ page: { type: Object, required: true } });
</script>

<template>
    <Head :title="page.title" />
    <article class="prose prose-slate max-w-none">
        <h1>{{ page.title }}</h1>
        <div v-html="page.body" />
    </article>
</template>
