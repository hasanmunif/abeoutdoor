@extends('front.layouts.app')
@section('title', 'Detail Transaksi - ' . $details->trx_id)
@section('content')

<div class="flex flex-col relative has-[#Bottom-nav]:pb-[144px] pb-32">
    <!-- Header with back button -->
    <div class="flex items-center justify-between px-5 pt-5 w-full">
        <a href="{{ route('front.index') }}">
            <div class="size-[44px] flex shrink-0">
                <img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
            </div>
        </a>
        <p class="text-lg leading-[27px] font-semibold">Detail Transaksi</p>
        <div class="size-[44px] flex shrink-0"></div>
    </div>

    <!-- Transaction ID Card -->
    <section class="px-5 mt-[30px]">
        <div class="flex items-center rounded-2xl border border-[#E9E8ED] gap-3 p-4">
            <div class="w-[60px] h-[60px] flex shrink-0">
                <img src="{{asset('assets/images/icons/crown-circle.svg')}}" alt="icon">
            </div>
            <div class="flex flex-col">
                <p class="font-semibold">{{$details->trx_id}}</p>
                <p class="text-sm leading-[21px] text-[#6E6E70]">Booking ID Anda</p>
            </div>
        </div>
    </section>

    <!-- Payment Status -->
    <section class="px-5 mt-4">
        @if($details->is_paid)
        <div class="flex items-center rounded-2xl p-4 gap-4 bg-green-50 border border-green-200">
            <div class="w-10 h-10 flex-shrink-0 bg-green-500 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="flex flex-col gap-1">
                <p class="font-semibold">Pembayaran Berhasil</p>
                <p class="text-sm text-gray-600">Pembayaran Anda sudah kami terima. Silahkan menunggu instruksi selanjutnya.</p>
            </div>
        </div>
        @else
        <div class="flex items-center rounded-2xl p-4 gap-4 bg-[#FFF8E1] border border-[#FCCF2F]">
            <div class="w-10 h-10 flex-shrink-0 bg-[#FCCF2F] rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex flex-col gap-1">
                <p class="font-semibold">Menunggu Pembayaran</p>
                <p class="text-sm text-gray-700">Tim kami sedang memeriksa transaksi pada booking ini.</p>
            </div>
        </div>
        @endif
    </section>

    <!-- Product Details -->
    <section class="px-5 mt-6 pt-6 border-t border-[#EDEEF0]">
        <div class="flex items-center gap-4">
            <div class="w-24 h-24 flex-shrink-0 rounded-2xl overflow-hidden bg-[#F6F6F6] flex items-center justify-center p-2">
                <img src="{{Storage::url($details->product->thumbnail)}}" class="max-h-full max-w-full object-contain" alt="thumbnail">
            </div>
            <div class="flex flex-col gap-2">
                <p class="font-bold text-lg">{{$details->product->name}}</p>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{$details->product->category->name ?? 'Uncategorized'}}
                    </span>
                    <span class="bg-[#FFF8E1] text-gray-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{$details->product->brand->name ?? 'No Brand'}}
                    </span>
                </div>
                <p class="text-sm text-gray-700">
                    <span class="font-medium">Qty:</span> {{$details->quantity}} ×
                    <span class="font-medium">Rp</span> {{number_format($details->product->price, 0, ',', '.')}}
                </p>
            </div>
        </div>
    </section>

    <!-- Customer Information -->
    <section class="px-5 mt-6 grid gap-4">
        <h2 class="font-semibold text-lg">Informasi Penyewa</h2>

        <div class="grid grid-cols-1 gap-4">
            <div class="bg-[#F4F4F6] rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 flex-shrink-0 bg-white rounded-full flex items-center justify-center">
                    <img src="{{asset('assets/images/icons/user.svg')}}" alt="icon" class="w-5 h-5">
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nama Lengkap</p>
                    <p class="font-medium">{{$details->name}}</p>
                </div>
            </div>

            <div class="bg-[#F4F4F6] rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 flex-shrink-0 bg-white rounded-full flex items-center justify-center">
                    <img src="{{asset('assets/images/icons/call.svg')}}" alt="icon" class="w-5 h-5">
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nomor Telepon</p>
                    <p class="font-medium">{{$details->phone_number}}</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="bg-[#F4F4F6] rounded-2xl p-4 flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 flex-shrink-0 bg-white rounded-full flex items-center justify-center">
                        <img src="{{asset('assets/images/icons/calendar.svg')}}" alt="icon" class="w-5 h-5">
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Mulai</p>
                        <p class="font-medium">{{$details->started_at->format('d M Y')}}</p>
                    </div>
                </div>

                <div class="bg-[#F4F4F6] rounded-2xl p-4 flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 flex-shrink-0 bg-white rounded-full flex items-center justify-center">
                        <img src="{{asset('assets/images/icons/calendar.svg')}}" alt="icon" class="w-5 h-5">
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Selesai</p>
                        <p class="font-medium">{{$details->ended_at->format('d M Y')}}</p>
                    </div>
                </div>
            </div>

            @if($details->delivery_type == 'pickup')
            <div class="bg-[#F4F4F6] rounded-2xl p-4 flex items-start gap-3">
                <div class="w-10 h-10 flex-shrink-0 bg-white rounded-full flex items-center justify-center mt-1">
                    <img src="{{asset('assets/images/icons/buildings.svg')}}" alt="icon" class="w-5 h-5">
                </div>
                <div>
                    <p class="text-sm text-gray-500">Lokasi Pengambilan</p>
                    <p class="font-medium">{{$details->store->name}}</p>
                    <p class="text-sm text-gray-600 mt-1">{{$details->store->address}}</p>
                </div>
            </div>
            @else
            <div class="bg-[#F4F4F6] rounded-2xl p-4 flex items-start gap-3">
                <div class="w-10 h-10 flex-shrink-0 bg-white rounded-full flex items-center justify-center mt-1">
                    <img src="{{asset('assets/images/icons/house-2.svg')}}" alt="icon" class="w-5 h-5">
                </div>
                <div>
                    <p class="text-sm text-gray-500">Alamat Pengiriman</p>
                    <p class="font-medium">{{$details->address}}</p>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- Payment Details -->
    <section class="px-5 mt-6 pt-6 border-t border-[#EDEEF0]">
        <h2 class="font-semibold text-lg mb-4">Detail Pembayaran</h2>

        <div class="rounded-2xl border border-[#EDEEF0] p-4 bg-[#FAFAFA]">
            <div class="flex justify-between mb-3">
                <p class="text-gray-600">Harga Sewa</p>
                <p>Rp {{number_format($details->product->price, 0, ',', '.')}} / 3 hari</p>
            </div>

            <div class="flex justify-between mb-3">
                <p class="text-gray-600">Jumlah Barang</p>
                <p>{{$details->quantity}} item</p>
            </div>

            <div class="flex justify-between mb-3">
                <p class="text-gray-600">Metode Pengambilan</p>
                <p>{{$details->delivery_type == 'pickup' ? 'Ambil Sendiri' : 'Diantar'}}</p>
            </div>

            <div class="flex justify-between pt-3 border-t border-dashed border-gray-300">
                <p class="font-semibold">Total</p>
                <p class="font-bold text-xl">Rp {{number_format($subTotal, 0, ',', '.')}}</p>
            </div>
        </div>
    </section>

    <!-- Order Timeline (Optional) -->
    <section class="px-5 mt-6 pt-6 border-t border-[#EDEEF0]">
        <h2 class="font-semibold text-lg mb-4">Status Pesanan</h2>

        <div class="flex flex-col">
            <div class="flex gap-3 mb-4">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 bg-[#FCCF2F] rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="w-0.5 h-16 bg-gray-200 mt-1"></div>
                </div>
                <div>
                    <p class="font-semibold">Pesanan Dibuat</p>
                    <p class="text-sm text-gray-500">{{$details->created_at->format('d M Y, H:i')}}</p>
                </div>
            </div>

            <div class="flex gap-3 mb-4">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 bg-{{ $details->is_paid ? '[#FCCF2F]' : 'gray-200' }} rounded-full flex items-center justify-center">
                        @if($details->is_paid)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        @endif
                    </div>
                    {{-- <div class="w-0.5 h-16 bg-gray-200 mt-1"></div> --}}
                </div>
                <div>
                    <p class="font-semibold">Pembayaran</p>
                    <p class="text-sm text-gray-500">{{ $details->is_paid ? 'Telah dibayar' : 'Menunggu pembayaran' }}</p>
                </div>
            </div>

            {{-- <div class="flex gap-3">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                        <!-- No check mark yet -->
                    </div>
                </div>
                <div>
                    <p class="font-semibold">Pesanan Selesai</p>
                    <p class="text-sm text-gray-500">Menunggu</p>
                </div>
            </div> --}}
        </div>
    </section>

    <!-- Contact CS Button -->
    <div class="sticky bottom-0 max-w-[640px] w-full mx-auto border-t border-gray-100 bg-white/95 backdrop-blur-sm z-10 mt-8">
        <div class="flex items-center p-4">
            <a href="https://wa.me/6285322465959" class="rounded-full py-3 px-4 bg-[#FCCF2F] font-semibold w-full text-center flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                Hubungi Customer Service
            </a>
        </div>
    </div>
</div>

@endsection

@push('after-styles')
<style>
    /* Add some additional styling for the transaction details page */
    .card {
        transition: all 0.2s ease;
    }
    .card:active {
        transform: scale(0.98);
    }
</style>
@endpush