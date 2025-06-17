@extends('front.layouts.app')
@section('title', 'Keranjang')
@section('content')

<main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative">
    <div class="flex items-center justify-between px-5 pt-5">
        <a href="{{ route('front.index') }}" class="back-btn">
            <div class="size-[44px] flex shrink-0">
                <img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
            </div>
        </a>
        <p class="text-lg leading-[27px] font-semibold">Keranjang</p>
    </div>


    <div id="cart-container" class="relative mb-36 pb-40">
        @if(count($items) > 0)
            <form action="{{ route('checkout.index') }}" method="POST" class="flex flex-col gap-[30px] mt-[30px]" id="checkout-form">
                @csrf
                <div class="flex items-center justify-between px-5 py-2">
                    <h2 class="font-semibold text-lg leading-[27px]">Produk ({{ count($items) }})</h2>
                    <button type="button" class="text-red-500 text-sm" id="clear-cart-btn">
                        Kosongkan Keranjang
                    </button>
                    <form id="clear-cart-form" action="{{ route('cart.clear') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>

                <div class="flex flex-col gap-5 px-5" id="cart-items">
                    @foreach($items as $item)
                        <div class="border border-[#EDEEF0] rounded-2xl p-4" id="cart-item-{{ $item['product']->id }}">
                            <div class="flex items-start gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="items[]" value="{{ $item['product']->id }}" class="sr-only" checked>
                                    <div class="w-6 h-6 rounded-lg border-2 border-gray-200 flex items-center justify-center transition-colors duration-200 ease-in-out checkbox-visual">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="checkbox-icon">
                                            <path d="M5 12L10 17L19 8" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </label>

                                <div class="flex flex-1 gap-3">
                                    <div class="w-20 h-20 flex shrink-0 rounded-2xl overflow-hidden bg-[#F6F6F6] items-center">
                                        <div class="w-full h-[50px] flex shrink-0 justify-center">
                                            <img src="{{ Storage::url($item['product']->thumbnail) }}" class="h-full w-full object-contain" alt="thumbnail" />
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1 flex-1">
                                        <p class="font-bold text-base">{{ $item['product']->name }}</p>
                                        <p class="text-sm text-[#6E6E70]">
                                            Tersedia: {{ $item['product']->stock }} unit
                                        </p>
                                        <p class="font-semibold text-sm">
                                            Rp {{ number_format($item['product']->price, 0, ',', '.') }} / 3 hari
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center quantity-control" data-product-id="{{ $item['product']->id }}">
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

                                    <button type="button" class="remove-item-btn text-red-500" data-product-id="{{ $item['product']->id }}">
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
                                        <div class="flex items-center duration-control" data-product-id="{{ $item['product']->id }}">
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
                        </div>
                    @endforeach
                </div>

                <div class="fixed bottom-16 left-0 right-0 max-w-[640px] mx-auto bg-white/50 backdrop-blur-sm border-t border-[#F1F1F1] p-5 z-10 mb-6">
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex flex-col gap-1 w-fit">
                            <p class="font-bold text-xl leading-[30px]" id="cart-total">Rp {{ number_format($total, 0, ',', '.') }}</p>
                            <p class="text-sm leading-[21px]" id="cart-count">Total ({{ count($items) }} produk)</p>
                        </div>
                        <a href="{{ route('checkout.index') }}" class="rounded-full p-[12px_24px] bg-[#FCCF2F] font-bold w-fit text-center">
                            Checkout
                        </a>
                    </div>
                </div>
            </form>
        @else
            <div class="flex flex-col items-center justify-center h-[70vh] px-5 text-center">
                <svg class="w-32 h-32 mb-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    <line x1="4" y1="8" x2="22" y2="8"></line>
                    <line x1="9" y1="12" x2="9" y2="4" stroke-dasharray="2,2"></line>
                    <line x1="15" y1="12" x2="15" y2="4" stroke-dasharray="2,2"></line>
                </svg>
                <p class="text-lg font-semibold text-gray-800">Keranjang Kosong</p>
                <p class="text-sm text-gray-500 mt-2">Anda belum menambahkan produk apapun ke keranjang</p>
                <a href="{{ route('front.index') }}" class="mt-6 rounded-full p-[12px_24px] bg-[#FCCF2F] font-bold">
                    Lihat Produk
                </a>
            </div>
        @endif
    </div>
</main>

@endsection
@push('after-scripts')
<script src="{{asset('customjs/cart.js')}}"></script>
@endpush


@push('after-styles')
<style>
    /* Simpel checkbox styling */
    .checkbox-visual {
        background-color: white;
    }

    input[type="checkbox"]:checked + .checkbox-visual {
        background-color: #FCCF2F;
        border-color: #FCCF2F;
    }

    .checkbox-icon {
        opacity: 0;
        transform: scale(0);
        transition: all 0.2s ease;
    }

    input[type="checkbox"]:checked + .checkbox-visual .checkbox-icon {
        opacity: 1;
        transform: scale(1);
    }

    /* Animation for duration chips */
    .duration-chip.bg-\[\#FCCF2F\] {
        animation: pulse 1s;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>
@endpush