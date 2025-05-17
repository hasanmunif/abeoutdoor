@extends('front.layouts.app')
@section('title', 'Home')
@section('content')

		<section id="Categories" class="flex flex-col gap-[10px] mt-[30px] px-5">
			<h2 class="font-semibold text-lg leading-[27px]">Kategori</h2>
			<div class="grid grid-cols-3 gap-4">
				@forelse($categories as $category)
				<a href={{route('front.category', $category->slug)}} class="card">
					<div class="rounded-2xl ring-2 ring-[#EDEEF0] p-4 flex flex-col items-center gap-3 text-center transition-all duration-300 hover:bg-[#f0f0f0]">
						<div class="w-[50px] h-[50px] flex shrink-0">
							<img src="{{Storage::url($category->icon)}}" alt="icon" />
						</div>
						<p class="font-semibold">{{$category->name}}</p>
					</div>
				</a>
				@empty
				<p>belum ada data kategori terbaru!</p>
				@endforelse
			</div>
		</section>

		<a id="promo" href="#" class="px-5 mt-[30px]">
			<div class="w-full aspect-[353/100] flex shrink-0 overflow-hidden rounded-2xl">
				<img src="{{asset('assets/images/backgrounds/abeoutdoor.png')}}" class="w-full h-full object-cover" alt="promo" />
			</div>
		</a>

		<section id="New" class="flex flex-col gap-[10px] mt-[30px]">
			<h2 class="font-semibold text-lg leading-[27px] px-5">Brand Terbaru</h2>
			<div class="swiper w-full h-fit">
				<div class="swiper-wrapper">
					@forelse($latest_product as $item_latest_product)
					<a href="{{route('front.details', $item_latest_product->slug)}}" class="swiper-slide max-w-[150px] first-of-type:ml-5 last-of-type:mr-5">
						<div class="flex flex-col gap-3 bg-white">
							<div class="h-[130px] flex shrink-0 items-center rounded-2xl overflow-hidden bg-[#F6F6F6]">
								<div class="h-[70px] w-full flex shrink-0 justify-center">
									<img src="{{Storage::url($item_latest_product->thumbnail)}}" class="w-full h-full object-contain" alt="thumbnail" />
								</div>
							</div>
							<div class="flex flex-col gap-1">
								<p class="font-semibold break-words">{{$item_latest_product->name}}</p>
								<div class="flex items-center justify-between">
									<p class="text-sm leading-[21px] text-[#6E6E70]">{{$item_latest_product->category->name}}</p>
								</div>
							</div>
						</div>
					</a>
					@empty
					<p>belum ada data produk terbaru</p>
					@endforelse
				</div>
			</div>
		</section>

		<section id="Recommendation" class="flex flex-col gap-[10px] mt-[30px] px-5 pb-40 mb-32">
			<h2 class="font-semibold text-lg leading-[27px]">Pilihan Lainya</h2>
			<div class="flex flex-col gap-5">
				@forelse($random_product as $item_random_product)
				<a href="{{route('front.details', $item_random_product->slug)}}" class="card">
					<div class="flex items-center gap-3">
						<div class="w-20 h-20 flex shrink-0 rounded-2xl overflow-hidden bg-[#F6F6F6] items-center">
							<div class="w-full h-[50px] flex shrink-0 justify-center">
								<img src="{{Storage::url($item_random_product->thumbnail)}}" class="h-full w-full object-contain" alt="thumbnail" />
							</div>
						</div>
						<div class="w-full flex flex-col gap-1">
							<p class="font-semibold">{{$item_random_product->name}}</p>
							<div class="flex items-center justify-between">
								<p class="text-sm leading-[21px] text-[#6E6E70]">Rp {{number_format($item_random_product->price, 0, ',', '.')}}/3 Days</p>
							</div>
						</div>
					</div>
				</a>
				@empty
				<p>belum ada produk terbaru</p>
				@endforelse
			</div>
		</section>
@endsection

@push('after-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
@endpush

@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="{{asset('customjs/browse.js')}}"></script>
@endpush



