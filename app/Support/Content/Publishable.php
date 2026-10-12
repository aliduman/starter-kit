<?php

namespace App\Support\Content;

use Illuminate\Database\Eloquent\Builder;

/**
 * Herkese açık sitede görünürlük: yayın bayrağı ve (varsa) yayın tarihi. Model `$publishedFlag` (varsayılan
 * `is_published`) ve isteğe bağlı `$publishedAtColumn` ile kolonları bildirir. Yayın tarihi ileride olan kayıt
 * zamanı gelene kadar görünmez; o an response cache'i ScheduledCacheBoundaries temizler (modül provider'ı
 * publicationBoundaries() kolonlarını kaydeder).
 */
trait Publishable
{
    public function scopePublished(Builder $query): Builder
    {
        $query->where($this->qualifyColumn($this->publishedFlag()), true);

        if ($column = $this->publishedAtColumn()) {
            $query->where(fn (Builder $q) => $q
                ->whereNull($this->qualifyColumn($column))
                ->orWhere($this->qualifyColumn($column), '<=', now()));
        }

        return $query;
    }

    /** @return list<string> ScheduledCacheBoundaries::register() için zaman kolonları. */
    public static function publicationBoundaries(): array
    {
        return array_filter([(new static)->publishedAtColumn()]);
    }

    protected function publishedFlag(): string
    {
        return property_exists($this, 'publishedFlag') ? $this->publishedFlag : 'is_published';
    }

    protected function publishedAtColumn(): ?string
    {
        return property_exists($this, 'publishedAtColumn') ? $this->publishedAtColumn : null;
    }
}
