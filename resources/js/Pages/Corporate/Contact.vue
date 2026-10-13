<script setup>
import PageHero from '~corporate/PageHero.vue';
</script>

<template>
    <PageHero title="İletişim" subtitle="Bizimle iletişime geçin." />
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
            <h3 class="font-semibold">Adres</h3>
            <p class="text-sm text-slate-600 mt-1">Şirket adresinizi buraya yazın.</p>
        </div>
        <div class="card">
            <h3 class="font-semibold">E-posta</h3>
            <p class="text-sm text-slate-600 mt-1">info@example.com</p>
        </div>
    </div>
</template>
