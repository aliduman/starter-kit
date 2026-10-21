import { defineStore } from 'pinia';

/**
 * Sitenin tek ses çalarının durumu. Çalar kalıcı yerleşimdedir (AppLayout → AudioBar): sayfa değişse de çalmaya devam
 * eder. Parça bir kayıt (bölüm dosyası) ya da canlı yayın akışı olabilir: { src, title, subtitle?, live? }.
 * Canlı yayında bağlantı koparsa artan aralıklarla yeniden bağlanır; duraklatılıp sürdürülen canlı yayın en güncel
 * noktadan başlar. Ses öğesi reaktif değildir (modül değişkeni).
 */
const RETRY_DELAYS = [1000, 3000, 8000, 15000];
const FAILED = 'Ses yüklenemedi. Bağlantınızı kontrol edip tekrar deneyin.';

let audio = null;
let retryTimer = null;

export const usePlayerStore = defineStore('player', {
    state: () => ({
        track: null,
        // idle | loading | playing | paused | error
        status: 'idle',
        error: null,
        volume: 0.8,
        retries: 0,
    }),

    getters: {
        playing: (state) => state.status === 'playing',
        busy: (state) => state.status === 'loading',
    },

    actions: {
        element() {
            if (!audio) {
                audio = new Audio();
                audio.preload = 'none';
                audio.volume = this.volume;
                audio.addEventListener('playing', () => {
                    this.status = 'playing';
                    this.error = null;
                    this.retries = 0;
                });
                audio.addEventListener('waiting', () => {
                    if (this.status === 'playing') this.status = 'loading';
                });
                audio.addEventListener('pause', () => {
                    if (this.status === 'playing') this.status = 'paused';
                });
                audio.addEventListener('ended', () => {
                    // Canlı yayının "bitmesi" kopmadır.
                    if (this.track?.live) this.failed();
                    else this.status = 'paused';
                });
                audio.addEventListener('error', () => this.failed());
            }

            return audio;
        },

        /** Parçayı çalar; aynı parça çalıyorsa duraklatır. */
        toggle(track) {
            if (this.isCurrent(track) && this.playing) {
                this.pause();
            } else if (this.isCurrent(track)) {
                this.resume();
            } else {
                this.play(track);
            }
        },

        isCurrent(track) {
            return Boolean(track && this.track && this.track.src === track.src);
        },

        play(track) {
            clearTimeout(retryTimer);
            this.track = { live: false, subtitle: null, ...track };
            this.retries = 0;
            this.load();
        },

        load() {
            const element = this.element();
            this.status = 'loading';
            this.error = null;
            element.src = this.track.src;
            element.load();
            element.play().catch((error) => {
                // Tarayıcı otomatik oynatmayı engellediyse kullanıcı tekrar basar; diğer hatalar 'error' olayıyla gelir.
                if (error?.name === 'NotAllowedError') this.status = 'paused';
            });
        },

        pause() {
            clearTimeout(retryTimer);
            this.element().pause();
            this.status = 'paused';
        },

        resume() {
            if (!this.track) return;
            if (this.track.live) {
                this.load();
            } else {
                this.status = 'loading';
                this.element().play().catch(() => this.failed());
            }
        },

        retry() {
            this.retries = 0;
            if (this.track) this.load();
        },

        failed() {
            if (!this.track || this.status === 'paused') return;

            if (this.track.live && this.retries < RETRY_DELAYS.length) {
                this.status = 'loading';
                clearTimeout(retryTimer);
                retryTimer = setTimeout(() => this.load(), RETRY_DELAYS[this.retries++]);
                return;
            }

            this.status = 'error';
            this.error = FAILED;
        },

        setVolume(value) {
            this.volume = Math.min(1, Math.max(0, Number(value)));
            this.element().volume = this.volume;
        },

        stop() {
            clearTimeout(retryTimer);
            if (audio) {
                audio.pause();
                audio.removeAttribute('src');
                audio.load();
            }
            this.$reset();
        },
    },
});
