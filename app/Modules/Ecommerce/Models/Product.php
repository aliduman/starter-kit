<?php

namespace App\Modules\Ecommerce\Models;

use App\Support\Content\Publishable;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    use InvalidatesResponseCache;
    use Publishable;

    protected string $publishedFlag = 'is_active';

    protected $table = 'ecommerce_products';

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'compare_price',
        'currency',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'is_active' => 'boolean',
            'stock' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
