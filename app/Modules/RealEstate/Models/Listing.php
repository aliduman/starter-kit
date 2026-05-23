<?php

namespace App\Modules\RealEstate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Listing extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;

    protected $table = 'realestate_listings';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'status',
        'price',
        'currency',
        'rooms',
        'bathrooms',
        'area_m2',
        'city',
        'district',
        'address',
        'latitude',
        'longitude',
        'description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'area_m2' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'rooms' => 'integer',
            'bathrooms' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
}
