<?php

namespace App\Support\Seo\Analysis;

/**
 * Odak anahtar kelimeye bakan denetim. Anahtar kelime girilmemişse çalışmaz ama içeriğe uyuyorsa (applies) sıfır
 * puan sayılır: anahtar kelimesiz içerik yeşil olamaz.
 */
interface RequiresKeyword
{
    /** Anahtar kelimeden bağımsız olarak bu içerikte uygulanabilir mi (ör. ilk paragraf var mı). */
    public function applies(SeoSubject $subject): bool;
}
