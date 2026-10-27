@extends('admin.layouts.admin')

@section('title', 'Create product')
@section('page-title', 'Create product')

@section('content')
<div class="form-panel">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="5" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Regular price</label>
                <input type="number" step="0.01" name="price" class="form-control" required value="{{ old('price') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sale price</label>
                <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock quantity</label>
                <input type="number" name="stock_quantity" class="form-control" required value="{{ old('stock_quantity', 0) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Product image</label>
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPEG, PNG, or WebP up to 20 MB. Saved as optimized WebP.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Featured</label>
                <select name="featured" class="form-select">
                    <option value="0" selected>No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save product</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
