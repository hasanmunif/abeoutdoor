<?php

namespace App\Filament\Customer\Resources\RentalHistoryResource\Pages;

use App\Filament\Customer\Resources\RentalHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRentalHistories extends ListRecords
{
    protected static string $resource = RentalHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak perlu action create di sini
        ];
    }
}