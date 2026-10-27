<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageOptimizationException;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->paginate(10)->appends($request->query());
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request, ImageOptimizationService $imageOptimizer): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $newImage = $request->hasFile('image')
            ? $this->optimizeImage($request, $imageOptimizer)
            : null;
        $validated['image'] = $newImage;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['featured'] = $request->boolean('featured');

        try {
            DB::transaction(fn () => Product::create($validated));
        } catch (Throwable $exception) {
            $imageOptimizer->discard($newImage);

            throw $exception;
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(
        StoreProductRequest $request,
        Product $product,
        ImageOptimizationService $imageOptimizer
    ): RedirectResponse {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['featured'] = $request->boolean('featured');
        $oldImage = $product->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $this->optimizeImage($request, $imageOptimizer);
            $validated['image'] = $newImage;
        } elseif ($request->boolean('remove_image')) {
            $validated['image'] = null;
        }

        try {
            DB::transaction(fn () => $product->update($validated));
        } catch (Throwable $exception) {
            $imageOptimizer->discard($newImage);

            throw $exception;
        }

        if ($oldImage !== $product->image) {
            $imageOptimizer->deleteIfUnreferenced($oldImage);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product, ImageOptimizationService $imageOptimizer): RedirectResponse
    {
        $oldImage = $product->image;
        $product->delete();
        $imageOptimizer->deleteIfUnreferenced($oldImage);

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    private function optimizeImage(StoreProductRequest $request, ImageOptimizationService $imageOptimizer): string
    {
        try {
            return $imageOptimizer->optimize($request->file('image'), 'product');
        } catch (ImageOptimizationException $exception) {
            throw ValidationException::withMessages(['image' => $exception->getMessage()]);
        }
    }
}
