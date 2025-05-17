@extends('front.layouts.app')
@section('title', 'Checkout')
@section('content')

    <main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative">
        <div class="flex items-center justify-between px-5 pt-5">
            <a href="{{ request()->has('from_booking') ? route('front.details', request('product_slug')) : route('cart.index') }}" class="back-btn">
                <div class="size-[44px] flex shrink-0">
                    <img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
                </div>
            </a>
            <p class="text-lg leading-[27px] font-semibold">Checkout</p>
            <div class="size-[44px] flex shrink-0"></div>
        </div>
        <form method="POST" enctype="multipart/form-data" action="{{route('checkout.store')}}" class="flex flex-col gap-[30px] mt-[30px] pb-36 mb-32">
            @csrf
            <section id="Product-list" class="flex flex-col gap-3 px-5">
                <h2 class="font-semibold text-lg leading-[27px]">Produk ({{ count(session('checkout_items', [])) }})</h2>

                @foreach(session('checkout_items', []) as $productId => $item)
                <div class="border border-[#EDEEF0] rounded-2xl p-4 checkout-item" id="checkout-item-{{ $productId }}">
                    <div class="flex items-center gap-[14px] py-2">
                        <div class="w-20 h-20 flex shrink-0 rounded-2xl overflow-hidden bg-[#F6F6F6] items-center">
                            <div class="w-full h-[50px] flex shrink-0 justify-center">
                                <img src="{{ Storage::url($item['product']->thumbnail) }}" class="h-full w-full object-contain" alt="thumbnail">
                            </div>
                        </div>
                        <div class="w-full flex flex-col gap-1">
                            <p class="font-bold text-base">{{ $item['product']->name }}</p>
                            <p class="text-sm text-[#6E6E70]">
                                Tersedia: {{ $item['product']->stock }} unit
                            </p>
                            <p class="font-semibold text-sm">
                                Rp {{ number_format($item['product']->price, 0, ',', '.') }} / 3 hari
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center quantity-control" data-product-id="{{ $productId }}">
                                <button type="button" class="decrement-btn w-8 h-8 rounded-full flex items-center justify-center border border-[#EDEEF0]" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                    <img src="{{asset('assets/images/icons/minus.svg')}}" alt="minus" class="w-4 h-4" />
                                </button>
                                <span class="w-8 text-center quantity-display">{{ $item['quantity'] }}</span>
                                <button type="button" class="increment-btn w-8 h-8 rounded-full flex items-center justify-center border border-[#EDEEF0]" {{ !$item['product']->can_multi_quantity || $item['quantity'] >= $item['product']->stock ? 'disabled' : '' }}>
                                    <img src="{{asset('assets/images/icons/plus.svg')}}" alt="plus" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex flex-col items-end">
                                <p class="text-sm text-[#6E6E70] duration-display">{{ $item['days'] }} hari</p>
                                <p class="font-bold subtotal-display">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                            </div>

                            <button type="button" class="remove-item-btn text-red-500" data-product-id="{{ $productId }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 mt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-[#6E6E70]">Durasi Sewa:</span>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center duration-control" data-product-id="{{ $productId }}">
                                    <button type="button" class="duration-decrement-btn w-8 h-8 rounded-full flex items-center justify-center border border-[#EDEEF0]" {{ $item['days'] <= 3 ? 'disabled' : '' }}>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6 12H18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <span class="w-16 text-center font-bold duration-value">{{ $item['days'] }} hari</span>
                                    <button type="button" class="duration-increment-btn w-8 h-8 rounded-full flex items-center justify-center border border-[#EDEEF0]" {{ $item['days'] >= 30 ? 'disabled' : '' }}>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 6V18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6 12H18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 bg-gray-50 rounded-xl p-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Estimasi biaya:</span>
                                <span class="font-semibold text-gray-800 price-estimation">
                                    Rp {{ number_format(ceil($item['days'] / 3) * $item['product']->price, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ ceil($item['days'] / 3) }} periode × Rp {{ number_format($item['product']->price, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="product_ids[]" value="{{ $productId }}" class="product-id-input">
                    <input type="hidden" name="quantities[]" value="{{ $item['quantity'] }}" class="quantity-input">
                    <input type="hidden" name="durations[]" value="{{ $item['days'] }}" class="duration-input">
                </div>
                @endforeach
            </section>

            <hr class="border-[#EDEEF0] mx-5">

            <!-- Tambahkan Booking Information -->
            <div id="Booking-info" class="flex flex-col px-5 gap-5">
                <h2 class="font-semibold text-lg leading-[27px]">Informasi Sewa</h2>

                <!-- Tanggal Sewa -->
                <div class="flex flex-col gap-2">
                    <label for="date" class="font-semibold">Tanggal Mulai Sewa</label>
                    <div class="group w-full rounded-2xl border border-[#EDEEF0] p-[18px_14px] flex items-center gap-3 relative transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FCCF2F]">
                        <div class="w-6 h-6 flex shrink-0">
                            <img src="{{asset('assets/images/icons/calendar.svg')}}" alt="icon">
                        </div>
                        <input type="date" name="started_at" id="date" class="appearance-none outline-none rounded-2xl w-full font-semibold text-sm leading-[24px]" required min="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Lokasi Pickup - Tampilan Modern -->
                <div class="flex flex-col gap-2">
                    <label for="store_id" class="font-semibold">Lokasi Pengambilan</label>
                    <div x-data="{ open: false, selected: null, stores: [] }" x-init="stores = [
                        @foreach(\App\Models\Store::all() as $store)
                            { id: {{ $store->id }}, name: '{{ $store->name }}', address: '{{ $store->address }}' },
                        @endforeach
                        ]; selected = stores.length > 0 ? stores[0] : null"
                        class="relative">

                        <!-- Hidden input for form submission -->
                        <input type="hidden" name="store_id" :value="selected ? selected.id : ''" required>

                        <!-- Selector Button -->
                        <button type="button"
                            @click="open = !open"
                            class="w-full rounded-2xl border border-[#EDEEF0] p-[18px_14px] flex items-center gap-3 transition-all duration-300 focus:ring-2 focus:ring-[#FCCF2F] bg-white relative"
                            :class="{'ring-2 ring-[#FCCF2F]': open}">

                            <div class="w-6 h-6 flex shrink-0">
                                <img src="{{asset('assets/images/icons/buildings.svg')}}" alt="icon">
                            </div>

                            <div class="flex-1 text-left font-semibold text-sm">
                                <span x-text="selected ? (selected.name + ' - ' + selected.address) : 'Pilih lokasi pengambilan'"
                                    class="block truncate"></span>
                            </div>

                            <div class="shrink-0 ml-2">
                                <svg class="h-5 w-5 text-gray-400 transition-transform duration-300"
                                    :class="{'rotate-180': open}"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        <!-- Dropdown List -->
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            @click.away="open = false"
                            class="absolute z-20 mt-1 w-full bg-white rounded-xl shadow-lg max-h-56 overflow-y-auto border border-[#EDEEF0]">

                            <ul class="py-1 divide-y divide-gray-100">
                                <template x-for="store in stores" :key="store.id">
                                    <li>
                                        <button type="button"
                                            @click="selected = store; open = false"
                                            class="w-full text-left px-4 py-3 flex items-center gap-2 hover:bg-gray-50 transition-colors duration-150"
                                            :class="{'bg-[#FCCF2F]/10': selected && selected.id === store.id}">

                                            <div class="w-8 h-8 rounded-full bg-[#FCCF2F]/20 flex items-center justify-center flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FCCF2F]" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>

                                            <div class="flex-1">
                                                <p class="font-medium text-sm" x-text="store.name"></p>
                                                <p class="text-xs text-gray-500 truncate" x-text="store.address"></p>
                                            </div>

                                            <!-- Selected indicator -->
                                            <div x-show="selected && selected.id === store.id" class="w-5 h-5 rounded-full bg-[#FCCF2F] flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Metode Pengambilan - Hanya Ambil di Toko -->
                <div class="flex flex-col gap-2">
                    <label class="font-semibold">Metode Pengambilan</label>
                    <div class="flex items-center rounded-2xl border border-[#EDEEF0] p-4 bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#FCCF2F]/20 rounded-full flex items-center justify-center flex-shrink-0">
                                <img src="{{asset('assets/images/icons/buildings.svg')}}" alt="store" class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="font-semibold">Ambil di Toko</p>
                                <p class="text-sm text-gray-500">Silakan ambil barang sesuai jadwal di lokasi toko yang dipilih</p>
                            </div>
                        </div>
                        <!-- Hidden input to ensure delivery_type is always "pickup" -->
                        <input type="hidden" name="delivery_type" value="pickup">
                    </div>
                </div>
            </div>

            <hr class="border-[#EDEEF0] mx-5">

            <div id="Customer-info" class="flex flex-col px-5 gap-5">
                <h2 class="font-semibold text-lg leading-[27px]">Customer Information</h2>
                <div class="flex flex-col gap-2">
                    <label for="name" class="font-semibold">Full Name</label>
                    <div class="group w-full rounded-2xl border border-[#EDEEF0] p-[18px_14px] flex items-center gap-3 relative transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FCCF2F]">
                        <div class="w-6 h-6 flex shrink-0">
                            <img src="{{asset('assets/images/icons/user.svg')}}" alt="icon">
                        </div>
                        <input type="text" name="name" id="name" class="appearance-none outline-none rounded-2xl w-full placeholder:font-normal placeholder:text-black font-semibold text-sm leading-[24px]" placeholder="Write your full name" required>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="phone" class="font-semibold">Phone Number</label>
                    <div class="group w-full rounded-2xl border border-[#EDEEF0] p-[18px_14px] flex items-center gap-3 relative transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FCCF2F]">
                        <div class="w-6 h-6 flex shrink-0">
                            <img src="{{asset('assets/images/icons/call.svg')}}" alt="icon">
                        </div>
                        <input type="tel" name="phone_number" id="phone" class="appearance-none outline-none rounded-2xl w-full placeholder:font-normal placeholder:text-black font-semibold text-sm leading-[24px]" placeholder="Write your phone number" required>
                    </div>
                </div>
            </div>

            <hr class="border-[#EDEEF0] mx-5">

            <div id="Payment-details" class="flex flex-col px-5 gap-3">
                <h2 class="font-semibold text-lg leading-[27px]">Payment Details</h2>
                <div class="flex flex-col gap-4">
                    <!-- Subtotal Produk - ini menjadi total akhir -->
                    <div class="flex items-center justify-between">
                        <p class="font-semibold">Total Pembayaran</p>
                        <p class="font-bold text-xl leading-[30px] text-[#FF7E00]" id="checkout-subtotal">
                            Rp {{ number_format(session('checkout_subtotal', 0), 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Keterangan tambahan -->
                    <div class="text-sm text-gray-500 bg-gray-50 p-3 rounded-xl">
                        <p>Total biaya sudah termasuk semua komponen biaya sewa</p>
                    </div>
                </div>
            </div>

            <hr class="border-[#EDEEF0] mx-5">
            <div id="Payment-method" class="flex flex-col px-5 gap-5">
                <h2 class="font-semibold text-lg leading-[27px]">Metode Pembayaran</h2>

                <div class="flex flex-col gap-3">
                    <!-- Pilihan metode pembayaran -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="payment-method-option border border-[#EDEEF0] rounded-2xl p-4 flex flex-col gap-2 cursor-pointer transition-all duration-200 relative">
                            <input type="radio" name="payment_method" value="manual" class="absolute opacity-0" checked>
                            <div class="flex items-center justify-center h-12">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M28 10.6667H4C3.46957 10.6667 2.96086 10.8774 2.58579 11.2525C2.21071 11.6275 2 12.1362 2 12.6667V25.3333C2 25.8638 2.21071 26.3725 2.58579 26.7475C2.96086 27.1226 3.46957 27.3333 4 27.3333H28C28.5304 27.3333 29.0391 27.1226 29.4142 26.7475C29.7893 26.3725 30 25.8638 30 25.3333V12.6667C30 12.1362 29.7893 11.6275 29.4142 11.2525C29.0391 10.8774 28.5304 10.6667 28 10.6667Z" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 22C18.2091 22 20 20.2091 20 18C20 15.7909 18.2091 14 16 14C13.7909 14 12 15.7909 12 18C12 20.2091 13.7909 22 16 22Z" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M22 10.6667L30 4.66667H2L10 10.6667" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="text-center">
                                <p class="font-semibold">Transfer Manual</p>
                                <p class="text-sm text-gray-500">Upload bukti transfer</p>
                            </div>
                            <div class="payment-check absolute top-3 right-3 opacity-0 transition-opacity">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="10" cy="10" r="10" fill="#FCCF2F"/>
                                    <path d="M6 10L9 13L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </label>

                        <label class="payment-method-option border border-[#EDEEF0] rounded-2xl p-4 flex flex-col gap-2 cursor-pointer transition-all duration-200 relative">
                            <input type="radio" name="payment_method" value="midtrans" class="absolute opacity-0">
                            <div class="flex items-center justify-center h-12">
                                <img src="{{asset('assets/images/logos/microsoft.svg')}}" class="h-10 object-contain" alt="midtrans logo">
                            </div>
                            <div class="text-center">
                                <p class="font-semibold">Midtrans</p>
                                <p class="text-sm text-gray-500">Bayar otomatis</p>
                            </div>
                            <div class="payment-check absolute top-3 right-3 opacity-0 transition-opacity">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="10" cy="10" r="10" fill="#FCCF2F"/>
                                    <path d="M6 10L9 13L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <hr class="border-[#EDEEF0] mx-5">

            <!-- Pembayaran Manual (Tampil jika metode manual dipilih) -->
            <div id="Manual-payment" class="flex flex-col px-5 gap-3">
                <div id="Send-Payment" class="flex flex-col gap-3">
                    <h2 class="font-semibold text-lg leading-[27px]">Rekening Tujuan</h2>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-[71px] h-[50px] flex shrink-0">
                                <img src="{{asset('assets/images/logos/bca.svg')}}" class="w-full h-full object-contain" alt="bank logo">
                            </div>
                            <div class="flex flex-col gap-[2px]">
                                <div class="flex items-center w-fit gap-1">
                                    <p class="font-semibold">Abe Outdoor</p>
                                    <div class="w-[18px] h-[18px] flex shrink-0">
                                        <img src="{{asset('assets/images/icons/verify.svg')}}" alt="verify">
                                    </div>
                                </div>
                                <p class="text-[#6E6E70]">214828422</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-[71px] h-[50px] flex shrink-0">
                                <img src="{{asset('assets/images/logos/mandiri.svg')}}" class="w-full h-full object-contain" alt="bank logo">
                            </div>
                            <div class="flex flex-col gap-[2px]">
                                <div class="flex items-center w-fit gap-1">
                                    <p class="font-semibold">Abe Outdoor</p>
                                    <div class="w-[18px] h-[18px] flex shrink-0">
                                        <img src="{{asset('assets/images/icons/verify.svg')}}" alt="verify">
                                    </div>
                                </div>
                                <p class="text-[#6E6E70]">3245124412441</p>
                            </div>
                        </div>
                    </div>

                    <div id="Confirm-Payment" class="flex flex-col gap-5 mt-4">
                        <h2 class="font-semibold text-lg leading-[27px]">Konfirmasi Pembayaran</h2>
                        <div class="flex flex-col gap-2">
                            <label for="Proof" class="font-semibold">Upload Bukti Transfer</label>
                            <div class="group w-full rounded-2xl border border-[#EDEEF0] p-[18px_14px] flex items-center gap-3 relative transition-all duration-300 focus-within:ring-2 focus-within:ring-[#FCCF2F] relative">
                                <div class="w-6 h-6 flex shrink-0">
                                    <img src="{{asset('assets/images/icons/note-add.svg')}}" alt="upload">
                                </div>
                                <button type="button" id="Upload-btn" class="appearance-none outline-none w-full text-left" onclick="document.getElementById('Proof').click()">
                                Add an attachment
                                </button>
                                <input type="file" name="proof" id="Proof" class="absolute -z-10 opacity-0" data-required="manual">
                            </div>
                        </div>
                        <label class="flex items-center gap-[6px]">
                            <input type="checkbox" name="confirm" id="manual_confirm" class="w-[24px] h-[24px] appearance-none checked:border-[5px] checked:border-solid checked:border-white rounded-[10px] checked:bg-[#FCCF2F] ring-1 ring-[#EDEEF0] transition-all duration-300" data-required="manual"/>
                            <p class="font-semibold text-sm leading-[21px]">Saya benar telah transfer pembayaran</p>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pembayaran Midtrans (Tampil jika metode midtrans dipilih) -->
            <div id="Midtrans-payment" class="flex flex-col px-5 gap-5 hidden">
                <h2 class="font-semibold text-lg leading-[27px]">Pembayaran via Midtrans</h2>
                <div class="bg-gray-50 rounded-2xl p-5">
                    <p class="text-center">Anda akan diarahkan ke halaman pembayaran Midtrans untuk menyelesaikan transaksi ini setelah mengklik tombol "Proses Pembayaran" di bawah.</p>


                </div>

                <label class="flex items-center gap-[6px]">
                    <input type="checkbox" name="midtrans_confirm" id="midtrans_confirm" class="w-[24px] h-[24px] appearance-none checked:border-[5px] checked:border-solid checked:border-white rounded-[10px] checked:bg-[#FCCF2F] ring-1 ring-[#EDEEF0] transition-all duration-300" data-required="midtrans"/>
                    <p class="font-semibold text-sm leading-[21px]">Saya setuju untuk melanjutkan ke pembayaran Midtrans</p>
                </label>
            </div>

            <div class="fixed bottom-16 left-0 right-0 max-w-[640px] mx-auto bg-white/80 backdrop-blur-sm border-t border-[#F1F1F1] p-5 z-20 mb-6">
                <button type="submit" class="rounded-full p-[12px_24px] bg-[#FCCF2F] font-bold w-full" id="payment-button">
                    Proses Pembayaran
                </button>
            </div>
        </form>
    </main>

    @endsection

    @push('after-scripts')
    <script src="{{asset('customjs/checkout.js')}}"></script>

    <!-- Tambahkan script Midtrans dengan client key yang benar -->
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}">
    </script>

    <script>
        window.csrfToken = '{{ csrf_token() }}';
        window.routes = {
            checkout: {
                midtrans: '{{ route("checkout.midtrans") }}',
                cart: '{{ route("cart.index") }}'
            }
        };
    </script>
    @endpush

    @push('after-styles')
    <style>
        /* Styling untuk checkout items */
        .checkout-item {
            transition: all 0.3s ease;
        }

        /* Styling untuk tombol disable */
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Styling untuk metode pembayaran */
        .payment-method-option {
            transition: all 0.2s ease;
        }

        .payment-method-option:hover {
            border-color: #FCCF2F;
        }

        /* Styling untuk payment method icons */
        .payment-method-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .payment-method-icon i {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background-color: #f8f9fa;
            color: #495057;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .payment-method-icon:hover i {
            background-color: #FCCF2F;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .payment-method-icon span {
            font-size: 12px;
            font-weight: 500;
            color: #495057;
        }
    </style>