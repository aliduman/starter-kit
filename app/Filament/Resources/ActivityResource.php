<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\ActivityResource\Pages\ListActivities;
use App\Models\Activity;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                TextColumn::make('subject_type')->label('Kayıt türü')->formatStateUsing(fn (string $state) => class_basename($state)),
                TextColumn::make('subject_id')->label('Kayıt no'),
                TextColumn::make('changes')
                    ->label('Değişen alanlar')
                    ->state(fn (Activity $record) => implode(', ', array_keys($record->changes ?? [])))
                    ->limit(60)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('event')->label('İşlem')->options(Activity::EVENTS),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
