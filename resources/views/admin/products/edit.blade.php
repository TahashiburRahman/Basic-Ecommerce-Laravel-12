@extends('admin.layouts.admin')

@section('title', 'Edit product')
@section('page-title', 'Edit product')

@section('content')
<div class="form-panel">
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $product->name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="5" class="form-control">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Regular price</label>
                <input type="number" step="0.01" name="price" class="form-control" required value="{{ old('price', $product->price) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sale price</label>
                <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock quantity</label>
                <input type="number" name="stock_quantity" class="form-control" required value="{{ old('stock_quantity', $product->stock_quantity) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Product image</label>
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPEG, PNG, or WebP up to 20 MB. Saved as optimized WebP.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $product->is_active) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !old('is_active', $product->is_active) ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Featured</label>
                <select name="featured" class="form-select">
                    <option value="1" {{ old('featured', $product->featured) ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !old('featured', $product->featured) ? 'selected' : '' }}>No</option>
                </select>
            </div>
            @if($product->image)
                <div class="col-md-12">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="180" height="180" class="product-thumbnail" onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';">
                    <div class="mt-2">
                        <label class="form-check-label"><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
                    </div>
                </div>
            @endif
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
