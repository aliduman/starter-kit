<?php

namespace App\Modules\Corporate\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\Content\Publishable;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use App\Support\Seo\Analysis\HasSeoAnalysis;
use App\Support\Seo\Analysis\SeoAnalyzable;
use App\Support\Seo\Analysis\SeoSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Page extends Model implements HasMedia, SeoAnalyzable
{
    use HasFactory;
    use HasSeoAnalysis;
    use HasSlug;
    use InteractsWithMedia;
    use InvalidatesResponseCache;
    use LogsActivity;
    use Publishable;

    protected string $publishedAtColumn = 'published_at';

    protected $table = 'corporate_pages';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'focus_keyword',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getUrlAttribute(): string
    {
        return route('corporate.pages.show', $this->slug, absolute: false);
    }

    /** Sayfanın başlığı ve açıklaması (PageController::show). */
    public function seoSubject(): SeoSubject
    {
        return $this->makeSeoSubject(
            title: $this->meta_title ?: $this->title,
            slugSource: (string) $this->title,
            metaDescription: $this->meta_description,
            fallbackDescription: $this->excerpt ?: $this->body,
            html: (string) $this->body,
        );
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
}
