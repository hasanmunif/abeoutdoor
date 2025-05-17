<?php

namespace App\Filament\Customer\Resources\TransactionResource\Pages;

use App\Filament\Customer\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('browse_products')
                ->label('Cari Produk')
                ->url(fn (): string => route('front.index'))
                ->icon('heroicon-o-shopping-bag'),
        ];
    }
}