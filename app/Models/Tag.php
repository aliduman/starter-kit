<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\ResponseCache\Concerns\InvalidatesResponseCache;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/** Tüm içerik türlerinin paylaştığı etiket; içeriğe App\Models\Concerns\HasTags ile bağlanır. */
class Tag extends Model
{
    use HasSlug;
    use InvalidatesResponseCache;
    use LogsActivity;

    protected $fillable = ['name', 'slug'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
