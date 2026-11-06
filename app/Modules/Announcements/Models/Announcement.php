<?php

namespace App\Modules\Announcements\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\Content\Publishable;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use App\Support\Seo\Analysis\HasSeoAnalysis;
use App\Support\Seo\Analysis\SeoAnalyzable;
use App\Support\Seo\Analysis\SeoSubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Announcement extends Model implements HasMedia, SeoAnalyzable
{
    use HasSeoAnalysis;
    use HasSlug;
    use InteractsWithMedia;
    use InvalidatesResponseCache;
    use LogsActivity;
    use Publishable;

    protected string $publishedAtColumn = 'published_at';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'body',
        'link',
        'button_text',
        'is_published',
        'published_at',
        'ends_at',
        'meta_title',
        'meta_description',
        'focus_keyword',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    /** Yayında ve süresi dolmamış duyurular (ana sayfa); arşiv published() kullanır. */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->published()->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    /** @return list<string> yayın ve bitiş anında response cache temizlenir */
    public static function publicationBoundaries(): array
    {
        return ['published_at', 'ends_at'];
    }

    public function getUrlAttribute(): string
    {
        return route('announcements.show', $this->slug, absolute: false);
    }

    /** Duyurunun başlığı, açıklaması ve görseli (AnnouncementController::show). */
    public function seoSubject(): SeoSubject
    {
        return $this->makeSeoSubject(
            title: $this->meta_title ?: $this->title,
            slugSource: (string) $this->title,
            metaDescription: $this->meta_description,
            fallbackDescription: $this->summary ?: $this->body,
            html: (string) $this->body,
            image: $this->seoMediaImage('cover'),
        );
    }

    public function toCard(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'url' => $this->url,
            'link' => $this->link,
            'button_text' => $this->button_text,
            'cover' => $this->getFirstMediaUrl('cover') ?: null,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
