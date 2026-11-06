<?php

namespace App\Modules\Ecommerce\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Modules\Ecommerce\Filament\Resources\ProductResource\Pages;
use App\Modules\Ecommerce\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'Ürün';

    protected static ?string $pluralModelLabel = 'Ürünler';

    protected static string|\UnitEnum|null $navigationGroup = 'E-Ticaret';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')->label('Ad')->required(),
            Forms\Components\TextInput::make('sku')->label('Stok kodu'),
            Forms\Components\TextInput::make('price')->label('Fiyat')->numeric()->required(),
            Forms\Components\TextInput::make('compare_price')->label('İndirimsiz fiyat')->numeric(),
            Forms\Components\TextInput::make('currency')->label('Para birimi')->default('TRY')->maxLength(3),
            Forms\Components\TextInput::make('stock')->label('Stok')->numeric()->default(0),
            Forms\Components\Textarea::make('description')->label('Açıklama')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Ad')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->label('Stok kodu')->toggleable(),
                Tables\Columns\TextColumn::make('price')->label('Fiyat')->money('TRY'),
                Tables\Columns\TextColumn::make('stock')->label('Stok')->numeric(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
