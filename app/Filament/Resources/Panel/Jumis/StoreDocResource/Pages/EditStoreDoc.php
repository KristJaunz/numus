<?php

namespace App\Filament\Resources\Panel\Jumis\StoreDocResource\Pages;

use App\Filament\Resources\Panel\Jumis\StoreDocResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoreDoc extends EditRecord
{
    protected static string $resource = StoreDocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
