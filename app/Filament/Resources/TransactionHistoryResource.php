<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionHistoryResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TransactionHistoryResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'History Transaksi Bulanan';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

    // Ubah label model di berbagai konteks
    protected static ?string $modelLabel = 'History Transaksi';
    protected static ?string $pluralModelLabel = 'History Transaksi';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'store', 'user'])
            ->latest();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Detail Transaksi')
                            ->schema([
                                Forms\Components\TextInput::make('trx_id')
                                    ->label('ID Transaksi')
                                    ->disabled(),
                                Forms\Components\DateTimePicker::make('created_at')
                                    ->label('Tanggal Transaksi')
                                    ->disabled(),
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total Pembayaran')
                                    ->prefix('Rp')
                                    ->disabled(),
                                Forms\Components\Toggle::make('is_paid')
                                    ->label('Sudah Dibayar')
                                    ->disabled(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Informasi Pelanggan')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Pelanggan')
                                    ->disabled(),
                                Forms\Components\TextInput::make('phone_number')
                                    ->label('Nomor Telepon')
                                    ->disabled(),
                                Forms\Components\TextInput::make('user.email')
                                    ->label('Email')
                                    ->disabled(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Informasi Produk')
                            ->schema([
                                Forms\Components\TextInput::make('product.name')
                                    ->label('Nama Produk')
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->disabled(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status Transaksi')
                                    ->options([
                                        'menunggu konfirmasi' => 'Menunggu Konfirmasi',
                                        'diproses' => 'Diproses',
                                        'selesai' => 'Selesai',
                                        'dibatalkan' => 'Dibatalkan',
                                    ])
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
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('trx_id')
                    ->label('ID Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durasi')
                    ->suffix(' hari'),
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
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Pembayaran')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter berdasarkan bulan dan tahun
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('month')
                                    ->label('Bulan')
                                    ->options([
                                        '1' => 'Januari',
                                        '2' => 'Februari',
                                        '3' => 'Maret',
                                        '4' => 'April',
                                        '5' => 'Mei',
                                        '6' => 'Juni',
                                        '7' => 'Juli',
                                        '8' => 'Agustus',
                                        '9' => 'September',
                                        '10' => 'Oktober',
                                        '11' => 'November',
                                        '12' => 'Desember',
                                    ])
                                    ->default(Carbon::now()->month),
                                Forms\Components\Select::make('year')
                                    ->label('Tahun')
                                    ->options(function() {
                                        $years = [];
                                        $currentYear = Carbon::now()->year;
                                        for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
                                            $years[$i] = $i;
                                        }
                                        return $years;
                                    })
                                    ->default(Carbon::now()->year),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'],
                                fn (Builder $query, $month): Builder => $query->whereMonth('created_at', $month)
                            )
                            ->when(
                                $data['year'],
                                fn (Builder $query, $year): Builder => $query->whereYear('created_at', $year)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['month'] ?? null) {
                            $monthName = Carbon::createFromDate(2000, $data['month'], 1)->format('F');
                            $indicators['month'] = 'Bulan: ' . $monthName;
                        }

                        if ($data['year'] ?? null) {
                            $indicators['year'] = 'Tahun: ' . $data['year'];
                        }

                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'menunggu konfirmasi' => 'Menunggu Konfirmasi',
                        'menunggu pembayaran' => 'Menunggu Pembayaran',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ]),

                Tables\Filters\SelectFilter::make('is_paid')
                    ->label('Pembayaran')
                    ->options([
                        '1' => 'Sudah Dibayar',
                        '0' => 'Belum Dibayar',
                    ]),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tambahkan bulk action jika diperlukan
            ]);
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

                Infolists\Components\Section::make('Informasi Pelanggan')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nama')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('phone_number')
                            ->label('Nomor Telepon'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->visible(fn ($record) => $record->user_id && $record->user),
                    ])
                    ->columns(3),

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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactionHistories::route('/'),
            'view' => Pages\ViewTransactionHistory::route('/{record}'),
        ];
    }
}
