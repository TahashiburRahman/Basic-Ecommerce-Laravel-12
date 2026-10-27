@extends('layouts.storefront')

@section('title', 'Order placed')

@section('content')
<div class="container py-5">
    <div class="form-panel text-center">
        <div class="mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
        </div>
        <h1>Thank you for your order!</h1>
        <p class="text-muted">Your order has been received and is being processed.</p>
        <p><strong>Order number:</strong> {{ $order->order_number }}</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
            <a href="{{ route('shop') }}" class="btn btn-primary">Continue shopping</a>
            <a href="{{ route('home') }}" class="btn btn-outline-primary">Back to home</a>
        </div>
    </div>
</div>
@endsection
