@extends('layouts.storefront')

@section('title', $category->name)

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">{{ $category->name }}</h1>
            <p class="text-muted mb-0">{{ $category->description ?: 'Explore curated items in this collection.' }}</p>
        </div>
        <a href="{{ route('shop') }}" class="btn btn-outline-primary">Back to shop</a>
    </div>

    @if($products->count())
        <div class="product-grid">
            @foreach($products as $product)
                @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="mt-4 d-flex justify-content-center">{{ $products->links() }}</div>
    @else
        <div class="empty-state text-center">
            <h4>No products available in this category yet.</h4>
        </div>
    @endif
</div>
@endsection
