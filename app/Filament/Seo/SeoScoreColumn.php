<?php

namespace App\Filament\Seo;

use App\Support\Seo\Analysis\SeoAnalyzable;
use App\Support\Seo\Analysis\SeoReport;
use App\Support\Seo\Analysis\SeoResult;
use Filament\Tables\Columns\TextColumn;

/** Listede SEO puanı (0–100); renk formdaki analizle aynı eşiklerden. Model SeoAnalyzable olmalıdır. */
class SeoScoreColumn
{
    public static function make(): TextColumn
    {
        return TextColumn::make('seo_score')
            ->label('SEO')
            ->state(fn (SeoAnalyzable $record) => $record->seoAnalysis()->score())
            ->badge()
            ->color(fn (int $state) => match (SeoReport::statusFor($state)) {
                SeoResult::GOOD => 'success',
                SeoResult::OK => 'warning',
                default => 'danger',
            });
    }
}
