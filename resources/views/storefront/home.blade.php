@extends('layouts.storefront')

@section('title', 'Verdant Market')

@section('content')
<div class="container py-5">
    <section class="shop-hero">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 mb-3">New season collection</span>
                <h1 class="display-5 fw-bold text-dark">Thoughtful essentials for a better home life.</h1>
                <p class="lead text-muted mt-3">Discover elevated everyday products with a fresh, modern aesthetic and practical design.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('shop') }}" class="btn btn-primary btn-lg">Shop now</a>
                    <a href="{{ route('shop', ['sort' => 'price_asc']) }}" class="btn btn-outline-primary btn-lg">Deals</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="{{ asset('images/placeholder-product.svg') }}" alt="Featured products" class="img-fluid rounded-4 shadow" width="600" height="420" style="max-height:420px; width:100%; object-fit:cover;">
            </div>
        </div>
    </section>

    <section class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="section-title mb-0">Featured categories</h3>
            <a href="{{ route('shop') }}" class="btn btn-link text-decoration-none text-green p-0">Browse all</a>
        </div>
        <div class="row g-4">
            @foreach($featuredCategories as $category)
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('category.show', $category) }}" class="text-decoration-none text-dark">
                        <div class="category-tile h-100">
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="img-fluid rounded-3 mb-3" width="600" height="400" loading="lazy" style="height:180px; width:100%; object-fit:cover;">
                            <h5 class="mb-0">{{ $category->name }}</h5>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="section-title mb-0">Featured products</h3>
        </div>
        <div class="product-grid">
            @foreach($featuredProducts as $product)
                @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <section class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="section-title mb-0">Latest arrivals</h3>
        </div>
        <div class="product-grid">
            @foreach($latestProducts as $product)
                @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
</div>
@endsection
