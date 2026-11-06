<?php

namespace App\Support\Seo\Analysis;

/** `seo.analysis.checks` listesindeki denetimleri çalıştırır; site denetim ekleyip çıkarabilir. */
class SeoAnalyzer
{
    public function analyze(SeoSubject $subject): SeoReport
    {
        $results = [];
        $missed = 0;

        foreach (config('seo.analysis.checks', []) as $check) {
            /** @var SeoCheck $check */
            $check = app($check);

            if (! $subject->keyword && $check instanceof RequiresKeyword) {
                $missed += (int) $check->applies($subject);

                continue;
            }

            if ($result = $check->run($subject)) {
                $results[] = $result;
            }
        }

        return new SeoReport($results, $missed);
    }
}
