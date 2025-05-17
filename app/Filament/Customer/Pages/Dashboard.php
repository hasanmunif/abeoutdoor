<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('browse_products')
                ->label('Lihat Produk')
                ->url(fn (): string => route('front.index'))
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),
            Action::make('view_cart')
                ->label('Lihat Keranjang')
                ->url(fn (): string => route('cart.index'))
                ->icon('heroicon-o-shopping-cart')
                ->color('warning'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $user = Auth::user();
        $totalTransactions = Transaction::where('user_id', $user->id)->count();
        $activeTransactions = Transaction::where('user_id', $user->id)
            ->whereIn('status', ['menunggu konfirmasi', 'diproses'])
            ->count();
        $completedTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->count();

        return [
            StatsOverviewWidget::make([
                Stat::make('Total Transaksi', $totalTransactions)
                    ->icon('heroicon-o-shopping-bag'),
                Stat::make('Transaksi Aktif', $activeTransactions)
                    ->icon('heroicon-o-clock')
                    ->color('warning'),
                Stat::make('Transaksi Selesai', $completedTransactions)
                    ->icon('heroicon-o-check-circle')
                    ->color('success'),
            ])
        ];
    }

    public function getHeading(): string
    {
        return 'Selamat Datang di Akun Anda';
    }
}