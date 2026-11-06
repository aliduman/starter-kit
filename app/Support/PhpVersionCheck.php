<?php

namespace App\Support;

/**
 * Çalışan PHP'nin bilinen güvenlik açıklarına karşı yamalı olup olmadığı (config app.php_security_minimums).
 *
 * Yalnızca uyarır, kurulumu durdurmaz: paylaşımlı hostta PHP'yi host günceller. Dağıtımların kendi paketlerine taşıdığı
 * yamalar (sürüm numarası değişmeden) görülemez; ea-php gibi cPanel paketleri sürüm numarasını yükseltir.
 */
final class PhpVersionCheck
{
    /** /kurulum çıktısı için tek satırlık rapor. */
    public static function report(?string $version = null): string
    {
        $version ??= self::current();
        $required = self::requiredFor($version);

        return $required === null
            ? "== PHP {$version}: güvenlik sürümü tamam =="
            : "== UYARI: PHP {$version} bilinen güvenlik açıkları içeriyor; en az {$required} gerekli. "
                ."Hostingden PHP'yi güncellemesini isteyin (cPanel → MultiPHP Manager'da sürümü kontrol edin). ==";
    }

    /** Sürüm yamalıysa null, değilse gereken en düşük sürüm. */
    public static function requiredFor(string $version): ?string
    {
        /** @var array<string, string> $minimums */
        $minimums = config('app.php_security_minimums', []);
        $branch = implode('.', array_slice(explode('.', $version), 0, 2));

        if (isset($minimums[$branch])) {
            return version_compare($version, $minimums[$branch], '>=') ? null : $minimums[$branch];
        }

        // Listede olmayan dal: listedekilerin hepsinden yeniyse yamadan sonra çıkmıştır. Daha eskiyse
        // (desteği bitmiş) bir üst listelenen dalın minimumu gerekir.
        $newer = array_filter($minimums, fn (string $minimum, string $listed) => version_compare($listed, $branch, '>'),
            ARRAY_FILTER_USE_BOTH);

        if ($newer === []) {
            return null;
        }

        usort($newer, 'version_compare');

        return $newer[0];
    }

    // PHP_VERSION dağıtım ekleri taşıyabilir (ör. "8.4.26-1+ubuntu"); karşılaştırma sade numarayla yapılır.
    private static function current(): string
    {
        return PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;
    }
}
