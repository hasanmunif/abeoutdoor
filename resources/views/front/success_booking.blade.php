@extends('front.layouts.app')
@section('title', 'Sukses Booking')
@section('content')

		<main class="max-w-[640px] mx-auto min-h-screen flex flex-col relative has-[#Bottom-nav]:pb-[144px] pb-36">
			<section id="finishBook" class="flex flex-col gap-[30px] max-w-[353px] p-[30px_20px] items-center m-auto">
				<div class="flex flex-col gap-2 items-center">
					<h1 class="text-2xl leading-[36px] font-bold">Booking Berhasil</h1>

					<!-- Tampilkan pesan berbeda berdasarkan metode pembayaran -->
					@if($transaction->payment_method == 'midtrans' && $transaction->is_paid)
						<p class="leading-[30px] text-[#6E6E70] text-center">Pembayaran Midtrans berhasil! Kami akan segera memproses pesanan Anda.</p>
					@else
						<p class="leading-[30px] text-[#6E6E70] text-center">Kami akan segera menghubungi anda untuk proses pemberian barang</p>
					@endif
				</div>

				<!-- Menampilkan brand logo dan thumbnail produk -->
				{{-- <div class="w-full flex flex-col items-center gap-4">
					<!-- Brand Logo -->
					<div class="w-24 h-24 bg-[#F8F8F8] rounded-full flex items-center justify-center shadow-sm">
						<img src="{{Storage::url($transaction->product->brand->logo)}}" alt="Brand Logo" class="w-16 h-16 object-contain" />
					</div>

					<!-- Product Thumbnail & Info -->
					<div class="w-full bg-[#F8F8F8] rounded-2xl p-4 flex flex-col items-center">
						<div class="w-full h-[160px] flex items-center justify-center flex-shrink-0">
							<img src="{{Storage::url($transaction->product->thumbnail)}}" alt="Thumbnail" class="max-h-full max-w-full object-contain" />
						</div>
						<div class="mt-3 text-center">
							<p class="font-semibold text-lg">{{$transaction->product->name}}</p>
							<p class="text-sm text-gray-600">{{$transaction->product->brand->name}}</p>
						</div>
					</div>
				</div> --}}

				<div class="flex flex-col gap-2 rounded-2xl overflow-hidden outline outline-1 outline-[#E9E8ED] p-4 w-full">
					<p class="font-semibold">Booking ID</p>
					<div class="flex items-center gap-3">
						<div class="w-[60px] h-[60px] flex shrink-0">
							<img src={{asset('assets/images/icons/crown-circle.svg')}} alt="icon" />
						</div>
						<div class="flex flex-col">
							<p class="font-semibold">{{$transaction->trx_id}}</p>
							<p class="text-sm leading-[21px] text-[#6E6E70]">Metode: {{ $transaction->payment_method == 'midtrans' ? 'Midtrans' : 'Transfer Manual' }}</p>
						</div>
					</div>
				</div>
				<div class="w-[220px] flex flex-col gap-3 items-center">
					<a href={{route('front.index')}} class="w-full text-center rounded-full p-[12px_24px] bg-[#FCCF2F] font-bold text-black">Sewa Lagi</a>
					<a href={{route('front.transactions')}} class="w-full text-center rounded-full p-[12px_24px] bg-white font-bold text-black outline outline-1">Detail Booking</a>
				</div>
			</section>
		</main>

	@endsection