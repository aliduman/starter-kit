<?php

namespace App\Modules\RealEstate\Filament\Resources\ListingResource\Pages;

use App\Modules\RealEstate\Filament\Resources\ListingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListListings extends ListRecords
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
