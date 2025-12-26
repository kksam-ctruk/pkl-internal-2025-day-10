<?php
// app/Http/Controllers/WishlistController.php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\WishlistService;


//    public function index(WishlistService $wishlistService)
// {
//     $products = $wishlistService->getUserWishlist();
//     return view('wishlist.index', compact('products'));
// }

// public function toggle(Product $product, WishlistService $wishlistService)
// {
//     $result = $wishlistService->toggleProduct($product);
//     return response()->json($result);
// }
class WishlistController extends Controller
{
    /**
     * Menampilkan halaman wishlist user
     */
    public function index(WishlistService $wishlistService)
    {
        $products = auth()->user()
            ->wishlistProducts() // ✅ PAKAI belongsToMany
            ->with(['category', 'primaryImage'])
            ->latest('wishlists.created_at')
            ->paginate(12);

            $products = $wishlistService->getUserWishlist();

        return view('wishlist.index', compact('products'));
    }

    /**
     * Toggle wishlist (AJAX)
     */
    public function toggle(Product $product, WishlistService $wishlistService)
    {
        $user = auth()->user();

        // Cek apakah produk sudah ada di wishlist
        $exists = $user->wishlists()
            ->where('product_id', $product->id)
            ->exists();

            $result = $wishlistService->toggleProduct($product);

        if ($exists) {
            // ❌ HAPUS dari wishlist
            $user->wishlistProducts()->detach($product->id);

            $added  = false;
            $message = 'Produk dihapus dari wishlist.';
        } else {
            // ✅ TAMBAH ke wishlist
            $user->wishlistProducts()->attach($product->id);

            $added  = true;
            $message = 'Produk ditambahkan ke wishlist!';
        }

        return response()->json([
            'status'  => 'success',
            'added'   => $added,
            'message' => $message,
            'count'   => $user->wishlistProducts()->count(),
        ]);
    }
}
