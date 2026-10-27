@extends('layouts.storefront')

@section('title', 'Checkout')

@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="form-panel">
                <h2 class="mb-4">Checkout</h2>
                <form method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full name</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile number</label>
                            <input type="text" name="mobile_number" class="form-control" value="{{ old('mobile_number') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Email (optional)</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Delivery address</label>
                            <textarea name="delivery_address" class="form-control" rows="3" required>{{ old('delivery_address') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">District or city</label>
                            <input type="text" name="district_or_city" class="form-control" value="{{ old('district_or_city') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Order notes (optional)</label>
                            <textarea name="order_notes" class="form-control" rows="3">{{ old('order_notes') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Place order</button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="summary-box">
                <h4>Order summary</h4>
                @php $subtotal = 0; @endphp
                @foreach($items as $item)
                    @php $subtotal += $item['line_total']; @endphp
                    <div class="d-flex justify-content-between my-2">
                        <span>{{ $item['product']->name }} x {{ $item['quantity'] }}</span>
                        <span>${{ number_format($item['line_total'], 2) }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>${{ number_format($subtotal, 2) }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
