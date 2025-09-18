<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekapBulananResource\Pages;
use App\Filament\Resources\RekapBulananResource\RelationManagers;
use App\Models\RekapBulanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RekapBulananResource extends Resource
{
    protected static ?string $model = RekapBulanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListRekapBulanans::route('/'),
            'create' => Pages\CreateRekapBulanan::route('/create'),
            'edit' => Pages\EditRekapBulanan::route('/{record}/edit'),
        ];
    }
}
