<?php

namespace App\Support\Seo\Analysis;

/**
 * Türkçeye uygun metin karşılaştırması. Anahtar kelime büyük/küçük harf, Türkçe harf (ı/i, ş/s...) ve kesme
 * işaretinden bağımsız eşleşir; Türkçe sondan eklemeli olduğu için her kelimenin başı eşleşmesi yeterlidir
 * ("namaz borcu" → "namazın borcunu", "Kur'an fazileti" → "Kuran'ın faziletleri").
 */
final class SeoText
{
    private const FOLD = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u', 'â' => 'a', 'î' => 'i', 'û' => 'u'];

    /** mb_strtolower 'İ'yi "i̇", 'I'yı "i" yapar; Türkçede doğrusu "i" ve "ı"dır. */
    public static function lower(string $text): string
    {
        return mb_strtolower(strtr($text, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
    }

    /** HTML'den düz metin: etiketler boşlukla ayrılır (<p>a</p><p>b</p> "ab" olmaz). */
    public static function plain(?string $html): string
    {
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', (string) $html)), ENT_QUOTES | ENT_HTML5);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** Karşılaştırma biçimi: küçük harf, sade harfler, kesme işaretsiz, tek boşluk. */
    public static function normalize(string $text): string
    {
        $text = preg_replace("/['’ʼ`]/u", '', strtr(self::lower($text), self::FOLD));

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public static function occurrences(string $text, string $keyword): int
    {
        $words = array_filter(explode(' ', self::normalize($keyword)));

        if ($words === []) {
            return 0;
        }

        $pattern = '/(?<![\p{L}\p{N}])'.implode('\p{L}*\s+', array_map(fn (string $word) => preg_quote($word, '/'), $words)).'/u';

        return preg_match_all($pattern, self::normalize($text));
    }

    public static function contains(?string $text, string $keyword): bool
    {
        return self::occurrences((string) $text, $keyword) > 0;
    }

    /** @return list<string> */
    public static function words(string $text): array
    {
        preg_match_all("/[\p{L}\p{N}]+(?:['’ʼ][\p{L}]+)*/u", $text, $matches);

        return $matches[0];
    }
}
