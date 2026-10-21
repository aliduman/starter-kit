<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\PersonResource\Pages\CreatePerson;
use App\Filament\Resources\PersonResource\Pages\EditPerson;
use App\Filament\Resources\PersonResource\Pages\ListPeople;
use App\Models\Person;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Kişiler: yazar, sunucu, ekip üyesi; hangi rolde göründüğünü ilgili modül belirler. */
class PersonResource extends Resource
{
    use ChecksPermission;

    protected static string $permission = 'manage-content';

    protected static ?string $model = Person::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 52;

    protected static ?string $modelLabel = 'Kişi';

    protected static ?string $pluralModelLabel = 'Kişiler';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->label('Ad Soyad')->required()->maxLength(255),
                TextInput::make('title')->label('Unvan'),
                SpatieMediaLibraryFileUpload::make('photo')
                    ->label('Fotoğraf')
                    ->collection('photo')
                    ->image()
                    ->avatar(),
                Textarea::make('bio')->label('Biyografi')->rows(5),
                TextInput::make('sort_order')->label('Sıra')->numeric()->default(0),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
            Section::make('Sosyal Medya')->schema([
                KeyValue::make('social_links')
                    ->label('Hesaplar')
                    ->keyLabel('Platform (instagram, youtube, x...)')
                    ->valueLabel('Adres'),
            ])->collapsed(),
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
                SpatieMediaLibraryImageColumn::make('photo')->label('Fotoğraf')->collection('photo')->circular(),
                TextColumn::make('name')->label('Ad')->searchable()->sortable(),
                TextColumn::make('title')->label('Unvan'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
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
            'index' => ListPeople::route('/'),
            'create' => CreatePerson::route('/create'),
            'edit' => EditPerson::route('/{record}/edit'),
        ];
    }
}
