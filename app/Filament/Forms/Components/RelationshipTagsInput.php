<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TagsInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Çoktan çoğa bir ilişkiyi (etiketler gibi) ada göre yazılan kutu: Enter ya da virgül etiketi tamamlar, imleç bir
 * sonrakine geçer. Var olan adlar öneri olarak çıkar; olmayanlar kayıtta oluşturulur.
 *
 * TagsInput::make('tags') yerine: RelationshipTagsInput::make('tags')->relationship('tags', 'name')
 */
class RelationshipTagsInput extends TagsInput
{
    public function relationship(string $name, string $titleAttribute): static
    {
        $this->splitKeys([',']);

        $this->suggestions(fn (self $component): array => $component->relatedQuery($name)
            ->orderBy($titleAttribute)
            ->pluck($titleAttribute)
            ->all());

        $this->loadStateFromRelationshipsUsing(static function (self $component, Model $record) use ($name, $titleAttribute): void {
            $component->state($record->{$name}()->pluck($titleAttribute)->all());
        });

        // Ad listesi modele sütun olarak yazılmaz; ilişki kayıttan sonra eşitlenir.
        $this->dehydrated(false);

        $this->saveRelationshipsUsing(static function (self $component, Model $record, ?array $state) use ($name, $titleAttribute): void {
            $related = $record->{$name}()->getRelated();

            $ids = collect($state ?? [])
                ->map(fn ($value): string => trim((string) $value))
                ->filter()
                ->unique(fn (string $value): string => mb_strtolower($value))
                ->map(fn (string $value) => $related->newQuery()->firstOrCreate([$titleAttribute => $value])->getKey());

            $record->{$name}()->sync($ids->all());
        });

        return $this;
    }

    /** İlişkinin bağlı tablosu, bu kayda bağlı olsun olmasın (öneriler tüm adlardan gelir). */
    private function relatedQuery(string $name): Builder
    {
        $model = $this->getModelInstance() ?? app($this->getModel());

        /** @var BelongsToMany $relation */
        $relation = $model->{$name}();

        return $relation->getRelated()->newQuery();
    }
}
