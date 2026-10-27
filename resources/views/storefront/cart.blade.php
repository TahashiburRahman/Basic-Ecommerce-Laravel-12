@extends('layouts.storefront')

@section('title', 'Shopping cart')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Your Cart</h1>

    @if(empty($items))
        <div class="empty-state text-center">
            <h4>Your cart is empty.</h4>
            <p class="text-muted">Add a few favorites to continue shopping.</p>
            <a href="{{ route('shop') }}" class="btn btn-primary">Browse products</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach($items as $item)
                    @php $product = $item['product']; @endphp
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
                            <img
                                src="{{ $product->image_url }}"
                                alt="{{ $product->name }}"
                                width="120"
                                height="120"
                                loading="lazy"
                                class="cart-product-image"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
                            >
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><a href="{{ route('product.show', $product) }}" class="text-decoration-none text-dark">{{ $product->name }}</a></h5>
                                <p class="mb-2 text-muted">${{ number_format($product->current_price, 2) }} each</p>
                                <form action="{{ route('cart.update', $product->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $product->stock_quantity }}" class="form-control" style="max-width:90px;">
                                    <button class="btn btn-outline-primary btn-sm" type="submit">Update</button>
                                </form>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5">${{ number_format($item['subtotal'], 2) }}</div>
                                <form action="{{ route('cart.remove', $product->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-danger text-decoration-none p-0">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="col-lg-4">
                <div class="summary-box">
                    <h4>Order summary</h4>
                    @php $subtotal = collect($items)->sum(fn($item) => $item['subtotal']); @endphp
                    <div class="d-flex justify-content-between py-2 border-bottom"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span>Shipping</span><span>Free</span></div>
                    <div class="d-flex justify-content-between py-3 fw-bold"><span>Total</span><span>${{ number_format($subtotal, 2) }}</span></div>
                    <a href="{{ route('checkout') }}" class="btn btn-primary w-100">Proceed to Checkout</a>
                    <form action="{{ route('cart.clear') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">Clear Cart</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
