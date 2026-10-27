<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
                : asset('images/placeholder-category.svg');
        }

        $legacyPath = str_starts_with($image, 'storage/')
            ? substr($image, strlen('storage/'))
            : $image;

        return $legacyPath && is_file(storage_path('app/public/'.$legacyPath))
            ? route('legacy-images.show', ['path' => $legacyPath])
            : asset('images/placeholder-category.svg');
    }
}
