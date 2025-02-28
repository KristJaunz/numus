<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogResource\Pages;
use App\Filament\Resources\LogResource\RelationManagers;
use App\Models\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';


    public static function getNavigationLabel(): string
    {
        return __('Žurnāls');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Darījumi');
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('tender.store_doc_id')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Jumis dok. ID'),

                Tables\Columns\TextColumn::make('tender.file')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Fails'),
                Tables\Columns\TextColumn::make('tender.doc_no_serial')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->label('Sērija'),
                Tables\Columns\TextColumn::make('tender.doc_no')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Dokumenta numurs'),
                Tables\Columns\TextColumn::make('message')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->wrap()
                    ->label('Ziņojums'),

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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListLogs::route('/'),
            'create' => Pages\CreateLog::route('/create'),
            'edit' => Pages\EditLog::route('/{record}/edit'),
        ];
    }
}
