<script setup>
defineProps({ article: { type: Object, required: true } });
</script>

<template>
    <article class="prose prose-slate max-w-none">
        <h1>{{ article.title }}</h1>
        <p class="lead">{{ article.excerpt }}</p>
        <div v-html="article.body" />
    </article>
</template>
