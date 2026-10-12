<?php

namespace App\Http\Middleware;

use App\Support\Routing\TrailingSlash;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * İşaretli rotaya sondaki "/" kuralına (TrailingSlash) uymadan gelen GET/HEAD isteğini doğru adrese kalıcı yönlendirir:
 * kural açıkken "/" eklenir, kapalıyken silinir. Tek adres = tek canonical.
 */
class EnforceTrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! $request->isMethodSafe() || $path === '/') {
            return $next($request);
        }

        $hasSlash = str_ends_with($path, '/');

        if (TrailingSlash::enabled() && ! $hasSlash) {
            return redirect()->to(TrailingSlash::append($request->getUri()), 301);
        }

        if (! TrailingSlash::enabled() && $hasSlash) {
            $query = $request->getQueryString();

            return redirect()->to(rtrim($request->getSchemeAndHttpHost().$request->getBaseUrl().$path, '/').($query ? '?'.$query : ''), 301);
        }

        return $next($request);
    }
}
