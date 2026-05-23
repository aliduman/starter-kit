<?php

namespace App\Modules\Ecommerce\Filament\Resources\ProductResource\Pages;

use App\Modules\Ecommerce\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
