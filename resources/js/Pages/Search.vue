<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    query: { type: String, default: '' },
    groups: { type: Array, default: () => [] },
});

const term = ref(props.query);

const submit = () => router.get(route('search'), { q: term.value }, { preserveState: true });
</script>

<template>
    <h1 class="text-2xl font-bold">Arama</h1>

    <form class="mt-6 flex max-w-xl gap-2" role="search" @submit.prevent="submit">
        <label for="search-query" class="sr-only">Aranacak kelime</label>
        <input id="search-query" v-model="term" type="search" class="flex-1 rounded-md border border-slate-300 px-4 py-2" placeholder="Ne aramıştınız?">
        <button type="submit" class="btn-primary">Ara</button>
    </form>

    <p v-if="query.length >= 2 && !groups.length" class="mt-8 text-slate-600">
        "{{ query }}" için sonuç bulunamadı.
    </p>

    <section v-for="group in groups" :key="group.key" class="mt-10">
        <h2 class="text-lg font-semibold">{{ group.label }}</h2>
        <ul class="mt-4 space-y-4">
            <li v-for="result in group.results" :key="result.url">
                <Link :href="result.url" class="font-medium text-brand-700 hover:underline">{{ result.title }}</Link>
                <p v-if="result.excerpt" class="mt-1 text-sm text-slate-600">{{ result.excerpt }}</p>
            </li>
        </ul>
    </section>
</template>
