<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { consent, hasTrackers, preferencesOpen, setConsent } from '@/analytics';

defineProps({
    // Çerez politikası sayfası; verilmezse bant bağlantısız gösterilir.
    policyHref: { type: String, default: null },
});

const page = usePage();
// Karar tarayıcıda saklanır; sunucu (SSR) çıktısı bandı hiç basmaz, istemci açılınca karar verir.
const mounted = ref(false);
onMounted(() => {
    mounted.value = true;
});

const trackers = computed(() => page.props.analytics);
const visible = computed(() => mounted.value && hasTrackers(trackers.value) && (consent.value === null || preferencesOpen.value));
</script>

<template>
    <div v-if="visible" class="tw-preflight">
        <section
            role="region"
            aria-label="Çerez tercihleri"
            class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700 shadow-xl sm:flex sm:items-center sm:gap-4"
        >
            <p class="flex-1">
                Sitenin nasıl kullanıldığını anlamak için, onay verirseniz Google Analytics çerezlerini kullanıyoruz.
                <template v-if="policyHref">
                    Ayrıntılar: <Link :href="policyHref" class="font-semibold underline">Çerez Politikası</Link>.
                </template>
            </p>
            <div class="mt-3 flex shrink-0 gap-2 sm:mt-0">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 font-semibold hover:bg-gray-50"
                    @click="setConsent('denied', trackers)"
                >Reddet</button>
                <button
                    type="button"
                    class="rounded-lg border border-gray-900 bg-gray-900 px-4 py-2 font-semibold text-white hover:bg-gray-700"
                    @click="setConsent('granted', trackers)"
                >Kabul et</button>
            </div>
        </section>
    </div>
</template>
