<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Transaksi Aktif';

    protected static ?string $navigationGroup = 'Penyewaan';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'store']) // Eager load relasi
            ->where('user_id', auth()->id())
            ->whereIn('status', ['menunggu konfirmasi', 'diproses', 'menunggu pembayaran'])
            ->latest();
    }

    public static function infolist(Infolist $infolist): Infolist
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
                                'menunggu pembayaran' => 'warning',
                                'diproses' => 'info',
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('trx_id')
                            ->label('ID Transaksi')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Tanggal Transaksi')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Pembayaran')
                            ->prefix('Rp')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Produk')
                    ->schema([
                        Forms\Components\TextInput::make('product.name')
                            ->label('Produk')
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Periode Sewa')
                    ->schema([
                        Forms\Components\DatePicker::make('started_at')
                            ->label('Mulai Sewa')
                            ->disabled(),
                        Forms\Components\DatePicker::make('ended_at')
                            ->label('Akhir Sewa')
                            ->disabled(),
                        Forms\Components\TextInput::make('duration')
                            ->label('Durasi Sewa')
                            ->suffix(' hari')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.thumbnail')
                    ->label('Produk')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('trx_id')
                    ->label('ID Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durasi')
                    ->suffix(' hari')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'menunggu konfirmasi' => 'gray',
                        'menunggu pembayaran' => 'warning',
                        'diproses' => 'info',
                        'selesai' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu konfirmasi' => 'Menunggu Konfirmasi',
                        'menunggu pembayaran' => 'Menunggu Pembayaran',
                        'diproses' => 'Diproses',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('bayarUlang')
                    ->label('Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->color('warning')
                    ->url(fn (Transaction $record): string => route('checkout.repay', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Transaction $record): bool =>
                        $record->status === 'menunggu pembayaran' &&
                        $record->payment_method === 'midtrans' &&
                        !$record->is_paid
                    ),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }
}