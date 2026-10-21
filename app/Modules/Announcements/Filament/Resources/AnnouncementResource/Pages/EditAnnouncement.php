<?php

namespace App\Modules\Announcements\Filament\Resources\AnnouncementResource\Pages;

use App\Modules\Announcements\Filament\Resources\AnnouncementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
