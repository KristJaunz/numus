<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingsResource\Pages;
use App\Filament\Resources\SettingsResource\RelationManagers;
use App\Models\Settings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingsResource extends Resource
{
    protected static ?string $model = Settings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationLabel(): string
    {
        return __('Uzstādījumi');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Konfigurācija');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Forms\Components\TextInput::make('key')
                    ->label('Key')
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->label('Type'),
                Forms\Components\TextInput::make('value')
                    ->label('Value')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Nosaukums'),

                Tables\Columns\TextColumn::make('key')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Vērtība'),

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
            'index' => Pages\ListSettings::route('/'),
         /*   'create' => Pages\CreateSettings::route('/create'),
            'edit' => Pages\EditSettings::route('/{record}/edit'),*/
        ];
    }
}
