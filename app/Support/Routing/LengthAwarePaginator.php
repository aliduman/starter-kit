<?php

namespace App\Support\Routing;

use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;

/**
 * Laravel sayfalama yolunun sonundaki "/" işaretini siler; site adresleri "/" ile biter (TrailingSlash), sayfa
 * bağlantıları da öyle kalsın (/makaleler/?page=2). Container'a bağlıdır: Eloquent paginate() bunu üretir.
 */
class LengthAwarePaginator extends BaseLengthAwarePaginator
{
    /** İstek özniteliği: sayfa sayfalı liste gösteriyor; canonical ?page'i yalnızca o zaman taşır (Seo). */
    public const REQUEST_ATTRIBUTE = 'paginated';

    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);

        request()->attributes->set(self::REQUEST_ATTRIBUTE, true);

        if (is_string($options['path'] ?? null) && UrlGenerator::endsWithSlash($options['path'])) {
            $this->path = $options['path'];
        }
    }
}
