<?php

namespace App\Modules\Media\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Models\Category;
use App\Modules\Media\Filament\Resources\ArticleResource\Pages;
use App\Modules\Media\Models\Article;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Article::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Media';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name', fn ($query) => $query->where('type', Article::CATEGORY_TYPE))
                ->createOptionForm([Forms\Components\TextInput::make('name')->required()])
                ->createOptionUsing(fn (array $data) => Category::create([...$data, 'type' => Article::CATEGORY_TYPE])->getKey()),
            Forms\Components\Select::make('tags')
                ->label('Etiketler')
                ->relationship('tags', 'name')
                ->multiple()
                ->preload()
                ->createOptionForm([Forms\Components\TextInput::make('name')->required()]),
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
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('published_at')->dateTime(),
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
