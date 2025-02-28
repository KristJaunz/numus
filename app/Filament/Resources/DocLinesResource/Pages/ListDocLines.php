<?php

namespace App\Filament\Resources\DocLinesResource\Pages;

use App\Filament\Resources\DocLinesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocLines extends ListRecords
{
    protected static string $resource = DocLinesResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
}
