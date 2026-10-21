<script setup>
// Ana sayfa bölümleri (App\Support\Homepage\HomepageBlocks): her bölüm resources/js/Blocks/{component}.vue ile çizilir.
const blocks = import.meta.glob('@/Blocks/*.vue', { eager: true, import: 'default' });

defineProps({
    sections: { type: Array, required: true },
});

const component = (name) => blocks[`/resources/js/Blocks/${name}.vue`];
</script>

<template>
    <template v-for="section in sections" :key="section.key">
        <component :is="component(section.component)" v-if="component(section.component)" :title="section.title" :data="section.data" />
    </template>
</template>
