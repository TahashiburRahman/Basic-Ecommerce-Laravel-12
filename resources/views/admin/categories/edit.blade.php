@extends('admin.layouts.admin')

@section('title', 'Edit category')
@section('page-title', 'Edit category')

@section('content')
<div class="form-panel">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Category image</label>
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPEG, PNG, or WebP up to 20 MB. Saved as optimized WebP.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $category->is_active) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !old('is_active', $category->is_active) ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            @if($category->image)
                <div class="col-md-12">
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" width="180" height="120" style="object-fit:cover;border-radius:12px;">
                </div>
            @endif
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
