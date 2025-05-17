@extends('front.layouts.app')
@section('title', 'Choose Brand')
@section('content')

	<main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative has-[#Bottom-nav]:pb-[144px]">
		<div class="flex items-center justify-between px-5 pt-5">
			<a href="{{route('front.index')}}">
				<div class="size-[44px] flex shrink-0">
					<img src="{{asset('assets/images/icons/arrow-left.svg')}}" alt="icon" />
				</div>
			</a>
			<p class="text-lg leading-[27px] font-semibold ms-2">Choose Brand</p>
		</div>

		<section id="Brand" class="flex flex-col gap-[30px] mt-[30px] px-5 pb-36">
			<div id="PhoneBrands" class="grid grid-cols-2 gap-5">
                @forelse($category->brandCategories as $brand)
				<a href="{{route('front.brand', $brand->brand->slug)}}"
					class="flex p-[33px] border border-[#EDEEF0] rounded-2xl overflow-hidden items-center justify-center transition-all duration-300 hover:ring-2 hover:ring-[#FCCF2F]">
					<div class="h-full w-[100px] flex shrink-0">
						<img src="{{Storage::url($brand->brand->logo)}}" alt="brand" class="size-full" />
					</div>
				</a>
                @empty
                <p>belum ada data brand dari kategori</p>
                @endforelse
			</div>
		</section>


	</main>

	@endsection