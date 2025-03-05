<?php

namespace App\Filament\Resources\TenderResource\Pages;

use App\Components\DB\TenderImport;
use App\Filament\Resources\TenderResource;
use App\Jobs\ConfirmDocuments;
use App\Models\Tender;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ListTenders extends ListRecords
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),

            \Filament\Actions\Action::make('preview_duplicates')
                ->label('Apskatīt & dzēst dublikātus')
                ->icon('heroicon-o-eye')
                ->modalHeading('Dublikātu pārskats')
                ->modalDescription('Šie ir dublikāti, kas tiks dzēsti. Ar pirmo dublikātu tiks paturēts, bet pārējie tiks dzēsti')
                ->modalSubmitActionLabel('Dzēst dublikātus')
                ->modalCancelActionLabel('Atcelt')
                ->modalContent(fn() => self::getDuplicatesPreview())
                ->action(fn() => self::deleteDuplicates()),

            \Filament\Actions\Action::make('sync_sales_data')
                ->label('Sinhronizēt')
                ->icon('heroicon-o-arrow-path')
                ->modalHeading('Pārdošanas datu sinhronizācija')
                ->modalDescription('Šī darbība sinhronizēs pārdošanas datus ar Jumis sistēmu. Šī darbība nav atgriezeniska.')
                ->modalSubmitActionLabel('Sinhronizēt')
                ->modalCancelActionLabel('Atcelt')
                ->action(fn() => function () {

                    Tender::with('docLines')->whereNull('deleted_at')->chunk(100, function ($tenders) {
                        foreach ($tenders as $tender) {

                            $new = new TenderImport();

                            $new->importStoreDocWithRetries($tender);

                        }
                    });

                }),

            Action::make('runConfirmDocuments')
                ->label('Apstiprināt')
                ->action(function () {
                    ConfirmDocuments::dispatch();
                })
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check'),

        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'All' => Tab::make('Visi')
                ->modifyQueryUsing(fn($query) => $query),
        ];

        $serials = Tender::select('doc_no_serial')
            ->distinct()
            ->pluck('doc_no_serial', 'doc_no_serial')
            ->toArray();


        foreach ($serials as $month => $name) {
            $tabs[$name] = Tab::make($name)
                ->modifyQueryUsing(fn($query) => $query->where('doc_no_serial', $name));
        }

        return $tabs;

    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'All';
    }

    /**
     * @return string|null
     */
    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }


    public static function getDuplicatesPreview()
    {
        $duplicates = DB::table('tenders')
            ->whereNull('deleted_at')
            ->select('doc_no', 'doc_no_serial', 'amount_cash', 'amount_card', 'amount_gift')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('doc_no', 'doc_no_serial', 'amount_cash', 'amount_card', 'amount_gift')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            return new HtmlString('<p class="text-gray-600">No duplicate records found.</p>');
        }

        // Define color classes for styling groups
        $colors = ['bg-blue-100', 'bg-green-100', 'bg-yellow-100', 'bg-purple-100', 'bg-pink-100', 'bg-orange-100'];
        $colorIndex = 0;

        $preview = '<table class="w-full border-collapse border border-gray-300">
                    <tr class="bg-gray-300 text-black">
                        <th class="border p-2">Dokumenta Nr</th>
                        <th class="border p-2">Sērija</th>
                        <th class="border p-2">Nauda</th>
                        <th class="border p-2">Karte</th>
                        <th class="border p-2">Davanu karte</th>
                        <th class="border p-2">Darbība</th>
                    </tr>';

        foreach ($duplicates as $dup) {
            $records = Tender::where([
                'doc_no' => $dup->doc_no,
                'doc_no_serial' => $dup->doc_no_serial,
                'amount_cash' => $dup->amount_cash,
                'amount_card' => $dup->amount_card,
                'amount_gift' => $dup->amount_gift
            ])->get();

            $groupColor = $colors[$colorIndex % count($colors)]; // Assign unique color to the group
            $colorIndex++;

            $firstRecord = true; // Flag to ensure only the first record in the group is marked for deletion

            foreach ($records as $index => $record) {
                $rowColor = !$firstRecord ? 'bg-red-300' : 'bg-red-600'; // First duplicate is red (to be deleted), others in group color
                $action = $firstRecord ? '✅ Paturēts' : '❌ Tiks dzēsts';

                if ($firstRecord) {
                    $firstRecord = false; // Only the first record in the group should be marked for deletion
                }

                $preview .= "<tr class='{$rowColor}'>
                            <td class='border p-2 {$rowColor}'>{$record->doc_no}</td>
                            <td class='border p-2'>{$record->doc_no_serial}</td>
                            <td class='border p-2'>{$record->amount_cash}</td>
                            <td class='border p-2'>{$record->amount_card}</td>
                            <td class='border p-2'>{$record->amount_gift}</td>
                            <td class='border p-2 font-bold'>{$action}</td>
                         </tr>";
            }

            $preview .= "<tr>
                            <td class=' p-2'></td>
                            <td class=' p-2'></td>
                            <td class=' p-2'></td>
                            <td class=' p-2'></td>
                            <td class=' p-2'></td>
                            <td class=' p-2 font-bold'></td>
                         </tr>";
        }

        $preview .= '</table>';
        return new HtmlString($preview);
    }


    public static function deleteDuplicates()
    {
        $duplicates = DB::table('tenders')
            ->whereNull('deleted_at')
            ->select('doc_no', 'doc_no_serial', 'amount_cash', 'amount_card', 'amount_gift')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('doc_no', 'doc_no_serial', 'amount_cash', 'amount_card', 'amount_gift')
            ->having('count', '>', 1)
            ->get();


        foreach ($duplicates as $group) {
            $records = Tender::where('doc_no', $group->doc_no)
                ->where('doc_no_serial', $group->doc_no_serial)
                ->where('amount_cash', $group->amount_cash)
                ->where('amount_card', $group->amount_card)
                ->where('amount_gift', $group->amount_gift)
                ->orderBy('id')
                ->get();

            if ($records->count() > 1) {
                // Keep the first record, delete the rest
                $idsToDelete = $records->skip(1)->pluck('id')->toArray();
                Tender::whereIn('id', $idsToDelete)->delete();

            }
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
           // TenderStatsOverview::class,
        ];
    }

}
