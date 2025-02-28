<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocLinesResource\Pages;
use App\Filament\Resources\DocLinesResource\RelationManagers;
use App\Filament\Resources\TenderResource\RelationManagers\DocLinesRelationManager;
use App\Models\DocLine;
use App\Models\DocLines;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocLinesResource extends Resource
{


    protected static ?string $model = DocLine::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('i')
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                // ...
            ])
            ->actions([
                // You may add these actions to your table if you're using a simple
                // resource, or you just want to be able to delete records without
                // leaving the table.
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                // ...
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                // ...
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->hidden(),

                Tables\Columns\TextColumn::make('tender.doc_no_serial')
                    ->label('Sērijas Nr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tender.doc_no')
                    ->label('Dokumenta Nr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('i')
                    ->label('Prece')
                    ->sortable(),

                Tables\Columns\TextColumn::make('l')
                    ->label('L')
                    ->sortable()
                    ->hidden(),

                Tables\Columns\TextColumn::make('q')
                    ->label('Daudzums')
                    ->sortable(),

                Tables\Columns\TextColumn::make('r')
                    ->label('PVN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pb')
                    ->label('Cena')
                    ->sortable(),

                Tables\Columns\TextColumn::make('d')
                    ->label('Atlaide')
                    ->sortable(),

                Tables\Columns\TextColumn::make('p')
                    ->label('Gala cena')
                    ->sortable(),



                TextColumn::make('rv')
                    ->label('RV')
                    ->sortable()
                    ->hidden(),

                TextColumn::make('m')
                    ->label('M')
                    ->sortable()
                    ->hidden(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->hidden(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->sortable()
                    ->hidden(),

                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->sortable()
                    ->hidden(false),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocLines::route('/'),
            'create' => Pages\CreateDocLines::route('/create'),
            'edit' => Pages\EditDocLines::route('/{record}/edit'),
        ];
    }
}
