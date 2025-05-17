@extends('front.layouts.app')
@section('title', 'Lanjutkan Pembayaran')
@section('content')

<main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative">
    <div class="flex items-center justify-between px-5 pt-5">
        <a href="{{ route('filament.customer.resources.transactions.index') }}" class="back-btn">
            <div class="size-[44px] flex shrink-0">
                <img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
            </div>
        </a>
        <p class="text-lg leading-[27px] font-semibold">Lanjutkan Pembayaran</p>
        <div class="size-[44px] flex shrink-0"></div>
    </div>

    <div class="flex flex-col gap-6 px-5 py-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold mb-4">Ringkasan Pembayaran</h2>

            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">ID Transaksi:</span>
                    <span class="font-semibold">{{ $transaction->trx_id }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Tanggal:</span>
                    <span>{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Produk:</span>
                    <span>{{ $transaction->product->name }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Status:</span>
                    <span class="bg-yellow-100 text-yellow-800 py-1 px-2 rounded text-sm">Menunggu Pembayaran</span>
                </div>

                <div class="border-t border-gray-200 my-3"></div>

                <div class="flex justify-between items-center font-bold">
                    <span>Total Pembayaran:</span>
                    <span class="text-lg text-[#FF7E00]">Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-5 rounded-lg">
            <div class="text-center mb-5">
                <svg class="w-12 h-12 mx-auto text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h3 class="font-bold text-lg mt-2">Pembayaran dengan Midtrans</h3>
                <p class="text-gray-600 mt-1">Anda akan diarahkan ke halaman pembayaran Midtrans setelah mengklik tombol di bawah.</p>
            </div>

            <button type="button" id="pay-button" class="w-full bg-[#FCCF2F] text-black py-3 rounded-full font-bold">
                Bayar Sekarang
            </button>
        </div>
    </div>
</main>

@endsection

@push('after-scripts')
<!-- Tambahkan script Midtrans -->
<script type="text/javascript"
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        // Tampilkan loading
        this.disabled = true;
        this.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
        `;

        // Buka snap popup
        window.snap.pay('{{ $snap_token }}', {
            onSuccess: function(result) {
                window.location.href = '{{ $success_url }}';
            },
            onPending: function(result) {
                window.location.href = '{{ $pending_url }}';
            },
            onError: function(result) {
                alert('Pembayaran gagal, silakan coba lagi.');
                document.getElementById('pay-button').disabled = false;
                document.getElementById('pay-button').innerHTML = 'Bayar Sekarang';
            },
            onClose: function() {
                alert('Anda menutup halaman pembayaran sebelum menyelesaikan transaksi');
                document.getElementById('pay-button').disabled = false;
                document.getElementById('pay-button').innerHTML = 'Bayar Sekarang';
            }
        });
    });
</script>
@endpush