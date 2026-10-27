@extends('layouts.storefront')

@section('title', $product->name)

@section('content')
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-6">
            <div class="product-detail__image-wrapper">
                <img
                    src="{{ $product->image_url }}"
                    alt="{{ $product->name }}"
                    class="product-image shadow-sm"
                    width="900"
                    height="900"
                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
                >
            </div>
        </div>
        <div class="col-lg-6">
            <span class="badge bg-success-subtle text-success mb-2">{{ $product->category->name ?? 'Product' }}</span>
            <h1 class="mb-3">{{ $product->name }}</h1>
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="fw-bold fs-4 text-green">${{ number_format($product->current_price, 2) }}</span>
                @if($product->sale_price)
                    <span class="text-line-through text-muted">${{ number_format($product->price, 2) }}</span>
                @endif
            </div>
            <p class="text-muted">{{ $product->description ?: 'A quality product built for everyday comfort and practicality.' }}</p>
            <div class="mb-3">
                @if($product->stock_quantity > 0)
                    <span class="badge bg-success-subtle text-success">In stock: {{ $product->stock_quantity }}</span>
                @else
                    <span class="badge bg-danger-subtle text-danger">Out of stock</span>
                @endif
            </div>

            <form action="{{ route('cart.add') }}" method="POST" class="row g-3 align-items-center">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="col-auto">
                    <label for="quantity" class="form-label mb-0">Quantity</label>
                </div>
                <div class="col-auto">
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="form-control" style="width:100px;">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-lg" @disabled($product->stock_quantity < 1)>Add to Cart</button>
                </div>
            </form>
        </div>
    </div>

    @if($relatedProducts->count())
        <div class="mt-5">
            <h3 class="section-title mb-3">Related products</h3>
            <div class="product-grid product-grid--related">
                @foreach($relatedProducts as $related)
                    @include('storefront.partials.product-card', ['product' => $related])
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
