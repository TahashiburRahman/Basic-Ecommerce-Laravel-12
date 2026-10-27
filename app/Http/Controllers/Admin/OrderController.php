<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('mobile_number', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->paginate(10)->appends($request->query());

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $status = $request->input('status');
        $allowed = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (! in_array($status, $allowed, true)) {
            return back()->with('error', 'Invalid order status.');
        }

        if ($status === 'cancelled' && $order->status !== 'cancelled') {
            $order->restoreStock();

            return redirect()->route('admin.orders.show', $order)->with('success', 'Order cancelled and stock restored.');
        }

        $order->status = $status;
        $order->save();

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order status updated.');
    }
}
