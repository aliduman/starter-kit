<?php

namespace App\Filament\Seo;

use App\Support\Seo\Analysis\SeoAnalyzable;
use App\Support\Seo\Analysis\SeoAnalyzer;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Seo;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component as Livewire;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Formların SEO bölümü: odak anahtar kelime, meta alanları, Google önizlemesi ve canlı analiz (Yoast benzeri).
 * Model SeoAnalyzable olmalıdır. Analiz formdaki kaydedilmemiş değerlerle yapılır; analizi etkileyen alanlar
 * (başlık, metin, kapak...) live() olursa sonuç yazarken güncellenir, değilse "Analizi yenile" ile.
 */
class SeoSection
{
    /**
     * @param  string|list<string>|null  $imageField  Paylaşım görselinin yükleme alanı (SpatieMediaLibraryFileUpload);
     *                                                birden çoksa öncelik sırasıyla (ör. ['share', 'cover']). Alan adı
     *                                                medya koleksiyonunun adıdır.
     * @param  list<Component>  $fields  Siteye özgü ek SEO alanları (ör. canonical adres).
     */
    public static function make(string|array|null $imageField = null, array $fields = []): Section
    {
        return Section::make('SEO')
            ->description('Bu içeriğin Google\'da nasıl görüneceği ve iyileştirme önerileri.')
            ->schema([
                TextInput::make('focus_keyword')
                    ->label('Odak Anahtar Kelime')
                    ->maxLength(100)
                    ->live(debounce: 600)
                    ->helperText('İçeriğin hangi aramada çıkmasını istiyorsunuz? Arama yapanın yazacağı 1–4 kelimelik ifade.'),
                TextInput::make('meta_title')
                    ->label('Meta Başlık')
                    ->maxLength(255)
                    ->live(debounce: 600)
                    ->helperText('Boşsa başlık kullanılır.'),
                Textarea::make('meta_description')
                    ->label('Meta Açıklama')
                    ->rows(2)
                    ->maxLength(255)
                    ->live(debounce: 600)
                    ->helperText('Boşsa özet ya da metnin başı kullanılır. 120–160 karakter önerilir.')
                    ->columnSpanFull(),
                ...$fields,
                View::make('filament.seo.analysis')
                    ->viewData(fn (Get $get, Livewire $livewire, string $model, ?Model $record) => self::analyze($get, $livewire, $model, $record, Arr::wrap($imageField)))
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    /**
     * @param  class-string<Model&SeoAnalyzable>  $modelClass
     * @param  list<string>  $imageFields
     */
    private static function analyze(Get $get, Livewire $livewire, string $modelClass, ?Model $record, array $imageFields): array
    {
        try {
            // Kaydı değiştirmeden formdaki değerlerle bir kopya; yalnızca formda olan alanlar üzerine yazılır.
            $model = $record ? clone $record : new $modelClass;
            $keys = array_intersect($model->getFillable(), array_keys((array) ($livewire->data ?? [])));
            $model->forceFill(array_combine($keys, array_map(fn (string $key) => $get($key), $keys)));

            $files = array_map(fn (string $field) => Arr::first(Arr::wrap($get($field))), array_combine($imageFields, $imageFields));
            $removed = array_keys(array_filter($files, fn ($file) => ! $file));

            if ($removed !== []) {
                // Formda kaldırılan görseller analizde de yok sayılır.
                $model->setRelation('media', $model->media->reject(fn ($media) => in_array($media->collection_name, $removed, true)));
            }

            $file = Arr::first(array_filter($files));
            $subject = $model->seoSubject();

            if ($file instanceof TemporaryUploadedFile) {
                $subject = $subject->withImage(SeoSubject::imageAt($file->getRealPath()));
            }

            $seo = app(Seo::class);

            return [
                'report' => app(SeoAnalyzer::class)->analyze($subject),
                'preview' => [
                    'title' => $subject->title,
                    'url' => self::displayUrl($subject->url),
                    'description' => $seo->description($subject->description),
                ],
            ];
        } catch (Throwable $e) {
            // Analiz hatası formu kullanılmaz hale getirmemeli.
            report($e);

            return ['report' => null, 'preview' => null];
        }
    }

    /** Google'ın gösterdiği biçim: "site.com › yol › sayfa". */
    private static function displayUrl(string $url): string
    {
        $parts = parse_url($url);
        $segments = array_filter(explode('/', $parts['path'] ?? ''));

        return implode(' › ', [preg_replace('/^www\./', '', $parts['host'] ?? ''), ...$segments]);
    }
}
