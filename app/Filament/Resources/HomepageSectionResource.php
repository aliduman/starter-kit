<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\HomepageSectionResource\Pages\CreateHomepageSection;
use App\Filament\Resources\HomepageSectionResource\Pages\EditHomepageSection;
use App\Filament\Resources\HomepageSectionResource\Pages\ListHomepageSections;
use App\Models\HomepageSection;
use App\Support\Homepage\HomepageBlocks;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/** Ana sayfa bölümleri: bileşen seçimi, sıra ve bileşene özel ayarlar (App\Support\Homepage\HomepageBlocks). */
class HomepageSectionResource extends Resource
{
    use ChecksPermission;

    protected static string $permission = 'manage-site';

    protected static ?string $model = HomepageSection::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Site Yönetimi';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Ana Sayfa Bölümü';

    protected static ?string $pluralModelLabel = 'Ana Sayfa Bölümleri';

    public static function form(Schema $schema): Schema
    {
        $blocks = app(HomepageBlocks::class);

        return $schema->components([
            Section::make()->schema([
                Select::make('key')
                    ->label('Bileşen')
                    ->options($blocks->labels())
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->live(),
                TextInput::make('title')
                    ->label('Başlık')
                    ->helperText('Boş bırakılırsa bileşenin adı kullanılır.'),
                TextInput::make('sort_order')->label('Sıra')->numeric()->default(0),
                Toggle::make('is_active')->label('Ana sayfada göster')->default(true),
            ])->columns(2),
            Section::make('Bileşen Ayarları')
                ->schema([Group::make()->schema(fn (Get $get) => $blocks->form($get('key')))])
                ->visible(fn (Get $get) => $blocks->form($get('key')) !== []),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Bileşen')
                    ->formatStateUsing(fn (string $state) => app(HomepageBlocks::class)->labels()[$state] ?? $state),
                TextColumn::make('title')->label('Başlık')->placeholder('—'),
                ToggleColumn::make('is_active')->label('Görünür'),
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
            'index' => ListHomepageSections::route('/'),
            'create' => CreateHomepageSection::route('/create'),
            'edit' => EditHomepageSection::route('/{record}/edit'),
        ];
    }
}
