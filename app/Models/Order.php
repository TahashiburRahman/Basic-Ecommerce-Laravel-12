<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'mobile_number',
        'email',
        'delivery_address',
        'district_or_city',
        'order_notes',
        'subtotal',
        'total',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(substr(md5(uniqid((string) time(), true)), 0, 8));
            }
        });
    }

    public function restoreStock(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        foreach ($this->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock_quantity', $item->quantity);
            }
        }

        $this->status = 'cancelled';
        $this->save();
    }
}
