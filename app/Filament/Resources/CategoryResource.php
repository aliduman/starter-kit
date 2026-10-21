<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Support\Taxonomy\CategoryTypes;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    use ChecksPermission;

    protected static string $permission = 'manage-content';

    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategoriler';

    /** Kategori kullanan modül yoksa (hiç tür kayıtlı değilse) menüde görünmez. */
    public static function shouldRegisterNavigation(): bool
    {
        return ! app(CategoryTypes::class)->isEmpty() && parent::shouldRegisterNavigation();
    }

    public static function form(Schema $schema): Schema
    {
        $types = app(CategoryTypes::class)->all();

        return $schema->components([
            Section::make()->schema([
                Select::make('type')
                    ->label('İçerik Türü')
                    ->options($types)
                    ->default(count($types) === 1 ? array_key_first($types) : null)
                    ->required()
                    ->live(),
                Select::make('parent_id')
                    ->label('Üst Kategori')
                    ->options(fn (Get $get, ?Category $record) => Category::query()
                        ->where('type', $get('type'))
                        ->when($record, fn ($query) => $query->whereKeyNot($record->id))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->placeholder('Yok'),
                TextInput::make('name')->label('Ad')->required()->maxLength(255),
                TextInput::make('slug')->label('Adres')->helperText('Boş bırakılırsa addan üretilir.'),
                Textarea::make('description')->label('Açıklama')->rows(3)->columnSpanFull(),
                TextInput::make('sort_order')->label('Sıra')->numeric()->default(0),
            ])->columns(2),
            Section::make('SEO')->schema([
                TextInput::make('meta_title')->label('Meta başlık'),
                Textarea::make('meta_description')->label('Meta açıklama')->rows(2),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ad')->searchable(),
                TextColumn::make('type')
                    ->label('Tür')
                    ->formatStateUsing(fn (string $state) => app(CategoryTypes::class)->all()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('parent.name')->label('Üst Kategori')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tür')->options(fn () => app(CategoryTypes::class)->all()),
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
