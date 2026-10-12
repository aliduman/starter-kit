<?php

namespace App\Http\Middleware;

use App\Support\Routing\LegacyRedirects;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Eski sitenin kayıtlı adres biçimlerini (LegacyRedirects) yeni adreslerine kalıcı yönlendirir; rota eşleşmeden önce. */
class RedirectLegacyUrls
{
    public function __construct(private readonly LegacyRedirects $redirects) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() && $url = $this->redirects->resolve($request)) {
            return redirect()->to($url, 301);
        }

        return $next($request);
    }
}
