import { route as ziggyRoute } from 'ziggy-js';

/**
 * Adlı rotanın göreli adresi (Ziggy, `@routes`). Sunucu `trailingSlash` ile işaretlediği rotaları "/" ile bitirir
 * (App\Support\Routing\TrailingSlash); burada da aynısı yapılır ki bağlantılar yönlendirmeye uğramasın.
 * Örnek: route('media.articles.show', 'merhaba') → "/haber/merhaba" (kural açıkken "/haber/merhaba/")
 */
export function route(name, params = {}) {
    const url = ziggyRoute(name, params, false);

    return routes()[name]?.middleware?.includes('trailingSlash') ? withTrailingSlash(url) : url;
}

// `@routes` küresel bir `const Ziggy` tanımlar; window özelliği değildir, ziggy-js gibi doğrudan okunur.
function routes() {
    /* global Ziggy */
    return (typeof Ziggy !== 'undefined' ? Ziggy : globalThis.Ziggy)?.routes ?? {};
}

function withTrailingSlash(url) {
    const end = url.search(/[?#]/);
    const path = end === -1 ? url : url.slice(0, end);

    return path.endsWith('/') ? url : `${path}/${end === -1 ? '' : url.slice(end)}`;
}
