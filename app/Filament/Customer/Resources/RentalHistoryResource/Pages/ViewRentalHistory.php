<?php

namespace App\Filament\Customer\Resources\RentalHistoryResource\Pages;

use App\Filament\Customer\Resources\RentalHistoryResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewRentalHistory extends ViewRecord
{
    protected static string $resource = RentalHistoryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Infolists\Components\TextEntry::make('trx_id')
                            ->label('ID Transaksi')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Transaksi')
                            ->dateTime('d M Y, H:i'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'selesai' => 'success',
                                'menunggu konfirmasi' => 'gray',
                                'diproses' => 'warning',
                                'dibatalkan' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('is_paid')
                            ->label('Status Pembayaran')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Lunas' : 'Belum Lunas')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Produk')
                    ->schema([
                        Infolists\Components\ImageEntry::make('product.thumbnail')
                            ->label('Foto Produk')
                            ->disk('public')
                            ->height(200)
                            ->extraImgAttributes(['class' => 'rounded-lg object-contain']),
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('Nama Produk')
                            ->weight('bold')
                            ->size('lg'),
                        Infolists\Components\TextEntry::make('quantity')
                            ->label('Jumlah Item'),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total Pembayaran')
                            ->money('IDR')
                            ->weight('bold'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Infolists\Components\Section::make('Periode Sewa')
                    ->schema([
                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Mulai Sewa')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('ended_at')
                            ->label('Akhir Sewa')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('duration')
                            ->label('Durasi Sewa')
                            ->suffix(' hari'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Informasi Pengambilan')
                    ->schema([
                        Infolists\Components\TextEntry::make('store.name')
                            ->label('Lokasi Pengambilan'),
                        Infolists\Components\TextEntry::make('delivery_type')
                            ->label('Metode Pengiriman')
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'pickup' => 'Ambil di Toko',
                                'delivery' => 'Diantar ke Alamat',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('address')
                            ->label('Alamat Pengiriman')
                            ->visible(fn ($record) => $record->delivery_type === 'delivery'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Bukti Pembayaran')
                    ->schema([
                        Infolists\Components\ImageEntry::make('proof')
                            ->label('Bukti Transfer')
                            ->disk('public')
                            ->height(300),
                    ]),
            ]);
    }
}