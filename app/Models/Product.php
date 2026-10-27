<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'image',
        'is_active',
        'featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->price);
    }

    public function getImageUrlAttribute(): string
    {
        $image = ltrim((string) $this->image, '/');

        if ($image && filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        if (str_starts_with($image, 'images/')) {
            return is_file(public_path($image))
                ? asset($image)
                : asset('images/placeholder-product.svg');
        }

        $legacyPath = str_starts_with($image, 'storage/')
            ? substr($image, strlen('storage/'))
            : $image;

        return $legacyPath && is_file(storage_path('app/public/'.$legacyPath))
            ? route('legacy-images.show', ['path' => $legacyPath])
            : asset('images/placeholder-product.svg');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
