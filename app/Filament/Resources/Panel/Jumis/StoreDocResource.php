<?php

namespace App\Filament\Resources\Panel\Jumis;

use App\Filament\Resources\Panel\Jumis\StoreDocResource\Pages;
use App\Models\Jumis\StoreDoc;
use App\Models\Jumis\StoreDocStatus;
use App\Models\Jumis\StoreDocType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StoreDocResource extends Resource
{
    protected static ?string $model = StoreDoc::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        $docRangeStart = request('tableFilters.date_range.created_from', today());
        $docRangeEnd = request('tableFilters.date_range.created_until', today());
        $docNoSerialDefault = request('tableFilters.doc_no_serial.value', 'EKA');
        $docTypeDefault = request('tableFilters.doc_type.value', 3);

        $docStatusList = once(fn () => StoreDocStatus::pluck('StatusName', 'StatusID')->toArray());

        return $table
            ->query(function () use ($docTypeDefault, $docNoSerialDefault, $docRangeEnd, $docRangeStart) {
                return StoreDoc::query()
                    ->with(['storeDocType', 'storeDocType'])
                    ->withSum('lines', 'AmountFinal')
                    ->where('StoreDoc.StoreDocTypeID', $docTypeDefault)
                    ->whereDate('StoreDoc.DocDate', '>=', $docRangeStart)
                    ->whereDate('StoreDoc.DocDate', '<=', $docRangeEnd)
                    ->where('StoreDoc.DocNoSerial', $docNoSerialDefault)
                    ->whereIn('StoreDoc.DocNo',
                        StoreDoc::select('StoreDoc.DocNo')
                            ->where('StoreDoc.StoreDocTypeID', $docTypeDefault)
                            ->where('StoreDoc.DocNoSerial', $docNoSerialDefault)
                            ->whereDate('StoreDoc.DocDate', '>=', $docRangeStart)
                            ->whereDate('StoreDoc.DocDate', '<=', $docRangeEnd)
                            ->groupBy('StoreDoc.DocNo', 'StoreDoc.DocNoSerial')
                            ->havingRaw('COUNT(*) > 1')
                    );
            })
            ->columns([
                Tables\Columns\TextColumn::make('CreateDate')
                    ->label('Izveidots')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('storeDocType.TypeName')
                    ->label('Veids')
                    ->sortable(),

                Tables\Columns\TextColumn::make('store_doc_line_sum_amount_final')
                    ->label('Summa')
                    ->sortable(),

                Tables\Columns\TextColumn::make('storeDocStatus.StatusName')
                    ->label('Status')
                    ->sortable(),

                Tables\Columns\TextColumn::make('Comments')
                    ->label('Komentārs')
                    ->searchable(),

                /*  Tables\Columns\TextColumn::make('DocDate')
                    ->label('Izveidots')
                    ->dateTime()
                    ->sortable(),*/

                /*      Tables\Columns\TextColumn::make('DocNoSerial')
                    ->label('Sērija')
                    ->searchable(),

                Tables\Columns\TextColumn::make('DocNo')
                    ->label('Nr.')
                    ->searchable(),*/

                /* Tables\Columns\TextColumn::make('PartnerID')
                    ->numeric()
                    ->sortable(),*/
                /*Tables\Columns\TextColumn::make('PartnerContactPersonID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('PartnerAddress')
                    ->searchable(),
                Tables\Columns\TextColumn::make('PartnerVatCountryID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('PartnerVatNo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('PartnerRegistrationNumber')
                    ->searchable(),
                Tables\Columns\TextColumn::make('PartnerEmail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('StoreAddress')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ContactID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('CompanyVatCountryID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('CompanyVatNo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('CurrencyID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('DiscountPercent')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('DiscountAmount')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('DiscountCoeff')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('TaxTypeID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('AccountingID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('PriceTaxIncluded')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('PriceRecalcMode')
                    ->numeric()
                    ->sortable(),*/

                /*                Tables\Columns\TextColumn::make('SemoStatus')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('DisbursementTerm')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('DisbursementComments')
                    ->searchable(),

                Tables\Columns\TextColumn::make('StoreDoc3ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('StoreDoc21ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('StoreDoc25ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('BusinessType')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('CuttingTicket')
                    ->searchable(),
                Tables\Columns\TextColumn::make('OriginPlace')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ForwarderID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ForwarderVatCountryID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ForwarderVatNo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('VehicleRegNo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('VehicleDriver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('DealType')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ServiceType')
                    ->searchable(),
                Tables\Columns\TextColumn::make('VatComments')
                    ->searchable(),
                Tables\Columns\TextColumn::make('PrepaymentTitle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('WarrantyTitle')
                    ->searchable(),*/
                // Tables\Columns\TextColumn::make('DocRegDate')
                //     ->dateTime()
                //      ->sortable(),
                // Tables\Columns\TextColumn::make('DocRegNo'),
                /*       ->searchable(),
                Tables\Columns\TextColumn::make('DocRegNoIndex')
                    ->searchable(),
                Tables\Columns\TextColumn::make('SemoDocGUID'),
                Tables\Columns\TextColumn::make('CreateUserID')
                    ->numeric()
                    ->sortable(),*/

                /*  Tables\Columns\TextColumn::make('UpdateUserID')
                    ->numeric()
                    ->sortable(),*/
                /* Tables\Columns\TextColumn::make('UpdateDate')
                    ->dateTime()
                    ->sortable(),*/

            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->default(today()),

                        Forms\Components\DatePicker::make('created_until')
                            ->default(today()),

                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'],
                                function ($query) use ($data) {
                                    $query->whereDate('StoreDoc.DocDate', '>=', $data['created_from']);
                                })
                            ->when($data['created_until'],
                                function ($query) use ($data) {
                                    $query->whereDate('StoreDoc.DocDate', '<=', $data['created_until']);
                                });
                    }),

                SelectFilter::make('doc_no_serial')
                    ->label('Sērija')
                    ->options(
                        StoreDoc::whereNotNull('DocNoSerial')
                            ->where('DocNoSerial', 'like', '%EKA%')// Exclude NULL values
                            ->distinct()
                            ->pluck('DocNoSerial', 'DocNoSerial')
                            ->filter() // Ensure no empty values
                            ->toArray()
                    )
                    ->default('EKA')
                    ->attribute('DocNoSerial'),

                SelectFilter::make('doc_type')
                    ->label('Veids')
                    ->options(StoreDocType::pluck('TypeName', 'StoreDocTypeID')->toArray())
                    ->default(3)
                    ->attribute('StoreDocTypeID'),

                /*   SelectFilter::make('doc_status')
                    ->label('Statuss')
                    ->options(
                        StoreDocStatus::distinct()
                            ->pluck('StatusName', 'StatusID')
                            ->toArray()
                    )
                    ->default(null)
                    ->attribute('DocStatus'),*/
            ])
            ->deferFilters()
            ->actions([
                //  Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultGroup(Tables\Grouping\Group::make('DocNo')->label('Dokumenta Nr.'))
            ->paginated(false)
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent);
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreDocs::route('/'),
        ];
    }
}
