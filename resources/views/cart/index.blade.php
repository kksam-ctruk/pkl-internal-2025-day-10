@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container py-5">
    {{-- Hitung Total Bayar secara LIVE (mencegah Rp 0 jika DB bermasalah) --}}
    @php
        $grandTotal = 0;
        if($cart && $cart->items) {
            foreach($cart->items as $item) {
                // Gunakan display_price dari accessor model Product
                $grandTotal += ($item->quantity * $item->product->display_price);
            }
        }
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h3 mb-0 text-gray-800">
            <i class="bi bi-cart3 text-primary me-2"></i>Keranjang Belanja
        </h2>
        @if($cart && $cart->items->count())
            <span class="badge bg-primary px-3 py-2 rounded-pill">
                {{ $cart->items->sum('quantity') }} Produk
            </span>
        @endif
    </div>

    {{-- Pesan Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($cart && $cart->items->count() > 0)
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="p-3">Produk</th>
                                    <th class="p-3 text-center">Harga</th>
                                    <th class="p-3 text-center">Jumlah</th>
                                    <th class="p-3 text-end">Subtotal</th>
                                    <th class="p-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart->items as $item)
                                    @php
                                        // Hitung subtotal baris ini secara live
                                        $itemSubtotal = $item->quantity * $item->product->display_price;
                                    @endphp
                                    <tr>
                                        <td class="p-3">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item->product->image_url }}" class="rounded me-3" width="70" height="70" style="object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">{{ Str::limit($item->product->name, 40) }}</h6>
                                                    <small class="text-muted">{{ $item->product->category->name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3 text-center">
                                            {{ $item->product->formatted_price }}
                                        </td>
                                        <td class="p-3">
                                            <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" 
                                                       min="1" max="{{ $item->product->stock }}"
                                                       class="form-control form-control-sm text-center mx-auto" 
                                                       style="width: 70px;" onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td class="p-3 text-end fw-bold">
                                            Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                        </td>
                                        <td class="p-3 text-end">
                                            <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <h5 class="mb-4">Ringkasan Belanja</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Harga</span>
                        <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total Bayar</span>
                        <span class="fw-bold fs-5 text-primary">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 btn-lg rounded-3">Checkout Sekarang</a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h4 class="mt-3">Keranjangmu Kosong</h4>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary mt-3">Mulai Belanja</a>
        </div>
    @endif
</div>
@endsection