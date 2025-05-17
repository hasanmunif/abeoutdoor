<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\RentalHistoryResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class RentalHistoryResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'History Penyewaan';

    protected static ?string $navigationGroup = 'Penyewaan';

    protected static ?int $navigationSort = 2;

    // Ubah label model di berbagai konteks
    protected static ?string $modelLabel = 'History Penyewaan';
    protected static ?string $pluralModelLabel = 'History Penyewaan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'store'])
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                // Tampilkan transaksi yang sudah selesai atau dibatalkan
                $query->where('status', 'selesai')
                      ->orWhere('status', 'dibatalkan');
            })
            ->latest();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Penyewaan')
                    ->schema([
                        Forms\Components\TextInput::make('trx_id')
                            ->label('ID Transaksi')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Tanggal Sewa')
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
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\FileUpload::make('product.thumbnail')
                                            ->label('Foto Produk')
                                            ->disk('public')
                                            ->image()
                                            ->disabled()
                                            ->imagePreviewHeight('150')
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('product.name')
                                            ->label('Nama Produk')
                                            ->disabled(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->disabled(),
                                    ]),
                            ]),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Sewa')
                    ->dateTime('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Tanggal Kembali')
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
                        'selesai' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->formatStateUsing(fn (string $state): string => $state === 'manual' ? 'Transfer Manual' : 'Midtrans')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'manual' ? 'info' : 'primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'manual' => 'Transfer Manual',
                        'midtrans' => 'Midtrans',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
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

                Infolists\Components\Section::make('Metode Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Metode')
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'manual' => 'Transfer Manual',
                                'midtrans' => 'Midtrans',
                                default => $state,
                            })
                            ->badge()
                            ->color(fn (string $state): string => $state === 'manual' ? 'info' : 'primary'),
                    ]),

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
                            ->visible(fn ($record) => $record->payment_method === 'manual' && $record->proof)
                            ->height(300),
                    ]),

                // Tampilkan informasi transaksi group jika ada
                Infolists\Components\Section::make('Informasi Multiple Checkout')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_group_id')
                            ->label('ID Grup Transaksi')
                            ->weight('bold'),

                        Infolists\Components\RepeatableEntry::make('group_transactions')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('trx_id')
                                            ->label('ID Transaksi')
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('product.name')
                                            ->label('Produk'),

                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->badge(),
                                    ]),
                            ]),
                    ])
                    ->visible(fn ($record) => !empty($record->transaction_group_id) && $record->getGroupTransactionsAttribute()->count() > 0),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentalHistories::route('/'),
            'view' => Pages\ViewRentalHistory::route('/{record}'),
        ];
    }
}