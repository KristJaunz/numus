<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Filament\Resources\ShopResource\RelationManagers;
use App\Models\Jumis\Partner;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function getNavigationLabel(): string
    {
        return __('Veikali');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Konfigurācija');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('partner_id')
                    ->options(Partner::listWarehouses())
                    ->label('Partneris'),
                Forms\Components\TextInput::make('address')
                    ->label('Adrese'),
                Forms\Components\TextInput::make('doc_serial')
                    ->label('Dokumenta sērija'),
                Forms\Components\TextInput::make('doc_serial_return')
                    ->label('Atgriešanas dokumenta sērija'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('partner_id')
                    ->label('Partneris')
                    ->state(function ($record) {
                        return Partner::listWarehouses()[$record->partner_id];
                    })
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('address')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Adrese'),
                Tables\Columns\TextColumn::make('doc_serial')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Dokumenta sērija'),
                Tables\Columns\TextColumn::make('doc_serial_return')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Atgriešanas dokumenta sērija'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}
