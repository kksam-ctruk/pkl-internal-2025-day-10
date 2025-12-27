<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Mengambil data keranjang user yang sedang login.
     */
    public function getCart()
    {
        if (!auth()->check()) {
            throw new \Exception("Anda harus login untuk mengakses keranjang.");
        }
        
        return Cart::firstOrCreate(['user_id' => auth()->id()]);
    }

    /**
     * Menambah produk ke keranjang.
     */
    public function addProduct(Product $product, int $quantity)
    {
        // Cek stok awal
        if (!$product->hasStock($quantity)) {
            throw new \Exception("Stok tidak mencukupi. Sisa stok: {$product->stock}");
        }

        $cart = $this->getCart();
        
        return DB::transaction(function () use ($cart, $product, $quantity) {
            $item = $cart->items()->where('product_id', $product->id)->first();

            if ($item) {
                $newQty = $item->quantity + $quantity;
                
                // Validasi total quantity terhadap stok
                if (!$product->hasStock($newQty)) {
                    throw new \Exception("Gagal menambah: Total di keranjang melebihi stok tersedia.");
                }
                
                $item->update([
                    'quantity' => $newQty,
                    'subtotal' => $newQty * $product->display_price
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price_at_purchase' => $product->display_price,
                    'subtotal' => $quantity * $product->display_price
                ]);
            }
        });
    }

    /**
     * Update jumlah item di keranjang.
     */
    public function updateQuantity($itemId, $quantity)
    {
        $cart = $this->getCart();
        $item = $cart->items()->with('product')->findOrFail($itemId);

        // Jika quantity 0 atau kurang, hapus item
        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }

        // Cek ketersediaan stok produk
        if (!$item->product->hasStock($quantity)) {
            throw new \Exception("Gagal update: Stok hanya tersedia {$item->product->stock}");
        }

        return $item->update([
            'quantity' => $quantity,
            'subtotal' => $quantity * $item->product->display_price
        ]);
    }

    /**
     * Menghapus satu item dari keranjang.
     */
    public function removeItem($itemId)
    {
        $cart = $this->getCart();
        return $cart->items()->where('id', $itemId)->delete();
    }

    /**
     * Mengosongkan seluruh keranjang (opsional).
     */
    public function clearCart()
    {
        $cart = $this->getCart();
        return $cart->items()->delete();
    }
}