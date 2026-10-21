<?php

namespace App\Modules\Announcements\Filament\Resources\AnnouncementResource\Pages;

use App\Modules\Announcements\Filament\Resources\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;
}
