<script setup>
import { usePlayerStore } from '@/Stores/player';

// Sayfa altındaki kalıcı çalar çubuğu; bir parça çalınmaya başlayınca görünür (Stores/player.js).
const player = usePlayerStore();
</script>

<template>
    <div v-if="player.track" class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3">
            <button
                type="button"
                class="grid size-11 flex-none place-items-center rounded-full bg-brand-600 text-white disabled:opacity-60"
                :aria-label="player.playing ? 'Duraklat' : 'Oynat'"
                @click="player.playing ? player.pause() : player.resume()"
            >
                <svg v-if="player.busy" class="size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-dasharray="40 20" />
                </svg>
                <svg v-else-if="player.playing" class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M7 5h4v14H7zM13 5h4v14h-4z" />
                </svg>
                <svg v-else class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </button>

            <div class="min-w-0 flex-1" aria-live="polite">
                <p class="truncate text-sm font-semibold">
                    {{ player.track.title }}
                    <span v-if="player.track.live" class="ml-2 text-xs font-bold text-rose-600">● CANLI</span>
                </p>
                <p v-if="player.error" class="text-xs text-rose-600">
                    {{ player.error }}
                    <button type="button" class="ml-1 font-semibold underline" @click="player.retry()">Tekrar dene</button>
                </p>
                <p v-else-if="player.track.subtitle" class="truncate text-xs text-slate-500">{{ player.track.subtitle }}</p>
            </div>

            <label class="hidden items-center gap-2 text-xs text-slate-500 sm:flex">
                Ses
                <input
                    type="range"
                    min="0"
                    max="1"
                    step="0.05"
                    :value="player.volume"
                    class="w-24"
                    @input="player.setVolume($event.target.value)"
                >
            </label>

            <button type="button" class="text-slate-400 hover:text-slate-700" aria-label="Çaları kapat" @click="player.stop()">✕</button>
        </div>
    </div>
</template>
