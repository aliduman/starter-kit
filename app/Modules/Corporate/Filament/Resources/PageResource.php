<?php

namespace App\Modules\Corporate\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Modules\Corporate\Filament\Resources\PageResource\Pages;
use App\Modules\Corporate\Models\Page;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Page::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Corporate';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->maxLength(255),
            Forms\Components\Textarea::make('excerpt')->rows(3),
            Forms\Components\RichEditor::make('body')->columnSpanFull(),
            Forms\Components\Toggle::make('is_published'),
            Forms\Components\DateTimePicker::make('published_at'),
            Section::make('SEO')->schema([
                Forms\Components\TextInput::make('meta_title')->label('Meta başlık'),
                Forms\Components\Textarea::make('meta_description')->label('Meta açıklama')->rows(2),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
