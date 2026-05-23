<?php

namespace App\Modules\Corporate\Filament\Resources\PageResource\Pages;

use App\Modules\Corporate\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
