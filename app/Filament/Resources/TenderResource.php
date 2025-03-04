<?php

namespace App\Filament\Resources;

use App\Components\DB\TenderImport;
use App\Filament\Resources\TenderResource\Pages;
use App\Filament\Resources\TenderResource\RelationManagers\DocLinesRelationManager;
use App\Models\Tender;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class TenderResource extends Resource
{
    protected static ?string $model = Tender::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';


    protected static ?string $navigationLabel = "Darījumi";
    protected static ?string $modelLabel = "Darījumi";
    protected static ?string $pluralLabel = "Darījumi";


    public static function getNavigationLabel(): string
    {
        return __('Darījumi');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Darījumi');
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([


                TextColumn::make('store_doc_id')
                    ->label('Dokumenta ID')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('file')
                    ->label('Fails')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable(),

                TextColumn::make('doc_no_serial')->label('Sērija')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable(),

                TextColumn::make('doc_no')->label('Dokumenta Nr.')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable(),

                TextColumn::make('partner_id')->label('Partneris')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable(),


                TextColumn::make('last_receipt_no')->label('Kases Nr.')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable(),

                TextColumn::make('doc_date')->label('Datums')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable(),
                TextColumn::make('currency_code')->label('Valūta')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->sortable(),

                TextColumn::make('amount_cash')->label('Skaidrā')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('amount_card')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->money('EUR')
                    ->label('Karte')->sortable(),
                TextColumn::make('amount_gift')
                    ->toggleable()
                    ->searchable(true,isIndividual: true)
                    ->money('EUR')
                    ->label('Davanu karte')->sortable(),

     /*           Tables\Columns\TextColumn::make('total_amount')
                    ->label('Apmaksai')
                    ->state(function ($record) {
                        return (float) $record->amount_cash + (float) $record->amount_card + (float) $record->amount_gift;
                    })
                    ->money('EUR'),*/

                TextColumn::make('created_at')
                    ->toggleable()
                    ->sortable()
                    ->toggledHiddenByDefault()
                    ->label('Pievienots'),

                TextColumn::make('updated_at')
                    ->toggleable()
                    ->sortable()
                    ->toggledHiddenByDefault()
                    ->label('Labots'),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Izveidots no'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Izveidots līdz'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'],
                                function ($query) use ($data) {
                                    $query->whereDate('doc_date', '>=', $data['created_from']);
                                })
                            ->when($data['created_until'],
                                function ($query) use ($data) {
                                    $query->whereDate('doc_date', '<=', $data['created_until']);
                                });
                    }),
    /*            SelectFilter::make('DocNoSerial')
                    ->label('Doc No Serial')
                    ->options(function () {
                        // Fetch all unique DocNoSerial values from the Tender model
                        return Tender::select('doc_no_serial')
                            ->distinct()
                            ->pluck('doc_no_serial', 'doc_no_serial')
                            ->toArray();
                    })
                    ->attribute('doc_no_serial'),*/
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Atvērt') // Button text
                    ->modalHeading('Čeka informācija') // Modal title
                    ->modalContent(fn ($record) => view('filament.tenders.view-doc-lines', [
                        'tender' => $record, // Pass the tender record to the view
                    ])),
                Tables\Actions\DeleteAction::make(),
               // Tables\Actions\ForceDeleteAction::make(),
               // Tables\Actions\RestoreAction::make(),

                Action::make('Send')
                    ->label('Sūtīt')
                    ->action(function ($record) {

                        $import = new TenderImport();

                        return $import->importStoreDocWithRetries($record);
                    }),

            ])
            ->groups([
                Tables\Grouping\Group::make('doc_no')->label('Dokumenta Nr.'),
                Tables\Grouping\Group::make('last_receipt_no')->label('Kases Nr.')
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
               // Tables\Actions\ForceDeleteBulkAction::make(),
                //Tables\Actions\RestoreBulkAction::make(),

                Tables\Actions\BulkAction::make('Send')
                    ->label('Sūtīt')
                    ->action(function ($records) {

                        $import = new TenderImport();

                        foreach ($records as $record) {
                            $import->importStoreDocWithRetries($record);
                        }

                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::Modal);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('doc_no_serial')
                ->label('Sērija'),

            TextEntry::make('amount_cash')
                ->label('Skadra nauda'),

            TextEntry::make('amount_card')
                ->label('Karte'),

            TextEntry::make('amount_gift')
                ->label('Davanu karte'),
        ]);
    }

    public static function getRelations(): array
    {
        return [ DocLinesRelationManager::make()];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenders::route('/'),
        ];
    }




}
