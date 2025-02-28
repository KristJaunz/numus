<?php

namespace App\Filament\Resources\TenderResource\RelationManagers;

use App\Filament\Resources\DocLinesResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'DocLines';

    public function form(Form $form): Form
    {
        return DocLinesResource::form($form);
    }

    public function table(Table $table): Table
    {
        return DocLinesResource::table($table);
    }
}
