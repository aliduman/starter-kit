import { ref } from 'vue';

/**
 * Ölçüm araçları (paylaşılan `analytics` prop'u: sağlayıcı => kimlik, bkz. App\Support\Seo\Seo::analytics).
 *
 * KVKK: araçların betikleri yalnızca ziyaretçi çerez bandında onay verdikten sonra yüklenir; reddedilirse sağlayıcıya
 * hiçbir istek gitmez. Karar tarayıcıda saklanır, footer'daki "Çerez tercihleri" ile değiştirilebilir.
 */

const STORAGE_KEY = 'cookie-consent';

function readConsent() {
    try {
        return localStorage.getItem(STORAGE_KEY);
    } catch {
        return null;
    }
}

/** 'granted' | 'denied' | null (henüz sorulmadı) */
export const consent = ref(readConsent());

/** Ziyaretçi footer'dan tercihlerini yeniden açtı. */
export const preferencesOpen = ref(false);

/**
 * GA4. Inertia gezinmeleri history.pushState ile olur; GA4'ün geliştirilmiş ölçümü ("tarayıcı geçmişi olaylarına
 * göre sayfa değişiklikleri", varsayılan açık) bunları sayfa görüntüleme olarak sayar.
 */
function loadGa4(id) {
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', id);

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
    document.head.appendChild(script);
}

/** Sağlayıcı => yükleyici. Yeni araç = bir yükleyici + sunucuda `analytics` girdisi. */
const loaders = { ga4: loadGa4 };

let started = false;

export function hasTrackers(trackers) {
    return Object.keys(trackers ?? {}).some((provider) => loaders[provider]);
}

export function startAnalytics(trackers) {
    if (started || consent.value !== 'granted' || !hasTrackers(trackers)) {
        return;
    }

    started = true;
    Object.entries(trackers).forEach(([provider, id]) => loaders[provider]?.(id));
}

/** Yüklenmiş araçların çerezleri (_ga, _ga_XXXX) site ve üst alan adları için silinir. */
function clearTrackerCookies() {
    const parts = window.location.hostname.split('.');
    const domains = ['', ...parts.map((_, i) => parts.slice(i).join('.')).filter((domain) => domain.includes('.'))];

    document.cookie.split(';')
        .map((cookie) => cookie.split('=')[0].trim())
        .filter((name) => name.startsWith('_ga'))
        .forEach((name) => domains.forEach((domain) => {
            document.cookie = `${name}=; Max-Age=0; path=/${domain ? `; domain=.${domain}` : ''}`;
        }));
}

export function setConsent(value, trackers) {
    try {
        localStorage.setItem(STORAGE_KEY, value);
    } catch {
        // Gizli pencere vb.: karar yalnızca bu sayfa için geçerli olur.
    }

    consent.value = value;
    preferencesOpen.value = false;

    if (value === 'granted') {
        startAnalytics(trackers);
    } else if (started) {
        // Yüklenmiş betik sayfadan çıkarılamaz; çerezler silinip sayfa onaysız yeniden açılır.
        clearTrackerCookies();
        window.location.reload();
    }
}
