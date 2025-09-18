<?php

namespace App\Filament\Resources\RekapBulananResource\Pages;

use App\Filament\Resources\RekapBulananResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekapBulanan extends EditRecord
{
    protected static string $resource = RekapBulananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
