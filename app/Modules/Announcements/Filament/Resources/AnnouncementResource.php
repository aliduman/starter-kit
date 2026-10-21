<?php

namespace App\Modules\Announcements\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Modules\Announcements\Filament\Resources\AnnouncementResource\Pages\CreateAnnouncement;
use App\Modules\Announcements\Filament\Resources\AnnouncementResource\Pages\EditAnnouncement;
use App\Modules\Announcements\Filament\Resources\AnnouncementResource\Pages\ListAnnouncements;
use App\Modules\Announcements\Models\Announcement;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Announcement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Duyuru';

    protected static ?string $pluralModelLabel = 'Duyurular';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('title')->label('Başlık')->required()->maxLength(255),
                TextInput::make('slug')->label('Adres')->helperText('Boş bırakılırsa başlıktan üretilir.'),
                Textarea::make('summary')->label('Özet')->rows(3)->columnSpanFull(),
                RichEditor::make('body')->label('İçerik')->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('cover')->label('Kapak görseli')->collection('cover')->image(),
                TextInput::make('link')->label('Bağlantı')->url(),
                TextInput::make('button_text')->label('Buton yazısı'),
            ])->columns(2),
            Section::make('Yayın')->schema([
                Toggle::make('is_published')->label('Yayında'),
                DateTimePicker::make('published_at')->label('Yayın tarihi')->helperText('İleri bir tarih seçilirse o an yayına girer.'),
                DateTimePicker::make('ends_at')->label('Ana sayfadan kalkış')->helperText('Boşsa süresiz. Duyuru arşivde kalır.'),
            ])->columns(3),
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
                TextColumn::make('title')->label('Başlık')->searchable(),
                IconColumn::make('is_published')->label('Yayında')->boolean(),
                TextColumn::make('published_at')->label('Yayın')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('ends_at')->label('Bitiş')->dateTime('d.m.Y H:i')->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Yayında'),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'edit' => EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
