<?php

namespace App\Filament\Resources\TransactionHistoryResource\Pages;

use App\Filament\Resources\TransactionHistoryResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ListTransactionHistories extends ListRecords
{
    protected static string $resource = TransactionHistoryResource::class;

    public function getSubheading(): string|Htmlable
    {
        // Mendapatkan bulan dan tahun saat ini atau dari filter
        $month = request('tableFilters.created_at.month') ?? Carbon::now()->month;
        $year = request('tableFilters.created_at.year') ?? Carbon::now()->year;

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');

        // Statistik cepat
        $totalTransactions = Transaction::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        // Ambil nilai dari database (yang telah dikalikan 100 oleh MoneyCast)
        $rawTotalRevenue = DB::table('transactions')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('status', 'selesai')
            ->sum('total_amount');

        // Konversi balik seperti yang dilakukan MoneyCast::get (dibagi 100)
        $totalRevenue = $rawTotalRevenue / 100;

        // Format dengan benar sesuai nilai aslinya
        $formattedRevenue = 'Rp ' . number_format($totalRevenue, 0, ',', '.');

        return new HtmlString("
            <div class='flex flex-col mt-2 space-y-1 text-sm'>
                <div>Periode: <strong>{$monthName} {$year}</strong></div>
                <div>Total Transaksi: <strong>{$totalTransactions}</strong></div>
                <div>Total Pendapatan: <strong>{$formattedRevenue}</strong></div>
            </div>
        ");
    }
}