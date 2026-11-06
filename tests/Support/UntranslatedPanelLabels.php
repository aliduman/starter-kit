<?php

namespace Tests\Support;

use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Filament\Support\get_model_label;

/**
 * Paneldeki İngilizce kalmış etiketleri bulur. Etiketi verilmemiş alan, sütun, filtre ve işlemlere Filament kod
 * adından İngilizce bir etiket üretir ('meta_title' → "Meta title"); adı verilmemiş kaynak da sınıf adını kullanır
 * ("Media article"). Her kaynağın liste ve ekleme sayfasıyla paneldeki diğer sayfalar açılıp taranır.
 */
final class UntranslatedPanelLabels
{
    /** @return list<string> "Kaynak/Sayfa: kod adı → üretilen etiket" */
    public static function find(User $admin, string $panel = 'admin'): array
    {
        $found = [];

        foreach (Filament::getPanel($panel)->getResources() as $resource) {
            $context = class_basename($resource);

            if ($resource::getModelLabel() === get_model_label($resource::getModel())) {
                $found[] = "{$context}: \$modelLabel verilmemiş → {$resource::getModelLabel()}";
            }

            if ($resource::hasTitleCaseModelLabel()) {
                $found[] = "{$context}: menü adı her kelimesi büyütülerek gösteriliyor → {$resource::getTitleCasePluralModelLabel()}";
            }

            foreach (['index', 'create'] as $page) {
                if ($resource::hasPage($page)) {
                    $found = [...$found, ...self::inPage($admin, $context, $resource::getPages()[$page]->getPage())];
                }
            }
        }

        foreach (Filament::getPanel($panel)->getPages() as $page) {
            $found = [...$found, ...self::inPage($admin, class_basename($page), $page)];
        }

        return array_values(array_unique($found));
    }

    /** @return list<string> */
    private static function inPage(User $admin, string $context, string $page): array
    {
        $livewire = Livewire::actingAs($admin)->test($page)->instance();

        $items = self::flatten($livewire->getCachedHeaderActions());

        foreach ($livewire->getCachedSchemas() as $schema) {
            $items = [...$items, ...$schema->getFlatComponents(withHidden: true)];
        }

        if ($livewire instanceof HasTable) {
            $table = $livewire->getTable();
            $items = [...$items, ...$table->getColumns(), ...array_values($table->getFilters()), ...$table->getFlatActions()];
        }

        $found = [];

        foreach ($items as $item) {
            if ($label = self::generatedLabel($item)) {
                $found[] = "{$context}: {$item->getName()} → {$label}";
            }
        }

        return $found;
    }

    /** Etiket kod adından üretilmişse o etiket, elle verilmişse ya da etiket gösterilmiyorsa null. */
    private static function generatedLabel(object $item): ?string
    {
        if (! method_exists($item, 'getName') || ! method_exists($item, 'getLabel') || blank($name = $item->getName())) {
            return null;
        }

        // Elle verilmiş etiket kod adıyla aynı olabilir ("Logo", "Program"); yalnızca hiç verilmemiş etiket sayılır.
        $hasOwnLabel = property_exists($item, 'label') && (fn () => $this->label !== null)->call($item);

        if ($hasOwnLabel || (method_exists($item, 'isLabelHidden') && $item->isLabelHidden())) {
            return null;
        }

        $label = $item->getLabel();

        if (! is_string($label)) {
            return null;
        }

        // Filament'in alan, sütun (ilişki.sütun), filtre ve işlem için kullandığı biçimler.
        $generated = collect([Str::afterLast($name, '.'), Str::before($name, '.'), Str::afterLast(Str::beforeLast($name, '.'), '.')])
            ->map(fn (string $part) => (string) str($part)->kebab()->replace(['-', '_'], ' ')->ucfirst());

        return $generated->contains($label) ? $label : null;
    }

    /** @return list<object> */
    private static function flatten(array $actions): array
    {
        return collect($actions)
            ->flatMap(fn ($action) => $action instanceof ActionGroup ? $action->getFlatActions() : [$action])
            ->values()
            ->all();
    }
}
