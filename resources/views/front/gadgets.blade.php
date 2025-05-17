@extends('front.layouts.app')
@section('title', $brand->name . ' - Products')
@section('content')

	<div class="flex flex-col relative has-[#Bottom-nav]:pb-[144px]">
		<div class="flex items-center justify-between px-5 pt-5 w-full">
			<a href="{{ url()->previous() }}">
				<div class="size-[44px] flex shrink-0">
					<img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
				</div>
			</a>
			<p class="text-lg leading-[27px] font-semibold">{{$brand->name}} Products</p>
			<div class="size-[44px] flex shrink-0"></div>
		</div>
		<section id="brand"
			class="flex p-[24px_20px] outline outline-1 outline-[#EDEEF0] rounded-2xl overflow-hidden items-center justify-between mt-[30px] mx-5">
			<div class="h-[30px] flex shrink-0">
				<img src="{{Storage::url($brand->logo)}}" alt="brand" class="h-full object-contain" />
			</div>
			<div class="flex flex-col items-end gap-[2x]">
				<p class="font-semibold">{{$brand->name}}</p>
				<p class="text-sm leading-[21px] text-[#6E6E70] text-nowrap">
                    {{$brand->products->count()}} Products
                </p>
			</div>
		</section>
		@if($brand->products->count() > 0)
		<section id="featured-product" class="px-5 mt-6 pb-5">
			@php
				// Get first product as featured
				$featuredProduct = $brand->products->first();
			@endphp
			<a href="{{route('front.details', $featuredProduct->slug)}}" class="block">
				<div class="rounded-2xl overflow-hidden bg-[#F6F6F6] relative">
					<div class="h-[180px] w-full flex items-center justify-center">
						<img src="{{Storage::url($featuredProduct->thumbnail)}}"
							 class="h-full w-full object-contain" alt="thumbnail" />
					</div>
					<div class="absolute bottom-0 left-0 right-0 bg-black/60 p-4">
						<h3 class="text-white font-semibold">{{$featuredProduct->name}}</h3>
						<p class="text-white/80 text-sm">Rp {{number_format($featuredProduct->price, 0, ',', '.')}}/3hari</p>
					</div>
				</div>
			</a>
		</section>
		@endif
		<section id="Explore" class="flex flex-col gap-[10px] mx-5 mt-[30px] pb-32">
			<div class="flex items-center justify-between">
				<h2 class="text-lg leading-[27px] font-semibold">Explore Products</h2>
				<div class="text-sm text-gray-500">{{ count($products) }} items</div>
			</div>
			<div class="flex flex-col gap-5">
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

							@if($product->category)
								<span class="text-xs px-2 py-1 bg-gray-100 rounded-full w-fit mt-2">{{ $product->category->name }}</span>
							@endif
						</div>
					</div>
				</a>
                @empty
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <img src="{{ asset('assets/images/icons/empty-box.svg') }}" alt="Empty" class="w-32 h-32 mb-4 opacity-60">
                    <p class="text-gray-500 font-medium">Belum ada produk dari brand {{$brand->name}}</p>
                    <p class="text-gray-400 text-sm mt-2">Silakan coba kategori lain atau kembali nanti</p>
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