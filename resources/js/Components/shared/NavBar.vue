<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const modules = computed(() => page.props.modules || {});

const links = computed(() => [
    { label: 'Anasayfa', href: '/', show: true },
    { label: 'Hakkımızda', href: '/about', show: modules.value?.corporate?.enabled },
    { label: 'Mağaza', href: '/shop', show: modules.value?.ecommerce?.enabled },
    { label: 'Emlak', href: '/emlak', show: modules.value?.realestate?.enabled },
    { label: 'Haberler', href: '/haber', show: modules.value?.media?.enabled },
]);
</script>

<template>
    <header class="sticky top-0 z-40 backdrop-blur-md bg-white/70 border-b border-white/40">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
            <Link href="/" class="font-display text-lg font-bold text-brand-900 tracking-tight">
                {{ $page.props.app.name }}
            </Link>
            <ul class="flex items-center gap-1 text-sm">
                <li v-for="link in links.filter(l => l.show)" :key="link.href">
                    <Link
                        :href="link.href"
                        class="btn-ghost"
                    >
                        {{ link.label }}
                    </Link>
                </li>
                <li v-if="!$page.props.auth.user" class="ml-2">
                    <Link href="/login" class="btn-primary">Giriş</Link>
                </li>
                <li v-else class="ml-3 text-brand-800 font-medium">
                    {{ $page.props.auth.user.name }}
                </li>
            </ul>
        </nav>
    </header>
</template>
