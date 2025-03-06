<?php

namespace App\Filament\Resources\LogResource\Pages;

use App\Filament\Resources\LogResource;
use App\Models\Tender;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListLogs extends ListRecords
{
    protected static string $resource = LogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'All' => Tab::make('Visi')
                ->modifyQueryUsing(fn($query) => $query),
        ];

        $tabs['Darījumi'] = Tab::make('Darījumi')
            ->modifyQueryUsing(fn($query) => $query->whereNotNull('tender_id'));

        $tabs['Apstiprināšana'] = Tab::make('Apstiprināšana')
            ->modifyQueryUsing(fn($query) => $query->whereNull('tender_id'));

        return $tabs;

    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'All';
    }
}
