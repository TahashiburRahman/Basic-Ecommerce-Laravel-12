@extends('admin.layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-link p-0 text-decoration-none">Back to dashboard</a>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add product</a>
</div>

<div class="form-panel mb-4">
    <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">All</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>
</div>

<div class="form-panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="60" height="60" loading="lazy" class="product-thumbnail" onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"></td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'Unassigned' }}</td>
                        <td>${{ number_format($product->current_price, 2) }}</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>{{ $product->featured ? 'Yes' : 'No' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this product?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $products->links() }}</div>
</div>
@endsection
