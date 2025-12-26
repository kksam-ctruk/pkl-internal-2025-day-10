<?php
// app/Services/WishlistService.php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    /**
     * Mendapatkan daftar produk di wishlist user yang sedang login.
     */
    public function getUserWishlist()
    {
        // Pastikan user login, jika tidak return collection kosong
        if (!Auth::check()) {
            return collect();
        }

        return Auth::user()->wishlistProducts()
            ->with(['category', 'primaryImage']) // Eager loading untuk performa
            ->latest('wishlists.created_at')
            ->paginate(12);
    }

    /**
     * Menambah atau menghapus produk dari wishlist (Toggle).
     * Sangat berguna untuk tombol "Hati" di UI.
     * * @return array ['status' => 'added'|'removed']
     */
    public function toggleProduct(Product $product): array
    {
        $user = Auth::user();
        
        // Pastikan relasi di model User adalah belongsToMany
        // syncWithoutDetaching menambah tanpa menghapus yang lama
        // detach menghapus jika ada
        
        $exists = $user->wishlistProducts()->where('product_id', $product->id)->exists();

        if ($exists) {
            $user->wishlistProducts()->detach($product->id);
            return ['status' => 'removed', 'message' => 'Produk dihapus dari wishlist'];
        } else {
            $user->wishlistProducts()->attach($product->id);
            return ['status' => 'added', 'message' => 'Produk ditambahkan ke wishlist'];
        }
    }

    /**
     * Mengecek apakah sebuah produk ada di wishlist user (untuk label di UI).
     */
    public function isWishlisted(int $productId): bool
    {
        if (!Auth::check()) return false;

        return Auth::user()->wishlistProducts()
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Mengosongkan semua isi wishlist.
     */
    public function clearWishlist(): void
    {
        Auth::user()->wishlistProducts()->detach();
    }
}