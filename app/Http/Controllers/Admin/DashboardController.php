<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCustomers = User::where('is_admin', false)->count();
        $totalSales = Order::whereNotIn('status', ['cancelled'])->sum('total');
        $recentOrders = Order::latest()->take(5)->get();
        $lowStockProducts = Product::where('is_active', true)
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalCustomers',
            'totalSales',
            'recentOrders',
            'lowStockProducts'
        ));
    }
}
