<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Formlar için bal küpü (rota middleware'i `honeypot`; yanına `throttle` eklenir). İnsanların görmediği `website`
 * alanı dolu gelirse istek kaydedilmeden başarılı gibi döner, bot farkı anlamaz. Vue tarafı: ~shared/HoneypotField.vue.
 */
class RejectHoneypot
{
    public const FIELD = 'website';

    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->input(self::FIELD))) {
            return back()->with('success', 'Gönderiminiz alındı.');
        }

        return $next($request);
    }
}
