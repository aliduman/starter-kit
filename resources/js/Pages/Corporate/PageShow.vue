<script setup>
defineProps({ page: { type: Object, required: true } });
</script>

<template>
    <article class="prose prose-slate max-w-none">
        <h1>{{ page.title }}</h1>
        <div v-html="page.body" />
    </article>
</template>
