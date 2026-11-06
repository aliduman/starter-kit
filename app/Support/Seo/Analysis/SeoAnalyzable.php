<?php

namespace App\Support\Seo\Analysis;

/**
 * SEO analizi yapılabilen model (bkz. HasSeoAnalysis). seoSubject() kaydedilmemiş, formdan doldurulmuş bir
 * kopyada da çalışmalıdır: panel canlı analizi böyle yapar.
 */
interface SeoAnalyzable
{
    public function seoSubject(): SeoSubject;

    public function seoAnalysis(): SeoReport;
}
