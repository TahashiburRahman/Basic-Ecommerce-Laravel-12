@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard overview')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="text-muted">Total products</div>
            <div class="fs-2 fw-bold">{{ $totalProducts }}</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="text-muted">Total orders</div>
            <div class="fs-2 fw-bold">{{ $totalOrders }}</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="text-muted">Total customers</div>
            <div class="fs-2 fw-bold">{{ $totalCustomers }}</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="text-muted">Total sales</div>
            <div class="fs-2 fw-bold">${{ number_format($totalSales, 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="form-panel">
            <h4 class="mb-3">Recent orders</h4>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">{{ $order->order_number }}</a></td>
                                <td>{{ $order->customer_name }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($order->status) }}</span></td>
                                <td>${{ number_format($order->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-panel">
            <h4 class="mb-3">Low stock</h4>
            @forelse($lowStockProducts as $product)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span>{{ $product->name }}</span>
                    <span class="badge bg-warning text-dark">{{ $product->stock_quantity }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">All products are sufficiently stocked.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
