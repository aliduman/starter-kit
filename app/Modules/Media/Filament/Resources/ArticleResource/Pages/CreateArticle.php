<?php

namespace App\Modules\Media\Filament\Resources\ArticleResource\Pages;

use App\Modules\Media\Filament\Resources\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}
