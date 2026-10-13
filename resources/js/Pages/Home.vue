<script setup>
import { Link } from '@inertiajs/vue3';
import Card from '~shared/Card.vue';

defineProps({
    modules: { type: Array, required: true },
});

const benefits = [
    'Modüler mimari — modülleri tek bayrakla aç/kapat',
    'Filament 5 admin paneli — anında yönetim',
    'Inertia + Vue 3 + Tailwind v4 — modern frontend',
];
</script>

<template>

    <section class="relative pt-10 pb-20 text-center">
        <p class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-xs font-semibold text-brand-700 ring-1 ring-brand-100">
            <span class="size-1.5 rounded-full bg-accent-500"></span>
            Laravel 13 · Vue 3 · Inertia 3 · Filament 5
        </p>

        <h1 class="mt-6 font-display font-extrabold tracking-tight text-brand-900 text-5xl md:text-6xl lg:text-7xl leading-[1.05]">
            Modüler. Hızlı.
            <span class="block bg-gradient-to-r from-brand-600 to-accent-500 bg-clip-text text-transparent">
                Üretime hazır.
            </span>
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-lg text-brand-800/80">
            Kurumsal, e-ticaret, emlak ve haber modülleri tek bir starter kit'te.
            Tek komutla başlat, dakikalar içinde yayına al.
        </p>

        <ul class="mx-auto mt-8 max-w-xl space-y-2 text-left text-sm text-brand-800">
            <li v-for="b in benefits" :key="b" class="flex items-start gap-3">
                <svg class="mt-0.5 size-5 flex-none text-accent-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.41 0l-3.5-3.5a1 1 0 011.41-1.42L8.5 12.09l6.79-6.8a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span>{{ b }}</span>
            </li>
        </ul>

        <div class="mt-10 flex items-center justify-center gap-3">
            <Link href="/admin" class="btn-primary text-base px-7 py-3">
                Yönetim Paneli
                <svg class="ml-2 size-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </Link>
            <a href="https://github.com" target="_blank" rel="noreferrer" class="btn-secondary text-base px-7 py-3">
                Dökümantasyon
            </a>
        </div>
    </section>

    <section class="pb-16">
        <div class="mb-8 text-center">
            <h2 class="font-display text-3xl md:text-4xl font-bold text-brand-900">Modüller</h2>
            <p class="mt-2 text-brand-800/70">İhtiyacın olanı aç, gerisini kapalı bırak.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card
                v-for="m in modules"
                :key="m.key"
                :title="m.name"
            >
                <p class="text-sm text-brand-800/75">{{ m.description }}</p>
                <span
                    class="mt-4 inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                    :class="m.enabled
                        ? 'bg-accent-500/10 text-accent-600 ring-1 ring-accent-500/30'
                        : 'bg-brand-100 text-brand-700 ring-1 ring-brand-200'"
                >
                    <span class="size-1.5 rounded-full" :class="m.enabled ? 'bg-accent-500' : 'bg-brand-400'"></span>
                    {{ m.enabled ? 'Aktif' : 'Kapalı' }}
                </span>
            </Card>
        </div>
    </section>
</template>
