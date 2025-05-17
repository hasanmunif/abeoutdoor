<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Menghitung pendapatan bulan ini
        $monthlyRevenue = DB::table('transactions')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'selesai')
            ->sum('total_amount');

        // Konversi sesuai MoneyCast
        $monthlyRevenue = $monthlyRevenue / 100;

        // Transaksi yang sedang aktif
        $activeTransactions = Transaction::whereIn('status', ['menunggu konfirmasi', 'diproses'])->count();

        // Produk dengan stok rendah (kurang dari 5)
        $lowStockProducts = Product::where('stock', '<', 5)->count();

        // Total pelanggan
        $totalUsers = User::count();

        return [
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'))
                ->description('Total transaksi selesai bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Transaksi Aktif', $activeTransactions)
                ->description('Menunggu konfirmasi & diproses')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('Produk Stok Rendah', $lowStockProducts)
                ->description('Stok kurang dari 5')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'danger' : 'success'),
        ];
    }
}