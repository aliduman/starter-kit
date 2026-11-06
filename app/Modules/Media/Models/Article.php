<?php

namespace App\Modules\Media\Models;

use App\Models\Category;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use App\Support\Content\Publishable;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use App\Support\Seo\Analysis\HasSeoAnalysis;
use App\Support\Seo\Analysis\SeoAnalyzable;
use App\Support\Seo\Analysis\SeoSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model implements HasMedia, SeoAnalyzable
{
    use HasFactory;
    use HasSeoAnalysis;
    use HasSlug;
    use HasTags;
    use InteractsWithMedia;
    use InvalidatesResponseCache;
    use LogsActivity;
    use Publishable;

    /** Ortak kategori tablosundaki türü (App\Support\Taxonomy\CategoryTypes). */
    public const CATEGORY_TYPE = 'media';

    protected string $publishedAtColumn = 'published_at';

    protected $table = 'media_articles';

    protected $fillable = [
        'author_id',
        'category_id',
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getUrlAttribute(): string
    {
        return route('media.articles.show', $this->slug, absolute: false);
    }

    /** Başlık ve açıklama yayın sayfasındakiyle aynı kurallardan gelir (ArticleController::show). */
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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
