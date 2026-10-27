@extends('admin.layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')
<div class="form-panel">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 align-items-end mb-3">
        <div class="col-md-10">
            <label class="form-label">Search by order number or customer</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control">
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Order number</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($order->status) }}</span></td>
                        <td>${{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
