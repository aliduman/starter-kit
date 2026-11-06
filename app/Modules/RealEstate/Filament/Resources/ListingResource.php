<?php

namespace App\Modules\RealEstate\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Modules\RealEstate\Filament\Resources\ListingResource\Pages;
use App\Modules\RealEstate\Models\Listing;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Listing::class;

    protected static ?string $modelLabel = 'İlan';

    protected static ?string $pluralModelLabel = 'İlanlar';

    protected static string|\UnitEnum|null $navigationGroup = 'Emlak';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('title')->label('Başlık')->required(),
            Forms\Components\Select::make('type')->label('Tür')
                ->options(['rent' => 'Kiralık', 'sale' => 'Satılık'])
                ->required(),
            Forms\Components\Select::make('status')->label('Durum')
                ->options(['available' => 'Müsait', 'reserved' => 'Rezerve', 'sold' => 'Satıldı'])
                ->default('available'),
            Forms\Components\TextInput::make('price')->label('Fiyat')->numeric()->required(),
            Forms\Components\TextInput::make('currency')->label('Para birimi')->default('TRY'),
            Forms\Components\TextInput::make('city')->label('Şehir')->required(),
            Forms\Components\TextInput::make('district')->label('İlçe'),
            Forms\Components\TextInput::make('rooms')->label('Oda sayısı')->numeric(),
            Forms\Components\TextInput::make('bathrooms')->label('Banyo sayısı')->numeric(),
            Forms\Components\TextInput::make('area_m2')->label('Alan (m²)')->numeric(),
            Forms\Components\Textarea::make('address')->label('Adres')->columnSpanFull(),
            Forms\Components\RichEditor::make('description')->label('Açıklama')->columnSpanFull(),
            Forms\Components\Toggle::make('is_published')->label('Yayında'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Başlık')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Tür')->badge(),
                Tables\Columns\TextColumn::make('city')->label('Şehir'),
                Tables\Columns\TextColumn::make('price')->label('Fiyat')->money('TRY'),
                Tables\Columns\IconColumn::make('is_published')->label('Yayında')->boolean(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }
}
