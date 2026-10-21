<?php

namespace App\Modules\Corporate\Filament\Resources\ContactMessageResource\Pages;

use App\Modules\Corporate\Filament\Resources\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;
}
