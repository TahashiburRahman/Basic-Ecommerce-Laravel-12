<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $items = $this->getCartItems();

        return view('storefront.cart', compact('items'));
    }

    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (int) ($request->quantity ?? 1);
        $cart = session()->get('cart', []);
        $existingQuantity = (int) ($cart[$product->id]['quantity'] ?? 0);
        $newQuantity = $existingQuantity + $quantity;

        if (! $product->is_active) {
            return back()->with('error', 'This product is currently unavailable.');
        }

        if ($newQuantity > $product->stock_quantity) {
            return back()->with('error', 'Only ' . $product->stock_quantity . ' items are available in stock.');
        }

        $cart[$product->id] = ['quantity' => $newQuantity];
        session()->put('cart', $cart);

        return back()->with('success', $product->name . ' was added to your cart.');
    }

    public function update(Request $request, $productId): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session()->get('cart', []);
        $product = Product::findOrFail($productId);
        $quantity = (int) $request->quantity;

        if (! $product->is_active) {
            return back()->with('error', 'This product is no longer available.');
        }

        if ($quantity > $product->stock_quantity) {
            return back()->with('error', 'Only ' . $product->stock_quantity . ' items are available in stock.');
        }

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = ['quantity' => $quantity];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function remove($productId): RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget('cart');

        return back()->with('success', 'Cart cleared.');
    }

    protected function getCartItems(): array
    {
        $cart = session()->get('cart', []);
        $items = [];

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);

            if (! $product || ! $product->is_active) {
                continue;
            }

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $quantity * $product->current_price,
            ];
        }

        return $items;
    }
}
