<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageOptimizationException;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::latest()->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request, ImageOptimizationService $imageOptimizer): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $newImage = $request->hasFile('image')
            ? $this->optimizeImage($request, $imageOptimizer)
            : null;
        $validated['image'] = $newImage;

        try {
            DB::transaction(fn () => Category::create($validated));
        } catch (Throwable $exception) {
            $imageOptimizer->discard($newImage);

            throw $exception;
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(
        StoreCategoryRequest $request,
        Category $category,
        ImageOptimizationService $imageOptimizer
    ): RedirectResponse {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $oldImage = $category->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $this->optimizeImage($request, $imageOptimizer);
            $validated['image'] = $newImage;
        }

        try {
            DB::transaction(fn () => $category->update($validated));
        } catch (Throwable $exception) {
            $imageOptimizer->discard($newImage);

            throw $exception;
        }

        if ($oldImage !== $category->image) {
            $imageOptimizer->deleteIfUnreferenced($oldImage);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category, ImageOptimizationService $imageOptimizer): RedirectResponse
    {
        $productsCount = Product::where('category_id', $category->id)->count();

        if ($productsCount > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'This category still has products. Reassign or delete products before deleting the category.');
        }

        $oldImage = $category->image;
        $category->delete();
        $imageOptimizer->deleteIfUnreferenced($oldImage);

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    private function optimizeImage(StoreCategoryRequest $request, ImageOptimizationService $imageOptimizer): string
    {
        try {
            return $imageOptimizer->optimize($request->file('image'), 'category');
        } catch (ImageOptimizationException $exception) {
            throw ValidationException::withMessages(['image' => $exception->getMessage()]);
        }
    }
}
