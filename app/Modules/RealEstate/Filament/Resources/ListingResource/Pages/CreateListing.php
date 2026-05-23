<?php

namespace App\Modules\RealEstate\Filament\Resources\ListingResource\Pages;

use App\Modules\RealEstate\Filament\Resources\ListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;
}
