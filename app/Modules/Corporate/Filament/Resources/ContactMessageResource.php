<?php

namespace App\Modules\Corporate\Filament\Resources;

use App\Filament\Concerns\ChecksPermission;
use App\Modules\Corporate\Filament\Resources\ContactMessageResource\Pages\EditContactMessage;
use App\Modules\Corporate\Filament\Resources\ContactMessageResource\Pages\ListContactMessages;
use App\Modules\Corporate\Models\ContactMessage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Gelen Mesajlar: iletişim formundan gelir; panelde yalnızca durumu değişir. */
class ContactMessageResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = ContactMessage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static string|\UnitEnum|null $navigationGroup = 'Corporate';

    protected static ?string $modelLabel = 'Mesaj';

    protected static ?string $pluralModelLabel = 'Gelen Mesajlar';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $new = ContactMessage::query()->where('status', 'new')->count();

        return $new > 0 ? (string) $new : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->label('Ad Soyad')->disabled(),
                TextInput::make('email')->label('E-posta')->disabled(),
                Select::make('subject')->label('Konu')->options(ContactMessage::subjects())->disabled(),
                TextInput::make('consented_at')->label('Onay zamanı')->disabled(),
                Textarea::make('message')->label('Mesaj')->rows(8)->disabled()->columnSpanFull(),
                Select::make('status')->label('Durum')->options(ContactMessage::STATUSES)->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')->label('Ad Soyad')->searchable(),
                TextColumn::make('email')->label('E-posta')->searchable(),
                TextColumn::make('subject')
                    ->label('Konu')
                    ->formatStateUsing(fn (?string $state) => ContactMessage::subjects()[$state] ?? $state),
                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn (string $state) => ContactMessage::STATUSES[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state) => $state === 'new' ? 'warning' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Durum')->options(ContactMessage::STATUSES),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()->label('Aç'),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'edit' => EditContactMessage::route('/{record}/edit'),
        ];
    }
}
