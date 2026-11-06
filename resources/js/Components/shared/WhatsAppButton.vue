<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from '~shared/Icon.vue';

// Site ayarındaki WhatsApp numarası doluysa her sayfada sağ altta; oynatıcı açıkken onun üstüne çıkar.
defineProps({ raised: { type: Boolean, default: false } });

const page = usePage();
const number = computed(() => (page.props.settings?.contact_whatsapp || '').replace(/\D/g, ''));
</script>

<template>
    <a
        v-if="number"
        :href="`https://wa.me/${number}`"
        target="_blank"
        rel="noopener"
        aria-label="WhatsApp'tan yazın"
        class="fixed right-4 z-40 grid size-14 place-items-center rounded-full bg-[#1f8f5a] text-white shadow-lg transition-colors hover:bg-[#18774a]"
        :class="raised ? 'bottom-24' : 'bottom-4'"
    >
        <Icon name="whatsapp" class="size-7" />
    </a>
</template>
