<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * fillable harus lengkap agar data dari Service bisa masuk ke DB.
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price_at_purchase',
        'subtotal',
    ];

    /**
     * Casts memastikan angka decimal dari DB dikonversi jadi tipe data 
     * yang benar di PHP (float/double) agar perhitungan matematika akurat.
     */
    protected $casts = [
        'price_at_purchase' => 'decimal:2',
        'subtotal'          => 'decimal:2',
        'quantity'          => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke header Keranjang.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Relasi ke Produk. 
     * Sangat penting untuk mengambil nama produk, gambar, dan stok di Blade.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==================== ACCESSORS (OPSIONAL) ====================

    /**
     * Jika kamu ingin format rupiah subtotal langsung dari model.
     * Panggil di Blade dengan: $item->formatted_subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}