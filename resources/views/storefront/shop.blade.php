@extends('layouts.storefront')

@section('title', 'Shop')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="mb-1">Shop all products</h1>
            <p class="text-muted mb-0">Browse curated essentials for modern living.</p>
        </div>
        <form method="GET" action="{{ route('shop') }}" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search products">
            <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Latest</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price low to high</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price high to low</option>
            </select>
        </form>
    </div>

    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="form-panel">
                <h5 class="mb-3">Categories</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="{{ route('shop') }}" class="text-decoration-none {{ !request('category') ? 'text-green fw-semibold' : 'text-dark' }}">All products</a></li>
                    @foreach($categories as $category)
                        <li class="mb-2">
                            <a href="{{ route('shop', ['category' => $category->slug]) }}" class="text-decoration-none {{ request('category') === $category->slug ? 'text-green fw-semibold' : 'text-dark' }}">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <div class="col-lg-9">
            @if($products->count())
                <div class="product-grid">
                    @foreach($products as $product)
                        @include('storefront.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @else
                <div class="empty-state text-center">
                    <h4>No products match your search.</h4>
                    <p class="text-muted mb-0">Try a different keyword or change the category filter.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
