@extends('front.layouts.app')
@section('title', $brand->name . ' - ' . ($categoryName ?? 'All Products'))
@section('content')

	<div class="flex flex-col relative has-[#Bottom-nav]:pb-[144px]">
		<div class="flex items-center justify-between px-5 pt-5 w-full">
			<a href="{{ url()->previous() }}">
				<div class="size-[44px] flex shrink-0">
					<img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
				</div>
			</a>
			<p class="text-lg leading-[27px] font-semibold">
				{{$brand->name}} {{ $categoryName ? '- '.$categoryName : 'Products' }}
			</p>
			<div class="size-[44px] flex shrink-0"></div>
		</div>
		<section class="flex flex-col gap-2 mx-5 mt-[30px]">
			<div class="flex p-[24px_20px] outline outline-1 outline-[#EDEEF0] rounded-xl items-center justify-between">
				<div class="flex items-center gap-3">
					<div class="h-[40px] w-[40px] flex shrink-0 bg-gray-50 rounded-full p-1">
						<img src="{{Storage::url($brand->logo)}}" alt="brand" class="h-full w-full object-contain" />
					</div>
					<div>
						<p class="font-semibold">{{$brand->name}}</p>
						<p class="text-sm text-[#6E6E70]">
							{{$products->count()}} Products
						</p>
					</div>
				</div>

				@if($categoryName)
				<div class="bg-[#FCCF2F]/20 px-4 py-2 rounded-lg border border-[#FCCF2F]/30">
					<span class="font-medium text-sm">{{ $categoryName }}</span>
				</div>
				@endif
			</div>


		</section>
		@if($brand->products->count() > 0)
		<section id="featured-product" class="px-5 mt-6">
			@php
				// Get first product as featured
				$featuredProduct = $brand->products->first();
			@endphp
			<a href="{{route('front.details', $featuredProduct->slug)}}" class="block">
				<div class="rounded-2xl overflow-hidden bg-[#F6F6F6] relative">
					{{-- <div class="h-[180px] w-full flex items-center justify-center">
						<img src="{{Storage::url($featuredProduct->thumbnail)}}"
							 class="h-full w-full object-contain" alt="thumbnail" />
					</div> --}}
					<div class="absolute bottom-0 left-0 right-0 bg-black/70 p-4">
						<div class="flex items-center justify-between">
							<h3 class="text-white font-semibold">{{$featuredProduct->name}}</h3>
							@if($featuredProduct->category)
							<span class="text-xs px-3 py-1 bg-white/20 text-white rounded-full">
								{{ $featuredProduct->category->name }}
							</span>
							@endif
						</div>
						<p class="text-white/80 text-sm mt-1">Rp {{number_format($featuredProduct->price, 0, ',', '.')}}/3hari</p>
					</div>
				</div>
			</a>
		</section>
		@endif
		<section id="Explore" class="flex flex-col gap-[10px] mx-5 mt-[30px] pb-32">
			<div class="flex items-center justify-between border-b border-gray-100 pb-2">
				<h2 class="text-lg leading-[27px] font-semibold">
					{{ $categoryName ? $categoryName . ' Collection' : 'All Products' }}
				</h2>
				{{-- <div class="text-sm text-gray-500">{{ count($products) }} items</div> --}}
			</div>

			<div class="flex flex-col gap-5 mt-3">
				@forelse($products as $product)
				<a href="{{route('front.details', $product->slug)}}" class="card rounded-2xl p-3 border border-[#EDEEF0] transition-all hover:shadow-sm">
					<div class="flex items-center gap-3">
						<div class="w-20 h-20 flex shrink-0 rounded-2xl overflow-hidden bg-[#F6F6F6] items-center">
							<div class="w-full h-[50px] flex shrink-0 justify-center">
								<img src="{{Storage::url($product->thumbnail)}}"
									class="h-full w-full object-contain" alt="thumbnail" />
							</div>
						</div>
						<div class="w-full flex flex-col gap-1">
							<p class="font-semibold">{{$product->name}}</p>
							<div class="flex items-center justify-between">
								<p class="text-sm leading-[21px] text-[#6E6E70]">Rp {{number_format($product->price, 0, ',', '.')}}/3hari</p>

								<!-- Stock badge -->
								<span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">
									Stock: {{ $product->stock }}
								</span>
							</div>

							<!-- Category & Brand info - selalu ditampilkan -->
							<div class="flex items-center gap-1 mt-2">
								@if($product->category)
								<span class="text-xs px-2 py-1 bg-gray-100 rounded-full">{{ $product->category->name }}</span>
								@endif
								<span class="text-xs px-2 py-1 bg-[#FCCF2F]/10 rounded-full">{{ $product->brand->name }}</span>
							</div>
						</div>
					</div>
				</a>
				@empty
				<div class="flex flex-col items-center justify-center py-8 text-center">
					<img src="{{ asset('assets/images/icons/empty-box.svg') }}" alt="Empty" class="w-32 h-32 mb-4 opacity-60">
					@if($categoryName)
						<p class="text-gray-500 font-medium">Belum ada produk {{ $brand->name }} dalam kategori {{ $categoryName }}</p>
						<p class="text-gray-400 text-sm mt-2">Silakan coba kategori lain</p>
					@else
						<p class="text-gray-500 font-medium">Belum ada produk dari brand {{$brand->name}}</p>
						<p class="text-gray-400 text-sm mt-2">Silakan coba brand lain atau kembali nanti</p>
					@endif
					<a href="{{ route('front.index') }}" class="mt-4 bg-[#FCCF2F] px-4 py-2 rounded-full text-sm font-medium">
						Kembali ke Beranda
					</a>
				</div>
				@endforelse
			</div>
		</section>
	</div>

@endsection

@push('after-styles')
<style>
    .card {
        transition: all 0.2s ease;
    }
    .card:active {
        transform: scale(0.98);
    }
</style>
@endpush