<?php

namespace App\Filament\Resources\DocLinesResource\Pages;

use App\Filament\Resources\DocLinesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocLines extends EditRecord
{
    protected static string $resource = DocLinesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
