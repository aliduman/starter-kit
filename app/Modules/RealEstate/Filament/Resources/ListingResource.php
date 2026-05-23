<?php

namespace App\Modules\RealEstate\Filament\Resources;

use App\Modules\RealEstate\Filament\Resources\ListingResource\Pages;
use App\Modules\RealEstate\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationGroup = 'Real Estate';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Select::make('type')
                ->options(['rent' => 'Kiralık', 'sale' => 'Satılık'])
                ->required(),
            Forms\Components\Select::make('status')
                ->options(['available' => 'Müsait', 'reserved' => 'Rezerve', 'sold' => 'Satıldı'])
                ->default('available'),
            Forms\Components\TextInput::make('price')->numeric()->required(),
            Forms\Components\TextInput::make('currency')->default('TRY'),
            Forms\Components\TextInput::make('city')->required(),
            Forms\Components\TextInput::make('district'),
            Forms\Components\TextInput::make('rooms')->numeric(),
            Forms\Components\TextInput::make('bathrooms')->numeric(),
            Forms\Components\TextInput::make('area_m2')->numeric(),
            Forms\Components\Textarea::make('address')->columnSpanFull(),
            Forms\Components\RichEditor::make('description')->columnSpanFull(),
            Forms\Components\Toggle::make('is_published'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('price')->money('TRY'),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
