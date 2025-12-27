<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

  

    /**
     * Relasi ke OrderItems
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    protected $fillable = [
    'user_id',
    'order_number',
    'total_amount',
    'status',
    'shipping_name',    // Sesuai DB
    'shipping_phone',   // Sesuai DB
    'shipping_address', // Sesuai DB
    'payment_method'
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}