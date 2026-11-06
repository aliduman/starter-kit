<?php

namespace App\Modules\Corporate\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Seo\SeoScoreColumn;
use App\Filament\Seo\SeoSection;
use App\Modules\Corporate\Filament\Resources\PageResource\Pages;
use App\Modules\Corporate\Models\Page;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Page::class;

    protected static ?string $modelLabel = 'Sayfa';

    protected static ?string $pluralModelLabel = 'Sayfalar';

    protected static string|\UnitEnum|null $navigationGroup = 'Kurumsal';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('title')->label('Başlık')->required()->maxLength(255)->live(onBlur: true),
            Forms\Components\TextInput::make('slug')->label('Adres')->maxLength(255)->live(onBlur: true),
            Forms\Components\Textarea::make('excerpt')->label('Özet')->rows(3)->live(onBlur: true),
            Forms\Components\RichEditor::make('body')->label('Metin')->live(onBlur: true)->columnSpanFull(),
            Forms\Components\Toggle::make('is_published')->label('Yayında'),
            Forms\Components\DateTimePicker::make('published_at')->label('Yayın tarihi'),
            SeoSection::make(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Başlık')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Adres')->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->label('Yayında')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Güncellendi')->dateTime()->sortable(),
                SeoScoreColumn::make(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Yayında'),
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
