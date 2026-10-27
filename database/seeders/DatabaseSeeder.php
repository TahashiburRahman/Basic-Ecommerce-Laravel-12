<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            'Home & Living' => 'home-living',
            'Electronics' => 'electronics',
            'Fashion' => 'fashion',
            'Beauty' => 'beauty',
        ];

        foreach ($categories as $name => $slug) {
            Category::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'slug' => $slug,
                'description' => 'A curated collection of premium items.',
                'is_active' => true,
            ]);
        }

        $categoryMap = Category::query()->pluck('id', 'slug');

        $products = [
            ['name' => 'Aster Smart Lamp', 'category' => 'home-living', 'price' => 59.99, 'sale_price' => 49.99, 'stock' => 20, 'featured' => true],
            ['name' => 'Nexa Wireless Speaker', 'category' => 'electronics', 'price' => 89.99, 'sale_price' => null, 'stock' => 15, 'featured' => true],
            ['name' => 'Oakwood Desk Chair', 'category' => 'home-living', 'price' => 129.00, 'sale_price' => 109.00, 'stock' => 12, 'featured' => false],
            ['name' => 'Verve Noise Cancelling Headphones', 'category' => 'electronics', 'price' => 199.99, 'sale_price' => 169.99, 'stock' => 10, 'featured' => true],
            ['name' => 'Luna Everyday Tote', 'category' => 'fashion', 'price' => 34.99, 'sale_price' => null, 'stock' => 24, 'featured' => false],
            ['name' => 'Sora Classic Watch', 'category' => 'fashion', 'price' => 149.00, 'sale_price' => 119.00, 'stock' => 8, 'featured' => true],
            ['name' => 'Serene Skin Kit', 'category' => 'beauty', 'price' => 44.50, 'sale_price' => null, 'stock' => 18, 'featured' => false],
            ['name' => 'Bloom Ceramic Diffuser', 'category' => 'beauty', 'price' => 39.99, 'sale_price' => 29.99, 'stock' => 22, 'featured' => false],
        ];

        foreach ($products as $productData) {
            Product::query()->updateOrCreate(
                ['slug' => Str::slug($productData['name'])],
                [
                    'category_id' => $categoryMap[$productData['category']],
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => 'A premium item designed for everyday living. Crafted with quality materials and built for comfort and utility.',
                    'price' => $productData['price'],
                    'sale_price' => $productData['sale_price'],
                    'stock_quantity' => $productData['stock'],
                    'is_active' => true,
                    'featured' => $productData['featured'],
                ]
            );
        }
    }
}
