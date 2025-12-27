{{-- resources/views/checkout/index.blade.php --}}

{{-- <x-app-layout> --}}
@extends('layouts.app')

@section('title', 'Halaman checkout')
@section('content')
    <div class="my-5 container justify-content-center">
        
        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            <div class="card">
                <h1 class="card-header text-center">Checkout</h1>

                {{-- Form Alamat --}}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="">Informasi Pengiriman</h2>
                            <div class="mb-3">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" name="name" value="{{ auth()->user()->name }}" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="address" rows="3" class="form-control" required></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                        <h2 class="">Ringkasan Pesanan</h2>

                            <div class="mb-3">
                                @foreach($cart->items as $item)
                                    <span>{{ $item->product->name }} x {{ $item->quantity }}</span><br>
                                    <span class="">{{ number_format($item->subtotal, 0, ',', '.') }}</span><br>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><h5>Total</h5></label><br>
                                <span>Rp <strong>{{ number_format($cart->items->sum('subtotal'), 0, ',', '.') }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="my-3 d-grid">
                    <button type="submit" class="btn btn-primary">Buat Pesanan</button>
                </div>

                {{-- Order Summary --}}
                {{-- <hr class="py-3">
                <div class="justify-content-center card-body">
                    <div class="">
                        <h2 class="">Ringkasan Pesanan</h2>

                        <div class="">
                            @foreach($cart->items as $item)
                                <div class="">
                                    <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                                    <span class="">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="">
                            <div class="">
                                <span>Total</span>
                                <span>Rp {{ number_format($cart->items->sum('subtotal'), 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="my-3 d-grid">
                            <button type="submit" class="btn btn-primary">Buat Pesanan</button>
                        </div>
                    </div>
                </div> --}}
                
            </div>
        </form>
    </div>
@endsection

<style>

</style>
{{-- </x-app-layout> --}}