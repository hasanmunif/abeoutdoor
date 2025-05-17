@extends('front.layouts.app')
@section('title', 'Menunggu Pembayaran')
@section('content')

<main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative pb-36">
    <div class="flex items-center justify-between px-5 pt-5 pb-32">
        <a href="{{ route('front.index') }}" class="back-btn">
            <div class="size-[44px] flex shrink-0">
                <img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
            </div>
        </a>
        <p class="text-lg leading-[27px] font-semibold">Status Transaksi</p>
        <div class="size-[44px] flex shrink-0"></div>
    </div>

    <div class="flex flex-col items-center justify-center mt-10 px-5 gap-6">
        <!-- Status Icon -->
        <div class="w-32 h-32 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-center">Menunggu Pembayaran</h1>

        <div class="text-center text-gray-600">
            <p>Transaksi telah dibuat. Silakan lakukan pembayaran untuk melanjutkan proses penyewaan.</p>
        </div>

        <!-- Transaction Details -->
        <div class="w-full bg-gray-50 p-5 rounded-lg">
            <h2 class="font-semibold mb-3">Informasi Transaksi</h2>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">No. Transaksi:</span>
                    <span class="font-semibold">{{ $transaction->trx_id }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-semibold text-yellow-500">{{ $transaction->status }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Total Pembayaran:</span>
                    <span class="font-semibold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Produk:</span>
                    <span class="font-semibold">{{ $transaction->product->name }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Jumlah:</span>
                    <span class="font-semibold">{{ $transaction->quantity }} pcs</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Durasi Sewa:</span>
                    <span class="font-semibold">{{ $transaction->duration }} hari</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col w-full gap-3 mt-4">

            <a href="{{ route('front.index') }}" class="border border-gray-300 text-center py-3 px-6 rounded-full">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</main>

@endsection