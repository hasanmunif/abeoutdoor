@extends('front.layouts.app')
@section('title', 'details')
@section('content')

	<main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative">
		<a href="{{ url()->previous() }}" class="absolute top-5 left-5 z-10">
			<div class="size-[44px] flex shrink-0">
				<img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
			</div>
		</a>

		<section id="Thumbnail" class="flex relative h-[370px] pt-[60px] pb-[66px] bg-[#F6F6F6]">
			<!-- Main Thumbnail -->
			<div class="w-full h-[230px] flex items-center justify-center flex-shrink-0">
				<img id="mainThumbnail" src="{{Storage::url($product->thumbnail)}}" alt="Thumbnail"
					class="size-full object-contain transition-opacity duration-500 ease-in-out" />
			</div>
			<!-- Selection Thumbnails -->
			<div class="flex gap-4 absolute -bottom-[35px] w-full items-center justify-center">
				<button
					class="thumbnail-button size-[70px] flex p-[15px_20px] rounded-full overflow-hidden bg-white flex-shrink-0 th-active">
					<img src="{{Storage::url($product->thumbnail)}}" alt="Thumbnail" class="size-full" />
				</button>

                @forelse($product->photos as $photo)
				<button
					class="thumbnail-button size-[70px] flex p-[15px_20px] rounded-full overflow-hidden bg-white flex-shrink-0 th-inactive">
					<img src="{{Storage::url($photo->photo)}}" alt="Thumbnail"
						class="size-full" />
				</button>
				@empty
                @endforelse

			</div>
		</section>
		<section id="Details" class="flex flex-col mt-[65px] pb-20 px-5 w-full gap-5">
			@if(session('success'))
				<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
					<span class="block sm:inline">{{ session('success') }}</span>
				</div>
			@endif

			@if(session('error'))
				<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
					<span class="block sm:inline">{{ session('error') }}</span>
				</div>
			@endif

			<div id="Heading" class="flex items-center justify-between">
				<div class="flex flex-col gap-1">
					<h1 class="text-[22px] leading-[33px] font-bold">{{$product->name}}</h1>
					<p class="text-[#6E6E70]">{{$product->category->name}} • {{$product->brand->name}}</p>
				</div>
			</div>

			<div class="flex items-center justify-between">
				<p class="font-semibold text-sm leading-[21px]">
					Stok: <span class="@if($product->stock <= 0) text-red-500 @endif">{{ $product->stock }} unit</span>
				</p>
				<p class="font-semibold text-sm leading-[21px]">
					{{ $product->can_multi_quantity ? 'Bisa pesan banyak' : 'Max 1 item' }}
				</p>
			</div>

			<div id="About" class="flex flex-col gap-1">
				<h2 class="font-semibold">About</h2>
				<p class="leading-[30px]">{{$product->about}}</p>
			</div>

			{{-- Benefits --}}
			<div id="Benefits" class="flex flex-col gap-3 pb-40 mb-32">
				<h2 class="font-semibold">Informasi Tambahan</h2>
				<div class="grid grid-cols-2 gap-4">
					<div
						class="flex p-[18px_14px] outline outline-1 outline-[#EDEEF0] rounded-2xl overflow-hidden justify-start">
						<div class="flex gap-3 items-center">
							<div class="size-6 flex shrink-0">
								<img src="{{asset('assets/images/icons/note-favorite.svg')}}" alt="icon" class="size-full" />
							</div>
							<p class="text-sm leading-[21px] font-semibold text-nowrap">Penyewaan per 3 Hari</p>
						</div>
					</div>
					<div
						class="flex p-[18px_14px] outline outline-1 outline-[#EDEEF0] rounded-2xl overflow-hidden justify-start">
						<div class="flex gap-3 items-center">
							<div class="size-6 flex shrink-0">
								<img src="{{asset('assets/images/icons/store.svg')}}" alt="icon" class="size-full" />
							</div>
							<p class="text-sm leading-[21px] font-semibold">Datang ke Toko Untuk Mengambil Barang Sewaan</p>
						</div>
					</div>
					<div
						class="flex p-[18px_14px] outline outline-1 outline-[#EDEEF0] rounded-2xl overflow-hidden justify-start">
						<div class="flex gap-3 items-center">
							<div class="size-6 flex shrink-0">
								<img src="{{asset('assets/images/icons/card.svg')}}" alt="icon" class="size-full" />
							</div>
							<p class="text-sm leading-[21px] font-semibold">Siapkan Kartu Tanda Pengenal Untuk Jaminan (KTP/KTM/dll)</p>
						</div>
					</div>
					<div
						class="flex p-[18px_14px] outline outline-1 outline-[#EDEEF0] rounded-2xl overflow-hidden justify-start">
						<div class="flex gap-3 items-center">
							<div class="size-6 flex shrink-0">
								<img src="{{asset('assets/images/icons/dollar-circle.svg')}}" alt="icon" class="size-full" />
							</div>
							<p class="text-sm leading-[21px] font-semibold text-nowrap">Denda Jika Telat Mengembalikan</p>
						</div>
					</div>
				</div>
			</div>

			<div class="fixed bottom-16 left-0 right-0 max-w-[640px] mx-auto bg-white/50 backdrop-blur-sm border-t border-[#F1F1F1] p-5 mb-6 z-10">
				<form action="{{ route('cart.add', $product->slug) }}" method="POST" class="relative z-10">
					@csrf
					<div class="flex flex-col gap-4 ">
						@if($product->can_multi_quantity && $product->stock > 0)
							<div class="flex items-center justify-between">
								<p class="font-semibold">Jumlah</p>
								<div class="flex items-center gap-3">
									<button type="button" id="decrementBtn" class="w-8 h-8 flex items-center justify-center rounded-full border border-[#EDEEF0]">
										<img src="{{asset('assets/images/icons/minus.svg')}}" alt="minus" class="w-4 h-4" />
									</button>
									<span id="quantityDisplay" class="w-6 text-center">1</span>
									<input type="hidden" name="quantity" id="quantity" value="1">
									<button type="button" id="incrementBtn" class="w-8 h-8 flex items-center justify-center rounded-full border border-[#EDEEF0]" {{ $product->stock <= 1 ? 'disabled' : '' }}>
										<img src="{{asset('assets/images/icons/plus.svg')}}" alt="plus" class="w-4 h-4" />
									</button>
								</div>
							</div>
						@endif

						<div class="flex items-center gap-3">
							<div class="flex flex-col gap-1 w-fit">
								<p class="font-bold text-xl leading-[30px]">Rp {{number_format($product->price, 0, ',', '.')}}</p>
								<p class="text-sm leading-[21px]">/3 Hari</p>
							</div>

							<div class="flex gap-2 flex-1 justify-end">
								<button type="submit" class="rounded-full p-[12px_18px] border border-[#FCCF2F] font-bold flex items-center gap-2" {{ $product->stock <= 0 ? 'disabled' : '' }}>
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M9 8H21" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									Add to Cart
								</button>

								<a href="{{ route('checkout.direct', $product->slug) }}" class="rounded-full p-[12px_18px] bg-[#FCCF2F] font-bold flex items-center justify-center" {{ $product->stock <= 0 ? 'aria-disabled=true' : '' }}>
									Rent Now
								</a>
							</div>
						</div>

						@if($product->stock <= 0)
							<p class="text-center text-red-500 font-semibold">Stok produk habis</p>
						@endif
					</div>
				</form>
			</div>
		</section>
	</main>

@endsection

@push('after-scripts')
<script src="{{asset('customjs/details.js')}}"></script>
<script>
	// Quantity control for cart
	document.addEventListener('DOMContentLoaded', function() {
		const decrementBtn = document.getElementById('decrementBtn');
		const incrementBtn = document.getElementById('incrementBtn');
		const quantityDisplay = document.getElementById('quantityDisplay');
		const quantityInput = document.getElementById('quantity');
		const maxStock = {{ $product->stock }};

		if (decrementBtn && incrementBtn) {
			decrementBtn.addEventListener('click', function() {
				let qty = parseInt(quantityInput.value);
				if (qty > 1) {
					qty--;
					quantityInput.value = qty;
					quantityDisplay.textContent = qty;
				}
			});

			incrementBtn.addEventListener('click', function() {
				let qty = parseInt(quantityInput.value);
				if (qty < maxStock) {
					qty++;
					quantityInput.value = qty;
					quantityDisplay.textContent = qty;
				}
			});
		}
	});
</script>
@endpush