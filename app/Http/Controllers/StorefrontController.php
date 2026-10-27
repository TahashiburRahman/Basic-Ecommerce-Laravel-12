<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StorefrontController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('categories') || !Schema::hasTable('products')) {
            return view('storefront.home', [
                'featuredCategories' => collect(),
                'featuredProducts' => collect(),
                'latestProducts' => collect(),
            ]);
        }

        $featuredCategories = Category::where('is_active', true)->take(4)->get();
        $featuredProducts = Product::with('category')->where('is_active', true)->where('featured', true)->latest()->take(6)->get();
        $latestProducts = Product::with('category')->where('is_active', true)->latest()->take(8)->get();

        return view('storefront.home', compact('featuredCategories', 'featuredProducts', 'latestProducts'));
    }

    public function shop(Request $request)
    {
        if (!Schema::hasTable('products')) {
            return view('storefront.shop', [
                'products' => collect(),
                'categories' => collect(),
            ]);
        }

        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            if (!Schema::hasTable('categories')) {
                return view('storefront.shop', [
                    'products' => collect(),
                    'categories' => collect(),
                ]);
            }

            $category = Category::where('slug', $request->category)->firstOrFail();
            $query->where('category_id', $category->id);
        }

        $sort = $request->get('sort', 'latest');

        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->appends($request->query());
        $categories = Schema::hasTable('categories') ? Category::where('is_active', true)->get() : collect();

        return view('storefront.shop', compact('products', 'categories'));
    }

    public function category(Category $category)
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('categories')) {
            abort(404);
        }

        $products = $category->products()->where('is_active', true)->paginate(12);
        $categories = Category::where('is_active', true)->get();

        return view('storefront.category', compact('category', 'products', 'categories'));
    }

    public function show(Product $product)
    {
        if (!Schema::hasTable('products')) {
            abort(404);
        }

        abort_unless($product->is_active, 404);

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('storefront.product', compact('product', 'relatedProducts'));
    }
}
