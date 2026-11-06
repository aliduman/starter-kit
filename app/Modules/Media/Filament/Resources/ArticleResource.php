<?php

namespace App\Modules\Media\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Forms\Components\RelationshipTagsInput;
use App\Filament\Seo\SeoScoreColumn;
use App\Filament\Seo\SeoSection;
use App\Models\Category;
use App\Modules\Media\Filament\Resources\ArticleResource\Pages;
use App\Modules\Media\Models\Article;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Article::class;

    protected static ?string $modelLabel = 'Yazı';

    protected static ?string $pluralModelLabel = 'Yazılar';

    protected static string|\UnitEnum|null $navigationGroup = 'Medya';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('title')->label('Başlık')->required()->live(onBlur: true),
            Forms\Components\Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name', fn ($query) => $query->where('type', Article::CATEGORY_TYPE))
                ->createOptionForm([Forms\Components\TextInput::make('name')->label('Ad')->required()])
                ->createOptionUsing(fn (array $data) => Category::create([...$data, 'type' => Article::CATEGORY_TYPE])->getKey()),
            RelationshipTagsInput::make('tags')
                ->label('Etiketler')
                ->relationship('tags', 'name')
                ->placeholder('Etiket yazıp Enter\'a basın'),
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
                Tables\Columns\TextColumn::make('title')->label('Başlık')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->label('Yayında')->boolean(),
                Tables\Columns\TextColumn::make('published_at')->label('Yayın tarihi')->dateTime(),
                SeoScoreColumn::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
