@props(['product'])

<div class="card h-100 border-0 shadow-sm product-card transition-all">
    {{-- Gambar & Overlay Tombol --}}
    <div class="position-relative overflow-hidden bg-light rounded-top" style="aspect-ratio: 1/1;">
        <a href="{{ route('catalog.show', $product->slug) }}" class="d-block h-100">
            <img src="{{ $product->image_url }}"
                class="card-img-top w-100 h-100 object-fit-cover hover-zoom"
                alt="{{ $product->name }}"
                loading="lazy"> {{-- Loading lazy biar web lebih cepat --}}
        </a>

        {{-- Badge Diskon --}}
        @if($product->has_discount)
        <div class="position-absolute top-0 start-0 m-2">
            <span class="badge rounded-pill bg-danger shadow-sm px-2 py-1">
                <i class="bi bi-tag-fill me-1"></i>{{ $product->discount_percentage }}%
            </span>
        </div>
        @endif

        {{-- Tombol Wishlist (Floating) --}}
        <button type="button" 
            onclick="toggleWishlist({{ $product->id }})"
            class="btn btn-white btn-wishlist rounded-circle position-absolute top-0 end-0 m-2 shadow-sm wishlist-btn-{{ $product->id }}"
            title="Tambah ke Wishlist">
            <i class="bi {{ $product->is_wishlisted ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
        </button>
    </div>

    {{-- Body Kartu --}}
    <div class="card-body d-flex flex-column p-3">
        {{-- Kategori --}}
        <div class="mb-1">
            <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}" 
               class="text-decoration-none text-uppercase fw-bold text-muted" 
               style="font-size: 0.7rem; letter-spacing: 0.5px;">
                {{ $product->category->name }}
            </a>
        </div>
        
        {{-- Judul --}}
        <h6 class="card-title mb-2">
            <a href="{{ route('catalog.show', $product->slug) }}" 
               class="text-decoration-none text-dark fw-semibold line-clamp-2" 
               title="{{ $product->name }}">
                {{ $product->name }}
            </a>
        </h6>

        {{-- Harga --}}
        <div class="price-section mt-auto">
            @if($product->has_discount)
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-danger fs-5">{{ $product->formatted_price }}</span>
                    <small class="text-decoration-line-through text-muted small">{{ $product->formatted_original_price }}</small>
                </div>
            @else
                <span class="fw-bold text-primary fs-5">{{ $product->formatted_price }}</span>
            @endif
        </div>
        
        {{-- Tombol Aksi --}}
        <div class="mt-3">
            <button class="btn btn-primary btn-sm w-100 shadow-sm py-2 fw-medium rounded-2">
                <i class="bi bi-cart-plus me-1"></i> Beli Sekarang
            </button>
        </div>
    </div>
</div>
 
<button onclick="toggleWishlist({{ $product->id }})"
        class="wishlist-btn-{{ $product->id }} btn btn-light btn-sm rounded-circle p-2 transition">
    <i class="bi {{ Auth::check() && Auth::user()->hasInWishlist($product) ? 'bi-heart-fill text-danger' : 'bi-heart text-secondary' }} fs-5"></i>
</button>


@pushOnce('styles')
<style>
    /* Card Styling */
    .product-card {
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
    }

    /* Image Zoom */
    .hover-zoom {
        transition: transform 0.6s ease;
    }

    .product-card:hover .hover-zoom {
        transform: scale(1.08);
    }

    /* Wishlist Button */
    .btn-wishlist {
        background: white;
        border: none;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        z-index: 3;
    }

    .btn-wishlist:hover {
        background: #f8f9fa;
        transform: scale(1.1);
    }

    .btn-wishlist:active {
        transform: scale(0.9);
    }

    /* Text Truncation */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        min-height: 2.8em; /* Menjaga agar tombol beli tetap sejajar */
        line-height: 1.4;
    }

    /* Utility */
    .btn-primary {
        background-color: #0d6efd;
        border: none;
    }
    
    .btn-primary:hover {
        background-color: #0b5ed7;
        transform: translateY(-1px);
    }
</style>
@endpushOnce