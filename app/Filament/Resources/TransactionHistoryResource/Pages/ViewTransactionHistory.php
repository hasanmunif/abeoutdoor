<?php

namespace App\Filament\Resources\TransactionHistoryResource\Pages;

use App\Filament\Resources\TransactionHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransactionHistory extends ViewRecord
{
    protected static string $resource = TransactionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url(fn (): string => TransactionHistoryResource::getUrl())
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }
}