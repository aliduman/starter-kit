<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <section class="rounded-xl bg-brand-50 px-8 py-16">
        <h1 class="text-3xl font-bold text-brand-700">{{ title }}</h1>
        <p v-if="subtitle" class="mt-2 text-slate-700">{{ subtitle }}</p>
    </section>
</template>
