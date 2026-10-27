@extends('admin.layouts.admin')

@section('title', 'Order details')
@section('page-title', 'Order details')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="form-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">{{ $order->order_number }}</h4>
                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>${{ number_format($item->product_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-panel mb-3">
            <h5>Update status</h5>
            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select mb-3">
                    @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary w-100">Save status</button>
            </form>
        </div>

        <div class="form-panel">
            <h5>Customer info</h5>
            <p class="mb-1"><strong>Name:</strong> {{ $order->customer_name }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ $order->mobile_number }}</p>
            <p class="mb-1"><strong>Email:</strong> {{ $order->email ?: 'N/A' }}</p>
            <p class="mb-1"><strong>Address:</strong> {{ $order->delivery_address }}</p>
            <p class="mb-1"><strong>City:</strong> {{ $order->district_or_city }}</p>
            <p class="mb-1"><strong>Notes:</strong> {{ $order->order_notes ?: 'None' }}</p>
            <hr>
            <p class="mb-1"><strong>Subtotal:</strong> ${{ number_format($order->subtotal, 2) }}</p>
            <p class="mb-0"><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
        </div>
    </div>
</div>
@endsection
