<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getNavigationLabel(): string
    {
        return __('Produkti');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Konfigurācija');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('product_id')
                    ->required()
                    ->placeholder('Izvēlies preci')
                    ->hiddenLabel()
                    ->label('Prece')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => \App\Models\Jumis\Product::where('ProductName', 'like', "%{$search}%")->limit(50)->pluck('ProductName', 'ProductID')->toArray())
                    ->getOptionLabelsUsing(fn(array $values): array => \App\Models\Jumis\Product::whereIn('ProductID', $values)->pluck('ProductName', '')->toArray())
                    ->nullable(),

                Forms\Components\TextInput::make('tax_rate')
                    ->nullable()
                    ->helperText('Ja neapliekas ar PVN, ievadi n/a')
                    ->label('PVN likme'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.ProductName')
                    ->label('Prece')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_rate')
                    ->label('PVN likme')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
