<script setup>
defineProps({
    title: { type: String, default: null },
});
</script>

<template>
    <article class="card-glass hover:-translate-y-1 hover:shadow-lg">
        <h3 v-if="title" class="font-display text-base font-semibold text-brand-900 mb-2">{{ title }}</h3>
        <slot />
    </article>
</template>
