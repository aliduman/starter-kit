<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import NavBar from '~shared/NavBar.vue';
import CookieConsent from '~shared/CookieConsent.vue';
import AudioBar from '~shared/AudioBar.vue';
import { usePlayerStore } from '@/Stores/player';
import { hasTrackers, preferencesOpen } from '@/analytics';

const page = usePage();
const flash = computed(() => page.props.flash || {});
const player = usePlayerStore();

const openCookiePreferences = () => {
    preferencesOpen.value = true;
};
</script>

<template>
    <div class="min-h-screen flex flex-col" :class="{ 'pb-20': player.track }">
        <NavBar />

        <main class="flex-1 mx-auto w-full max-w-7xl px-4 py-10">
            <div v-if="flash.success" class="mb-6 rounded-md bg-emerald-50 px-4 py-3 text-emerald-700">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="mb-6 rounded-md bg-rose-50 px-4 py-3 text-rose-700">
                {{ flash.error }}
            </div>

            <slot />
        </main>

        <footer class="border-t border-slate-200 bg-white py-6 text-sm text-slate-500">
            <div class="mx-auto max-w-7xl px-4 flex justify-between">
                <span>© {{ new Date().getFullYear() }} {{ $page.props.app.name }}</span>
                <div class="flex gap-4">
                    <button v-if="hasTrackers(page.props.analytics)" class="hover:text-slate-900" @click="openCookiePreferences">
                        Çerez tercihleri
                    </button>
                    <Link href="/admin" class="hover:text-slate-900">Admin</Link>
                </div>
            </div>
        </footer>

        <AudioBar />
        <CookieConsent />
    </div>
</template>
