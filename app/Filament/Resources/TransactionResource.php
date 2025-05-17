<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Kelola Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone_number')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('trx_id')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('transaction_group_id')
                    ->maxLength(255)
                    ->helperText('ID Group untuk multiple checkout via Midtrans'),

                Forms\Components\TextArea::make('address')
                    ->required()
                    ->maxLength(1024),

                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->prefix('IDR'),

                Forms\Components\TextInput::make('duration')
                    ->required()
                    ->numeric()
                    ->prefix('Days')
                    ->maxValue(255),

                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('store_id')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('started_at')
                    ->required(),

                Forms\Components\DatePicker::make('ended_at')
                    ->required(),

                Forms\Components\Select::make('delivery_type')
                    ->options([
                        'pickup' => 'Pickup Store',
                        'delivery' => "Home Delivery",
                    ])
                    ->required(),

                Forms\Components\Select::make('payment_method')
                    ->options([
                        'manual' => 'Transfer Manual',
                        'midtrans' => 'Midtrans',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('proof')
                    ->openable()
                    ->image(),

                Forms\Components\Select::make('status')
                    ->options([
                        'menunggu konfirmasi' => 'Menunggu Konfirmasi',
                        'menunggu pembayaran' => 'Menunggu Pembayaran',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->required(),

                Forms\Components\Select::make('is_paid')
                    ->options([
                        true => 'Paid',
                        false => 'Not Paid',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trx_id')
                    ->label('ID Transaksi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('transaction_group_id')
                    ->label('Group ID')
                    ->searchable()
                    ->toggleable()
                    ->tooltip('Transaction group untuk multiple checkout')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->prefix('Rp ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->formatStateUsing(fn (string $state): string => $state === 'manual' ? 'Transfer Manual' : 'Midtrans')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'manual' ? 'info' : 'primary'),

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

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Sudah Bayar?'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu konfirmasi' => 'Menunggu Konfirmasi',
                        'menunggu pembayaran' => 'Menunggu Pembayaran',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'manual' => 'Transfer Manual',
                        'midtrans' => 'Midtrans',
                    ]),

                Tables\Filters\Filter::make('has_group')
                    ->label('Multiple Checkout')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('transaction_group_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('trx_id')
                                    ->label('ID Transaksi')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal Pembuatan')
                                    ->dateTime('d M Y H:i'),

                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'menunggu konfirmasi' => 'gray',
                                        'menunggu pembayaran' => 'warning',
                                        'diproses' => 'info',
                                        'selesai' => 'success',
                                        'dibatalkan' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nama Pelanggan'),

                                Infolists\Components\TextEntry::make('phone_number')
                                    ->label('Telepon'),
                            ]),

                        Infolists\Components\TextEntry::make('address')
                            ->label('Alamat')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Informasi Produk & Pembayaran')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('product.name')
                                    ->label('Nama Produk'),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Jumlah')
                                    ->suffix(' item'),

                                Infolists\Components\TextEntry::make('duration')
                                    ->label('Durasi')
                                    ->suffix(' hari'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('started_at')
                                    ->label('Tanggal Mulai')
                                    ->date('d M Y'),

                                Infolists\Components\TextEntry::make('ended_at')
                                    ->label('Tanggal Selesai')
                                    ->date('d M Y'),

                                Infolists\Components\TextEntry::make('store.name')
                                    ->label('Lokasi Toko'),
                            ]),

                        Infolists\Components\TextEntry::make('delivery_type')
                            ->label('Metode Pengiriman')
                            ->formatStateUsing(fn (string $state): string => $state === 'pickup' ? 'Pickup di Toko' : 'Diantar ke Alamat')
                            ->badge(),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->formatStateUsing(fn (string $state): string => $state === 'manual' ? 'Transfer Manual' : 'Midtrans')
                                    ->badge()
                                    ->color(fn (string $state): string => $state === 'manual' ? 'info' : 'primary'),

                                Infolists\Components\TextEntry::make('total_amount')
                                    ->label('Total Pembayaran')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\IconEntry::make('is_paid')
                                    ->label('Status Pembayaran')
                                    ->boolean()
                                    ->trueColor('success')
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseColor('danger')
                                    ->falseIcon('heroicon-o-x-circle'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Bukti Pembayaran')
                    ->schema([
                        Infolists\Components\ImageEntry::make('proof')
                            ->label('Bukti Transfer')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->payment_method === 'manual' && $record->proof),
                    ]),

                Infolists\Components\Section::make('Transaksi Dalam Group')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_group_id')
                            ->label('Group ID')
                            ->weight(FontWeight::Bold)
                            ->hidden(fn ($record) => !$record->transaction_group_id),

                        Infolists\Components\RepeatableEntry::make('group_transactions')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('trx_id')
                                            ->label('ID Transaksi')
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('product.name')
                                            ->label('Produk'),

                                        Infolists\Components\TextEntry::make('total_amount')
                                            ->label('Nominal')
                                            ->money('IDR'),

                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->badge(),
                                    ]),
                            ])
                            ->hidden(fn ($record) => !$record->transaction_group_id)
                    ])
                    ->hidden(fn ($record) => !$record->transaction_group_id),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}