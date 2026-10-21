<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\EditMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use App\Models\MenuItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuItemResource extends Resource
{
    use ChecksPermission;

    protected static string $permission = 'manage-site';

    protected static ?string $model = MenuItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected static string|\UnitEnum|null $navigationGroup = 'Site Yönetimi';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Menü Öğesi';

    protected static ?string $pluralModelLabel = 'Menüler';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('location')
                    ->label('Menü Konumu')
                    ->options(MenuItem::locations())
                    ->required()
                    ->live(),
                Select::make('parent_id')
                    ->label('Üst Menü Öğesi')
                    ->options(fn (Get $get, ?MenuItem $record) => MenuItem::query()
                        ->whereNull('parent_id')
                        ->when($get('location'), fn (Builder $query, string $location) => $query->where('location', $location))
                        ->when($record, fn (Builder $query) => $query->whereKeyNot($record->id))
                        ->orderBy('sort_order')
                        ->pluck('title', 'id'))
                    ->placeholder('Yok'),
                TextInput::make('title')->label('Başlık')->required()->maxLength(255),
                TextInput::make('url')
                    ->label('Bağlantı')
                    ->helperText('Ör. /, /iletisim veya tam adres. Alt menüsü olan başlıkta boş bırakılabilir.'),
                Select::make('target')->label('Açılış')->options(MenuItem::TARGETS)->default('_self')->required(),
                TextInput::make('sort_order')->label('Sıra')->numeric()->default(0),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Başlık')->searchable(),
                TextColumn::make('location')
                    ->label('Konum')
                    ->formatStateUsing(fn (string $state) => MenuItem::locations()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('parent.title')->label('Üst Öğe')->placeholder('—'),
                TextColumn::make('url')->label('Bağlantı')->limit(40),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->filters([
                SelectFilter::make('location')->label('Menü Konumu')->options(MenuItem::locations()),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
