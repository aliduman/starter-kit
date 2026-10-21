<?php

namespace App\Modules\Corporate\Filament\Resources\ContactMessageResource\Pages;

use App\Modules\Corporate\Filament\Resources\ContactMessageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContactMessage extends EditRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
