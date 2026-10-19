<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sitenin tek adresi APP_URL: başka şema ya da alan adıyla gelen istek (http, www, eski test alt alanı) aynı yola
 * kalıcı yönlenir; arama motoru aynı sayfanın kopyalarını görmez. config('seo.redirect_to_app_url') açıkken.
 */
class RedirectToAppUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $root = rtrim((string) config('app.url'), '/');

        if (! config('seo.redirect_to_app_url') || ! $request->isMethodSafe() || $root === ''
            || strcasecmp($request->getSchemeAndHttpHost(), $root) === 0) {
            return $next($request);
        }

        return redirect()->to($root.$request->getRequestUri(), 301);
    }
}
