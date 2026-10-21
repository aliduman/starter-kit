<?php

namespace App\Support\Settings;

/**
 * Panelde düzenlenen site ayarlarının tanımları. Çekirdek (AppServiceProvider) ve modüller alanlarını provider'larında
 * kaydeder; Site Ayarları sayfası (App\Filament\Pages\ManageSettings) formu bu listeden kurar. Değerler `settings`
 * tablosunda anahtar => değer olarak saklanır (App\Models\Setting).
 *
 * Alan: label, group (grup anahtarı), type (text|textarea|url|email|toggle|select), options (select için), help,
 * default, public (true ise istemciye `settings` prop'uyla gider; erişim anahtarı gibi gizli değerler gitmez).
 */
final class SettingFields
{
    /** @var array<string, string> */
    private array $groups = [];

    /** @var array<string, array<string, mixed>> */
    private array $fields = [];

    public function group(string $key, string $label): static
    {
        $this->groups[$key] = $label;

        return $this;
    }

    /** @param  array<string, array<string, mixed>>  $fields  anahtar => alan tanımı */
    public function add(array $fields): static
    {
        foreach ($fields as $key => $field) {
            $this->fields[$key] = ['type' => 'text', 'group' => 'general', 'public' => false, ...$field];
        }

        return $this;
    }

    /** @return array<string, string> */
    public function groups(): array
    {
        return $this->groups;
    }

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return $this->fields;
    }

    /** @return array<string, mixed>|null */
    public function get(string $key): ?array
    {
        return $this->fields[$key] ?? null;
    }

    /** @return list<string> */
    public function publicKeys(): array
    {
        return array_keys(array_filter($this->fields, fn (array $field) => $field['public']));
    }
}
