<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Sitede adı geçen kişi: yazar, sunucu, programcı, ekip üyesi. Hangi rolde göründüğünü ilişki kuran modül belirler
 * (ör. yayın modülünde program sunucusu); kişinin herkese açık sayfası da o modülündür.
 */
class Person extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;
    use InvalidatesResponseCache;
    use LogsActivity;

    protected $table = 'people';

    protected $fillable = [
        'name',
        'slug',
        'title',
        'bio',
        'social_links',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function photoUrl(): ?string
    {
        return $this->getFirstMediaUrl('photo') ?: null;
    }
}
