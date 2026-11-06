<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\ActivityResource\Pages\ListActivities;
use App\Models\Activity;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

/** İşlem geçmişi: yalnızca okunur (App\Models\Concerns\LogsActivity yazar). */
class ActivityResource extends Resource
{
    use ChecksPermission;

    protected static string $permission = 'view-activity';

    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Site Yönetimi';

    protected static ?int $navigationSort = 90;

    protected static ?string $modelLabel = 'İşlem';

    protected static ?string $pluralModelLabel = 'İşlem Geçmişi';

    /** @var array<string, array<string, string>> Model sınıfı => alan adı => formdaki etiket */
    private static array $formLabels = [];

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Zaman')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('user.name')->label('Kullanıcı')->placeholder('Sistem / ziyaretçi'),
                TextColumn::make('event')
                    ->label('İşlem')
                    ->formatStateUsing(fn (string $state) => Activity::EVENTS[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'created' => 'success',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')->label('Kayıt türü')->formatStateUsing(fn (string $state) => static::subjectLabel($state)),
                TextColumn::make('subject_id')->label('Kayıt no'),
                TextColumn::make('changes')
                    ->label('Değişen alanlar')
                    ->state(fn (Activity $record) => implode(', ', array_map(
                        fn (string $field) => static::fieldLabel($field, $record->subject_type),
                        array_keys($record->changes ?? []),
                    )))
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('event')->label('İşlem')->options(Activity::EVENTS),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** Kayıt türü: paneldeki kaynağın adı; kaynağı olmayan model kendi `$activityLabel` adını verir. */
    public static function subjectLabel(string $type): string
    {
        if ($resource = Filament::getModelResource($type)) {
            return $resource::getModelLabel();
        }

        return property_exists($type, 'activityLabel') ? (new $type)->activityLabel : class_basename($type);
    }

    /**
     * Alan adı: kaydın panel formundaki etiket; formda olmayan alanda lang/{locale}/validation.php `attributes`
     * (formlardaki hata mesajlarıyla aynı adlar), o da yoksa sütun adı.
     */
    public static function fieldLabel(string $field, ?string $type = null): string
    {
        return ($type ? static::formLabels($type)[$field] ?? null : null)
            ?? (Lang::has("validation.attributes.{$field}") ? __("validation.attributes.{$field}") : $field);
    }

    /** @return array<string, string> */
    private static function formLabels(string $type): array
    {
        return static::$formLabels[$type] ??= rescue(function () use ($type): array {
            if (! $resource = Filament::getModelResource($type)) {
                return [];
            }

            // Form yalnızca etiketleri okumak için kurulur; bir Livewire sayfasına bağlı olması gerekir.
            $schema = $resource::form(Schema::make(new ListActivities)->model($type));

            return collect($schema->getFlatComponents(withActions: false, withHidden: true))
                ->filter(fn ($component) => $component instanceof Field && $component->hasCustomLabel() && is_string($component->getLabel()))
                ->mapWithKeys(fn (Field $field) => [$field->getName() => $field->getLabel()])
                ->all();
        }, []);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
