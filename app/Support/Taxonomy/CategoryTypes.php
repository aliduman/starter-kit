<?php

namespace App\Support\Taxonomy;

/**
 * Kategori kullanan içerik türleri (tür anahtarı => paneldeki ad). Kategoriler tek tabloda tutulur (`categories.type`);
 * modüller kendi türünü provider'ında kaydeder, panel yalnızca kayıtlı türleri gösterir.
 */
final class CategoryTypes
{
    /** @var array<string, string> */
    private array $types = [];

    public function register(string $type, string $label): static
    {
        $this->types[$type] = $label;

        return $this;
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return $this->types;
    }

    public function isEmpty(): bool
    {
        return $this->types === [];
    }
}
