<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ articles: { type: Object, required: true } });
</script>

<template>
    <h1 class="text-2xl font-bold">Haberler</h1>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link v-for="a in articles.data" :key="a.id"
              :href="`/haber/${a.slug}`" class="card hover:shadow-md transition">
            <h3 class="font-medium">{{ a.title }}</h3>
            <p class="text-sm text-slate-600 mt-2">{{ a.excerpt }}</p>
            <p class="text-xs text-slate-400 mt-3">{{ a.published_at }}</p>
        </Link>
    </div>
</template>
