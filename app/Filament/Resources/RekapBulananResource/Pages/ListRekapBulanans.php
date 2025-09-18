<?php

namespace App\Filament\Resources\RekapBulananResource\Pages;

use App\Filament\Resources\RekapBulananResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekapBulanans extends ListRecords
{
    protected static string $resource = RekapBulananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
